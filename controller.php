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
		$uid = $params['uid'];
		$vms = array();
        
		//TODO test function  will be modified
		$host = $this->libvirt;
		$doms = $host[0]->get_domains();
		
		for($i = 0; $i < sizeof($doms); $i++) {
			$name = $doms[$i];
            $res = $host[0]->get_domain_by_name($name);
			$uuid = libvirt_domain_get_uuid_string($res);
            $dom = $host[0]->domain_get_info($res);
            $mem = number_format($dom['memory'] / 1024, 2, '.', ' ').' MB';
            if($dom['memory'] == 0) $mem = ' - ';
            $cpu = $dom['nrVirtCpu'];
			$arch = $host[0]->domain_get_arch($res);
            $state = $host[0]->domain_state_translate($dom['state']);
            $vnc = $host[0]->domain_get_vnc_port($res);
			$disk = $host[0]->get_disk_capacity($res);
			$token = 'test';//demo test
			array_push($vms, array('name'=>$name, 'vcpu'=>$cpu, 'mem'=>$mem, 'disk'=>$disk, 'arch'=>$arch ,'state'=>$state, 'uuid'=>$uuid, 'token'=>$token));
		}
		
		return $vms;
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
            $sql = "INSERT INTO vmlist (uid, name, host) VALUES('$uid','$name',$host)";

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

    

    //$host is string that it is ip address
    public function domainControl($params) {
        $msg = '';
        $state = '';
        $host = $params['host'];
        $action = $params['action'];
        $expectState = $action == 'start' ? 'running' : 'shutoff';
        $domName = $this->libvirt[$host]->domain_get_name_by_uuid($params['uuid']);
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
