<?php
require_once('mysql.php');
require_once('controller.php');
require_once('config.php');
session_start();
if(!isset($_SESSION['uid'])) {
	header('Location : login.php');
}

$mysql = new MySQL();
$controller = new Controller($mysql, $hosts_ip);

switch ($_POST['action']) {
	case 'login':
		echo $controller->login($_POST['params']);
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

    }

?>
