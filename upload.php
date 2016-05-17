<?php
    require_once('mysql.php');
    require_once('user.php');

    $mysql = new MySQL();
    $user = new User($mysql);
    $userArr = $user->getUser();
    $isadmin = $userArr['isadmin'] == '1' ? true : false;
    if(isset($_FILES['input-file']) && $_FILES['input-file']['error'] == 0 && pathinfo($_FILES['input-file']['name'], PATHINFO_EXTENSION) == 'csv' && $isadmin) {
        $file = fopen($_FILES['input-file']['tmp_name'], "r");
        $result = [];
        while(($data = fgetcsv($file, 0, ',')) !== FALSE) {
            if($data[0] == '' || count($data) < 4) continue;
            
            file_put_contents('uuuu.txt',mb_detect_encoding($data[2])."\n",FILE_APPEND);
            $code = mb_detect_encoding($data[2]) != 'UTF-8' ? 'BIG-5' : 'UTF-8';
            $data[2] = mb_convert_encoding($data[2], "UTF-8",$code);
            if($mysql->execute('INSERT INTO user (uid, passwd, displayname, email) VALUES(?,?,?,?)',$data)) {
                array_push($result, $data);
            }
        }
            
        echo json_encode(array('status' => 'success' , 'data' => $result));
    } else {
        echo json_encode(array('status'=>'error'));
    }

?>

