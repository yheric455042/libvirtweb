<?php
require_once('mysql.php');
require_once('libvirt.php');

class Controller {
	
	private $SQLClass;
	private $libvirt = array();
    private $ips;
    private $templates;
    private $user;
    //private $config;
	public function __construct($SQLClass, $hosts_ip, $templates, $user) {
		$this->SQLClass = $SQLClass;
        $this->templates = $templates;
        $this->ips = $hosts_ip;
        $this->user = $user;
        for($i = 0; $i < sizeof($hosts_ip); $i++) {
		    $this->libvirt[$i] = new Libvirt('qemu+ssh://root@'.$hosts_ip[$i].'/system');
        }
	}
    
    
	
	public function getVMList($params) {
        $userArray = $this->user->getUser();
        $uid = $userArray['uid'];
        $where = $this->user->isadmin() ? "1" : "uid = ?";
		$vms = $this->SQLClass->select("SELECT * FROM vmlist WHERE $where", $this->user->isadmin() ? array() : array($uid));
        $outputs = array();

        //file_put_contents('token.list', '');
	    foreach($vms as $vm) {
			$name = $vm['uid']."-".$vm['name'];
            $res = $this->libvirt[$vm['host']]->get_domain_by_name($name);
			$uuid = libvirt_domain_get_uuid_string($res);
            $dom = $this->libvirt[$vm['host']]->domain_get_info($res);
            $cpu = $dom['nrVirtCpu'];
			$arch = $this->libvirt[$vm['host']]->domain_get_arch($res);
            $state = $this->libvirt[$vm['host']]->domain_state_translate($dom['state']);
            $vnc = $this->libvirt[$vm['host']]->domain_get_vnc_port($res);
			$disk = $this->libvirt[$vm['host']]->get_disk_capacity($res);
			$token = $vm['host'].'-'.$uuid;
            $this->libvirt[$vm['host']]->domain_is_running($name) && $this->tokenfileControl('add', $token, $this->ips[$vm['host']].':'.$vnc);
			array_push($outputs, array('uid'=>$vm['uid'], 'name'=>$vm['name'], 'vcpu'=>$cpu, 'mem'=>$vm['mem']."G", 'disk'=>$disk, 'arch'=>$arch ,'state'=>$state, 'uuid'=>$uuid, 'token'=>$token, 'host'=>$vm['host']));
		}
        $outputs['isadmin'] = $userArray['isadmin'];
		return $outputs;
	}

    public function pendingCreate($params) {
        $host = $params['host'];

        if(isset($params['id'])) {
            
            $id = $params['id'];
            $value = $params['value'];

            if($value == 'cancel') {
                $result = $this->SQLClass->execute("UPDATE pending_list SET status = 3 WHERE id=?",array($id)) ? 'success' : 'error'; // 3 is reject
                 
                return $result;

            } else if($value == 'submit') {
                $this->SQLClass->execute('UPDATE pending_list SET status = 1 WHERE id = ?',array($id));
                $arr = $this->SQLClass->select("SELECT * FROM pending_list WHERE id=?",array($id));

                foreach($arr as $user_data) {
                    $uid = $user_data['uid'];
                    $name = $user_data['name'];
                    $vcpu = $user_data['vcpu'];
                    $mem = $user_data['mem'];
                    $template = $user_data['template'];
                }
                
                if($this->vmRepeat($uid, $name)) {
                    return 'error';
                }

                $sql = "INSERT INTO vmlist (uid, name, host, mem) VALUES(?,?,?,?)";
                if($this->createVM($host, $vcpu, $mem, $template, $uid, $name)) {
                    $this->SQLClass->execute($sql, array($uid, $name, $host, $mem));
                    $result = $this->SQLClass->execute("UPDATE  pending_list SET status = 2 WHERE id=?",array($id)) ? 'success' : 'error'; // 2 is done
                 
                    return $result;

                }

            }


        } else if($params['isadmin'] == 'true') {
            $name = $params['name'];
            $uid = $params['uid'];    
            $vcpu = $params['vcpu'];    
            $mem = $params['mem'];
            $template = $params['template']; 

            if($this->vmRepeat($uid, $name)) {
                    return 'error';
            }

            $sql = "INSERT INTO vmlist (uid, name, host, mem) VALUES(?,?,?,?)";
            $uuid = $this->createVM($host, $vcpu, $mem, $template, $uid, $name);
            if($this->SQLClass->execute($sql, array($uid, $name, $host, $mem))) {
                return array('uuid' => $uuid);
            }
        } else {
            $name = $params['name'];
            $isadmin = $params['isadmin'];
            $uid = $params['uid'];    
            $vcpu = $params['vcpu'];    
            $mem = $params['mem'];
            $template = $params['template'];    

            if($this->vmRepeat($uid, $name)) {
                return 'error';
            }

            $sql = "INSERT INTO pending_list (uid, name, vcpu, mem, template, ts, status) VALUES(?,?,?,?,?,?,?)";

            if($this->SQLClass->execute($sql, array($uid, $name, $vcpu, $mem, $template, time(), 0))) { // 0 is unchecked
                return 'success';
            } else {
                return 'error';
            }
        }
    } 
    
