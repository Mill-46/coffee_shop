<?php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = ''; 
    private $database = 'dbKafeLatte';
    public $conn;
    
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function prepare($query) {
        return $this->conn->prepare($query);
    }
    
    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Query error: " . $this->conn->error);
            return false;
        }
        return $result;
    }
    
    public function close() {
        $this->conn->close();
    }
}

define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
?>