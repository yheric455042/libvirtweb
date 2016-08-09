<?php
require_once('mysql.php');

class User {
    private $mysql;

    public function __construct($mysql) {
        $this->mysql = $mysql;
    }
    
    public function getUser() {
        session_start();
         
        return array('uid' => $_SESSION['uid'], 'isadmin' => $_SESSION['isadmin']);
    }

	public function login($params) {
		$uid = $params['uid'];
		$password = $params['password'];
		$sql = "SELECT * FROM user WHERE uid= ? AND passwd= ?";
        $params = array($uid, $password);

		$query = $this->mysql->select($sql, $params);
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

    public function getuserList() {
        $userArray = $this->getUser();
        $isadmin = $userArray['isadmin'] == '1' ? true : false;
        
        if($isadmin) {
            $users = $this->mysql->select("SELECT uid, displayname, email, isadmin FROM user",array());

            return $users;
        }

        return 'notadmin';
    }

    public function userCreate($params) {
        $uid = $params['uid'];
        $password = $params['password'];
        $displayname = $params['displayname'];
        $email = $params['email'];
        $user = $this->getUser();
        $isadmin = $user['isadmin'] == '1' ? true :false;
        
        $sql = "INSERT INTO user (uid, passwd, displayname, email) VALUES(?,?,?,?)";
        $status = $isadmin ? $this->mysql->execute($sql, array($uid,$password, $displayname, $email)) : false;
        if($status) {
            return 'success';
        }
        
        return 'error';

    }

    public function modifyPassword($params) {
        $userArray = $this->getUser();
        $uid = $userArray['uid'];
        $oldpassword = $params['oldpass'];
        $newpassword = $params['newpass'];
        if(count($this->mysql->select('SELECT COUNT(*) FROM user WHERE uid= ? AND passwd = ?', array($uid, $oldpassword))) > 0) {
            $msg =  $this->mysql->execute('UPDATE user SET passwd = ? WHERE uid = ?', array($newpassword, $uid)) ? 'success' : 'error' ;

            return $msg;
        }
        return 'error';
    }

    public function removeUser($params) {
        $userArray = $this->getUser();
        $isadmin = $userArray['isadmin'] ? true: false;

        $user = $params['user'];
        $msg = $isadmin && $user != 'admin' ? 'success' : 'error';
        if($isadmin && $user != 'admin') {
            $this->mysql->execute('DELETE FROM user WHERE uid= ?', array($user));
            
        }

        return $msg;
    }

    public function modifyAdmin($params) {
        $userArray = $this->getUser();
        $isadmin = $userArray['isadmin'] ? true: false;

        $user = $params['user'];
        
        $msg = $isadmin && $user != 'admin' ? 'success' : 'error';
        if($isadmin && $user != 'admin') {
            $this->mysql->execute('UPDATE user SET isadmin = ? WHERE uid= ?', array(intval($params['admin']), $user));
            
        }

        return $msg;
    }


}

?>
