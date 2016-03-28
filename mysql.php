<?php

class MySQL {
	private $dbhost = '127.0.0.1';
	private $dbuser = 'root';
	private $dbpass = 'inwin888';
	private $dbname = 'libvirt';
	private $conn;

	public function __construct() {
		$this->conn = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
		mysql_query("SET NAMES 'utf8'");
		mysql_select_db($this->dbname);
	}

	public function select($sql) {
		$arr = array();
		$result = mysql_query($sql);
		if($result === false) {
			die(mysql_error());
		}
		while($row = mysql_fetch_array($result)) {
			$arr[] = $row;
		}
		
		return $arr;
	}

    public function insert($sql) {
        $result = mysql_query($sql);
        if($result === false){
            die(mysql_error());
        }
        
        return true;
    
    }

    public function update($sql) {
        $result = mysql_query($sql);
        if($result === false){
            die(mysql_error());
        }
        
        return true;
    
    }

    public function delete($sql) {
        $result = mysql_query($sql);
        if($result === false){
            die(mysql_error());
        }
        
        return true;
    
    }

}
	

?>
