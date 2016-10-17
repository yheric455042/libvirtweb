<?php
require_once('mysql.php');

class User {
    private $mysql;

    public function __construct($mysql) {
        $this->mysql = $mysql;
    }
    
    public function getUser() {
        return array('uid' => $_SESSION['uid'], 'isadmin' => $_SESSION['isadmin']);
    }
    
    public function isadmin() {
        return $_SESSION['isadmin'];
    }

	public function login($params) {
		$uid = $params['uid'];
		$password = $params['password'];
		$sql = "SELECT * FROM user WHERE uid= ? AND passwd= ?";
        $params = array($uid, $password);

		$query = $this->mysql->select($sql, $params);
		if(sizeof($query)) {
			foreach($query as $userarray) {
				
				$_SESSION['uid'] = $uid;
				$_SESSION['isadmin'] = intval($userarray['isadmin']); 
				return 'success';
			}
			
		} else {

			return 'error';
		}
	}

    public function getuserList() {
                
        if($this->isadmin()) {
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

        $sql = "INSERT INTO user (uid, passwd, displayname, email) VALUES(?,?,?,?)";
        $status = $this->isadmin() ? $this->mysql->execute($sql, array($uid,$password, $displayname, $email)) : false;
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

    public function removeUser($uid) {
        
        $msg = ($this->isadmin() && $uid != 'admin') ? ($this->mysql->execute('DELETE FROM user WHERE uid= ?', array($uid)) ? 'success' : 'error') : 'error';
            

        return $msg;
    }

    public function modifyAdmin($params) {
        $user = $params['user'];
        
        $msg = $this->isadmin() && $user != 'admin' ? 'success' : 'error';
        if($this->isadmin() && $user != 'admin') {
            $this->mysql->execute('UPDATE user SET isadmin = ? WHERE uid= ?', array(intval($params['admin']), $user));
            
        }

        return $msg;
    }


}

?>
