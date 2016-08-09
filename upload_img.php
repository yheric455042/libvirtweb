<?php
    require_once('mysql.php');
    require_once('user.php');
    require_once('config.php');
    $mysql = new MySQL();
    $user = new User($mysql);
    $userArr = $user->getUser();
    $isadmin = $userArr['isadmin'] == '1' ? true : false;

    if(isset($_FILES['input-img']) && $_FILES['input-img']['error'] == 0 && (pathinfo($_FILES['input-img']['name'], PATHINFO_EXTENSION) == 'qcow2' || pathinfo($_FILES['input-img']['name'], PATHINFO_EXTENSION) == 'img') && $isadmin) {
        
        $files = $_FILES['input-img']['name'];
        move_uploaded_file($_FILES['input-img']['tmp_name'], getcwd()."/uploaded/$files");
        
        if(pathinfo($files, PATHINFO_EXTENSION) == 'img') { 
            exec("qemu-img convert -f raw -O qcow2 uploaded/$files uploaded/".substr($files,0,-3)."qcow2");
            exec("rm -rf uploaded/$files");
            $files = substr($files,0,-3)."qcow2";

        }
        
        foreach($hosts_ip as $host) {
            exec("scp ".getcwd()."/uploaded/$files root@$host:/var/lib/libvirt/images/");
        }
         
        $mysql->execute('INSERT INTO templates (name, file_name) VALUES (?,?)',array(substr($files,0,-6), $files));
        

        echo json_encode(array('status' => 'success', 'name' => substr($files, 0, -4)));
    } else {
        echo json_encode(array('status'=>'error'));
    }

?>

