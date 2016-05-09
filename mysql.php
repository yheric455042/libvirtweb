<?php

class MySQL {
	private $dbhost = '127.0.0.1';
	private $dbuser = 'root';
	private $dbpass = 'inwin888';
	private $dbname = 'libvirt';
    private $settings = array(
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' 
    );
	private $conn;

	public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->dbhost;dbname=$this->dbname;",$this->dbuser, $this->dbpass, $this->settings);
        } catch(PDOException $e) {
        
            throw new PDOException($e->getMessage());
        }
	}

	public function select($sql, $params) {
        $query = $this->conn->prepare($sql);
        try {
            if ($query->execute($params)) {
	            return $query->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        } 
	}

    public function execute($sql, $params) {
        $query = $this->conn->prepare($sql);
        try {
            if ($query->execute($params)) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        } 
    }

    
}

?>
