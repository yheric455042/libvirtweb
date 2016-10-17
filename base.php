<?php

require_once('mysql.php');
require_once('controller.php');
require_once('user.php');
require_once('config.php');


$mysql = new MySQL();
$user = new User($mysql);
session_start();

if($_POST['action'] != 'login') {
    $count = isset($_SESSION['uid']) ? count($mysql->select('SELECT * FROM user WHERE uid = ?',array($_SESSION['uid']))) : 0;
    if($count == 0) {
        session_unset();
        session_destroy();
        exit();
    }

}

$mysql->execute('DELETE FROM pending_list WHERE ts + ? < ?', array(60*60*24*180, time()));


$template_file = [];
$templates = $mysql->select('SELECT * from templates',array());
foreach($templates as $template) {
    $template_file[] = $template['file_name'];
}

$controller = new Controller($mysql, $hosts_ip, $template_file, $user);


switch ($_POST['action']) {
    case 'islogin': 
        return isset($_SESSION['uid']);
        break;

	case 'login':
		echo $user->login($_POST['params']);
		break;
	
	case 'logout':
        session_start();
		session_destroy();
		echo 'success';
		break;

	case 'getuid':
        session_start();
		echo json_encode(array('uid'=>$_SESSION['uid'], 'isadmin'=> $_SESSION['isadmin']));
		break;
	
	case 'getVMList':
		$vms = $controller->getVMList($_POST['params']);
		echo json_encode($vms);
		break;

    case 'domainControl':
        echo json_encode($controller->domainControl($_POST['params']));
        break;

    case 'hostCount':
        echo count($hosts_ip);
        break;


    case 'pendingCreate':
        echo $controller->pendingCreate($_POST['params']);
        break;

    case 'pendingList':
        echo json_encode($controller->getpendingList());
        break;

    case 'userVMCreate':
        echo $controller->pendingCreate($_POST['params']);
        break;

    case 'getAllvmName':
        echo json_encode($controller->getAllvmName($_POST['params']));
        break;

    case 'userList':
        echo json_encode($user->getuserList());
        break;

    case 'userCreate':
        echo $user->userCreate($_POST['params']);
        break;

    case 'hostInfo':
        echo json_encode($controller->hostInfo());
        break;

    case 'modifyPassword':
        echo $user->modifyPassword($_POST['params']);
        break;

    case 'removeUser':
        $uid = $_POST['params']['user'];
        $state = true;

        if($user->isadmin()) {
            $vms = $mysql->select('SELECT host, name FROM vmlist WHERE uid = ?',array($uid));
            foreach($vms as $vm) {
                $vmname = $uid .'-'. $vm['name']; 
                if(!$controller->deleteVM($vmname, intval($vm['host']), true)) {
                    $state = false;
                    break;
                }
            }
        }
        $state = $state && $mysql->execute('DELETE FROM pending_list WHERE uid = ?',array($uid));

        echo $state ? $user->removeUser($uid) : 'error';

        break;

    case 'modifyAdmin':
        echo $user->modifyAdmin($_POST['params']);
        break;

    case 'getTemplate': 
        echo json_encode($templates);
        break;

    case 'deleteTemplate':
        echo json_encode($controller->deleteTemplate($_POST['params']));




}

?>