    private function vmRepeat($uid, $name) {
        foreach($this->SQLClass->select('SELECT name FROM vmlist WHERE uid=?',array($uid)) as $row) {
            if($row['name'] == $name) return true;
        }

        return false;
    }


    public function hostInfo() {
                
        $sql = "SELECT host,uid,name, mem FROM vmlist";
        $vms = $this->SQLClass->select($sql,array());
        $vcpu_used = 0;
        $mem_used = 0;


        $host = array();

        for($i= 0; $i < count($this->ips); $i++) {
            $node = $this->libvirt[$i]->host_get_node_info();

            $host[$i] = ['vcpu_max' => $node['cpus'], 'mem_max' => number_format($node['memory']/1048576, 2 , '.', ' ') - 0.5, 'vcpu_used' => 0, 'mem_used' => 0];
        }
        
        foreach($vms as $vm) {
            $name = $vm['uid']."-".$vm['name'];
            $res = $this->libvirt[$vm['host']]->get_domain_by_name($name);
            $dom = $this->libvirt[$vm['host']]->domain_get_info($res);
            $host[$vm['host']]['vcpu_used'] += (int) $dom['nrVirtCpu'];
            $host[$vm['host']]['mem_used'] += (int) $vm['mem'];
        }

        return $host;
    }

    private function createVM($host, $vcpu, $mem, $template, $uid, $name) {
        $memory = ((int)$mem)*1024*1024;
        exec('ssh root@'.$this->ips[$host].' cp /var/lib/libvirt/images/'.$this->templates[$template].' /var/lib/libvirt/images/'.$uid.'-'.$name.'.qcow2');
        exec('ssh root@'.$this->ips[$host].' chown -R qemu:qemu /var/lib/libvirt/images/'.$uid.'-'.$name.'.qcow2');
        $xml = "
        <domain type='kvm'>
          <name>".$uid."-".$name."</name>
          <memory unit='KiB'>$memory</memory>
          <currentMemory unit='KiB'>$memory</currentMemory>
          <vcpu placement='static' current='$vcpu'>$vcpu</vcpu>
          <os>
            <type arch='x86_64' machine='pc-i440fx-rhel7.0.0'>hvm</type>
            <boot dev='hd'/>
          </os>
          <features>
            <acpi/>
            <apic/>
            <pae/>
          </features>
          <clock offset='localtime'/>
          <on_poweroff>destroy</on_poweroff>
          <on_reboot>restart</on_reboot>
          <on_crash>restart</on_crash>
          <devices>
            <emulator>/usr/libexec/qemu-kvm</emulator>
            <disk type='file' device='disk'>
              <driver name='qemu' type='qcow2'/>
              <source file='/var/lib/libvirt/images/".$uid."-".$name.".qcow2'/>
              <target dev='vda' bus='virtio'/>
            </disk>
            <controller type='usb' index='0'/>
            <controller type='pci' index='0' model='pci-root'/>
            <interface type='bridge'>
              <mac address='".$this->libvirt[$host]->generate_random_mac_addr()."'/>
              <source bridge='br0'/>
              <model type='virtio'/>
            </interface>
            <serial type='pty'>
              <target port='0'/>
            </serial>
            <console type='pty'>
              <target type='serial' port='0'/>
            </console>
            <input type='mouse' bus='ps2'/>
            <input type='keyboard' bus='ps2'/>
            <graphics type='vnc' port='-1' autoport='yes' listen='0.0.0.0' />
            <video>
              <model type='vga' vram='16384' heads='1'/>
            </video>
            <memballoon model='virtio' />
          </devices>
        </domain> 
        ";


        $res = $this->libvirt[$host]->domain_define($xml);
        $uuid = libvirt_domain_get_uuid_string($res);
        $domName = $this->libvirt[$host]->domain_get_name_by_uuid($uuid);
        $this->libvirt[$host]->domain_start($domName);
        
        return $uuid;
    }

    public function getpendingList() {
        $userArray = $this->user->getUser();
        $uid = $userArray['uid'];
        $where = $userAray['isadmin']  == '1'? "1" : "uid = ?";
        $sql = "SELECT * FROM pending_list WHERE (status = 1 OR status = 0) AND $where";
        $list = array();
        $params = $userArray['isadmin']== '1' ? array() : array($uid);
        $result = $this->SQLClass->select($sql, $params);
        foreach($result as $request) {
            $request['template'] = substr($this->templates[$request['template']],0,-6);
            array_push($list, $request);
        
        }
        $list['isadmin'] = $userArray['isadmin'];
    
        return $list;
    }


