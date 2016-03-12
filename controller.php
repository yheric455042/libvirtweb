<?php
require_once('mysql.php');
require_once('libvirt.php');

class Controller {
	
	private $SQLClass;
	private $libvirt = array();
    private $ips;
    //private $config;
	public function __construct($SQLClass, $hosts_ip) {
		$this->SQLClass = $SQLClass;
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

    private function createVM($host, $vcpu, $mem, $template) {
         
    
    }

    private function deleteVM($host, $vmname) {
         
    
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
