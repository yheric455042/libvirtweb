<?php
require_once('mysql.php');
require_once('libvirt.php');

class Controller {
	
	private $SQLClass;
	private $libvirt = array();
    private $ips;
    private $templates;
    //private $config;
	public function __construct($SQLClass, $hosts_ip, $templates) {
		$this->SQLClass = $SQLClass;
        $this->templates = $templates;
        $this->ips = $hosts_ip;
        for($i = 0; $i < sizeof($hosts_ip); $i++) {
		    $this->libvirt[$i] = new Libvirt('qemu+ssh://root@'.$hosts_ip[$i].'/system');
        }
	}
    
    private function getUser() {
        session_start();
        
        return array('uid' => $_SESSION['uid'], 'isadmin' => $_SESSION['isadmin']);
    }

	public function login($params) {
		$uid = $params['uid'];
		$password = $params['password'];
		$sql = "SELECT * FROM user WHERE uid='$uid' AND passwd='$password'";

		$query = $this->SQLClass->select($sql);
		if(sizeof($query)) {
			foreach($query as $userarray) {
				
				session_start();
				$_SESSION['uid'] = $uid;
				$_SESSION['isadmin'] = $userarray['isadmin']; 
				return 'success';
			}
			
		} else {

			return 'error';
		}
	}
	
	public function getVMList($params) {
        $userArray = $this->getUser();
        $uid = $userArray['uid'];
        $where = $userArray['isadmin'] == 'true' ? "1" : "uid = '$uid'";

		$vms = $this->SQLClass->select("SELECT * FROM vmlist WHERE $where");
        $outputs = array();
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
			$token = 'test';//demo test
			array_push($outputs, array('uid'=>$vm['uid'], 'name'=>$vm['name'], 'vcpu'=>$cpu, 'mem'=>$vm['mem']."G", 'disk'=>$disk, 'arch'=>$arch ,'state'=>$state, 'uuid'=>$uuid, 'token'=>$token, 'host'=>$vm['host']));
		}
		return $outputs;
	}

    public function pendingCreate($params) {
        $name = $params['name'];    
        $isadmin = $params['isadmin'];
        $uid = $params['uid'];    
        $vcpu = $params['vcpu'];    
        $mem = $params['mem'];    
        $template = $params['template'];    
        $host = $params['host'];

        if($isadmin == 'true') {
            $sql = "INSERT INTO vmlist (uid, name, host, mem) VALUES('$uid','$name',$host, $mem)";

            if($this->SQLClass->insert($sql)) {
                return array('uuid' => $this->createVM($host, $vcpu, $mem, $template, $uid, $name));
            }
        } else {
        
            $sql = "INSERT INTO pending_list (uid, name, vcpu, mem, template) VALUES('$uid', '$name', $vcpu, $mem, $template)";

            if($this->SQLClass->insert($sql)) {
                return 'success';
            } else {
                return 'error';
            }
        }
    }  

    private function createVM($host, $vcpu, $mem, $template, $uid, $name) {
        $memory = ((int)$mem)*1024*1024;
        exec('ssh root@'.$this->ips[$host].' cp /var/lib/libvirt/images/'.$this->templates[$template].' /var/lib/libvirt/images/'.$uid.'-'.$name.'.qcow2');
        $xml = "
        <domain type='qemu'>
          <name>".$uid."-".$name."</name>
          <memory unit='KiB'>$memory</memory>
          <currentMemory unit='KiB'>$memory</currentMemory>
          <vcpu placement='static'>$vcpu</vcpu>
          <os>
            <type arch='x86_64' machine='pc-i440fx-rhel7.0.0'>hvm</type>
            <boot dev='hd'/>
          </os>
          <features>
            <acpi/>
            <apic/>
          </features>
          <clock offset='utc'>
            <timer name='rtc' tickpolicy='catchup'/>
            <timer name='pit' tickpolicy='delay'/>
            <timer name='hpet' present='no'/>
          </clock>
          <on_poweroff>destroy</on_poweroff>
          <on_reboot>restart</on_reboot>
          <on_crash>restart</on_crash>
          <pm>
            <suspend-to-mem enabled='no'/>
            <suspend-to-disk enabled='no'/>
          </pm>
          <devices>
            <emulator>/usr/libexec/qemu-kvm</emulator>
            <disk type='file' device='disk'>
              <driver name='qemu' type='qcow2'/>
              <source file='/var/lib/libvirt/images/".$this->templates[$template]."'/>
              <target dev='vda' bus='virtio'/>
            </disk>
            <controller type='usb' index='0' model='ich9-ehci1' />
            <controller type='usb' index='0' model='ich9-uhci1' />
            <controller type='pci' index='0' model='pci-root'/>
            <controller type='ide' index='0' />
            <controller type='virtio-serial' index='0' />
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
          </devices>
        </domain> 
        ";


        $res = $this->libvirt[$host]->domain_define($xml);
        $uuid = libvirt_domain_get_uuid_string($res);
        $domName = $this->libvirt[$host]->domain_get_name_by_uuid($uuid);
        $this->libvirt[$host]->domain_start($domName);
        
        return $uuid;
    }

    public function getpendingList($params) {
        $userArray = getUser();
        $uid = $userArray['uid'];
        $where = $userAray['isadmin'] ? "1" : "uid = '$uid'";
        $sql = "SELECT * FROM pending_list WHERE = $where";
        $list = array();
        foreach($this->SQLClass->select($sql) as $request) {
            array_push($list, $request);
        
        }
    
        return $list;
    }
    

    //$host is string that it is ip address
    public function domainControl($params) {
        $msg = '';
        $state = '';
        $host = $params['host'];
        $action = $params['action'];
        $expectState = $action == 'start' ? 'running' : 'shutoff';
        $domName = $this->libvirt[$host]->domain_get_name_by_uuid($params['uuid']);
        $uidANDname = split($domName, "-");
        $uid = $uidANDname[0];
        $name = $uidANDname[1];
        $res = $this->libvirt[$host]->get_domain_by_name($domName);

        switch($action) {
            case 'start':
                $msg = $this->libvirt[$host]->domain_start($domName) ? 'success' : 'error';
                
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];
                    $state = $waiting['state'];
                }
                break;

            case 'shutdown':
                $msg = $this->libvirt[$host]->domain_shutdown($domName) ? 'success' : 'error';
                
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];
                    $state = $waiting['state'];
                }

                break;

            case 'forceoff':
                $msg = $this->libvirt[$host]->domain_destroy($domName) ? 'success' : 'error';
                
                if($msg == 'success') {
                    $waiting = $this->waitingCurrState($expectState, $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff', $domName, $host);
                    $msg = $waiting['msg'];
                    $state = $waiting['state'];
                }

                break;

            case 'delete':
                if(!$this->libvirt[$host]->domain_is_running($domName)) {
                    $state = $this->libvirt[$host]->domain_undefine($domName);

                    if($state) {
                        exec('ssh root@'.$this->ips[$host].' rm -rf /var/lib/libvirt/images/'.$domName.'.qcow2');
                        $this->SQLClass->delete("DELETE FROM vmlist WHERE uid='$uid' AND name='$name'");   
                        $msg = 'success_delete';

                    } else {
                        $msg =  'error';
                    }
                     
                } else {
                    $msg = 'is_running';
                
                }
                break;
        }


        return array('msg'=>$msg, 'state'=>$state);

    }
    
    private function waitingCurrState($expectState, $currState, $domName, $host) {
        $currState = $this->libvirt[$host]->domain_is_running ? 'running' : 'shutoff';
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