    public function deleteTemplate($params) {
        $template = $params['template'];

        $result = $this->SQLClass->execute('DELETE FROM templates WHERE name=?', array($template)) ? 'success' : 'error';

        if($result == 'success') {
            exec("rm -rf uploaded/$template.qcow2");
            foreach($this->ips as $host) {
                exec("ssh root@$host rm -rf /var/lib/libvirt/images/$template.qcow2");
            }
        }
        
        return array('status' => $result);
    }


    //$host is string that it is ip address
    public function domainControl($params) {
        $msg = '';
        $state = '';
        $host = $params['host'];
        $action = $params['action'];
        $expectState = $action == 'start' ? 'running' : 'shutoff';
        $domName = $this->libvirt[$host]->domain_get_name_by_uuid($params['uuid']);
        $token = $host.'-'.$params['uuid'];
        $res = $this->libvirt[$host]->get_domain_by_name($domName);
        

        switch($action) {
            case 'start':
                $msg = $this->libvirt[$host]->domain_start($domName) ? 'success' : 'error';
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];

                    $vnc = $this->libvirt[$host]->domain_get_vnc_port($res);
                    $this->tokenfileControl('add',$token, $this->ips[$host].':'.$vnc);
                    $state = $waiting['state'];
                }
                break;

            case 'shutdown':
                $msg = $this->libvirt[$host]->domain_shutdown($domName) ? 'success' : 'error';
                
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];
                    $this->tokenfileControl('delete',$token, $this->ips[$host].':'.$vnc);
                    $state = $waiting['state'];
                }

                break;

            case 'forceoff':
                $msg = $this->libvirt[$host]->domain_destroy($domName) ? 'success' : 'error';
                
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running($domName) ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];
                    $this->tokenfileControl('delete',$token);
                    $state = $waiting['state'];
                }

                break;

            case 'delete':

                $msg =  $this->deleteVM($domName, $host) ? 'success_delete' : 'error';

                break;
        }


        return array('msg'=>$msg, 'state'=>$state);

    }

    public function deleteVM($vmname, $host, $force = false) {
        $res = $this->libvirt[$host]->get_domain_by_name($vmname);
		$uuid = libvirt_domain_get_uuid_string($res);
        $token = $host . '-'. $uuid;
        $uidANDname = explode("-", $vmname);
        $uid = $uidANDname[0];
        $name = $uidANDname[1];

        if($this->libvirt[$host]->domain_is_running($vmname) && !$force) {
           
           return false;

        } else {

            if($this->libvirt[$host]->domain_is_running($vmname) && $this->libvirt[$host]->domain_destroy($vmname)) {
                $waiting = $this->waitingCurrState('shutoff', $this->libvirt[$host]->domain_is_running($vmname) ? 'running' : 'shutoff', $vmname, $host);
                file_put_contents('wait.txt',print_r($waiting, true)."\n", FILE_APPEND);

                if($waiting['msg'] === 'error') {
                    return false;
                 }
            }

            $state = $this->libvirt[$host]->domain_undefine($vmname);

            if($state) {
                exec('ssh root@'.$this->ips[$host].' rm -rf /var/lib/libvirt/images/'.$vmname.'.qcow2');
                $this->SQLClass->execute("DELETE FROM vmlist WHERE uid=? AND name=?",array($uid, $name));
                $this->tokenfileControl('delete',$token);

                return true;
            } else {

                return false;
            }


        }
        
        
 
    }
    
    private function tokenfileControl($action, $token, $vnchost=false) {
        $tokenarr = explode("\n", file_get_contents('token.list',false));
        unset($tokenarr[count($tokenarr) - 1]);

        if($action == 'delete') {

            for($i=0; $i < count($tokenarr); $i++) {
                $key = explode(":", $tokenarr[$i]);
                if($key[0] == $token) {
                    unset($tokenarr[$i]);
                    break;
                }
            }
            
            $file = implode("\n", $tokenarr);
            file_put_contents('token.list',$file."\n");

        } else if($action == 'add' && $vnchost) {
            for($i=0;$i < count($tokenarr); $i++) {
                $key = explode(":", $tokenarr[$i]);
                if($key[0] == $token) {
                    return;
                }

            }

            file_put_contents('token.list', $token.': '.$vnchost."\n",FILE_APPEND);
            
        }
    }

    private function waitingCurrState($expectState, $currState, $domName, $host) {
        $currState = $this->libvirt[$host]->domain_is_running($domName) ? 'running' : 'shutoff';
        $time = time();

        while($currState != $expectState) {
            sleep(1);
            $currState = $this->libvirt[$host]->domain_is_running($domName) ? 'running' : 'shutoff';
            if(time() - $time > 30) {
                return array('msg'=>'error', 'state'=>$currState);
            }
        }

        return array('msg'=>'success', 'state'=>$currState);
        

    
    }





}
?>
