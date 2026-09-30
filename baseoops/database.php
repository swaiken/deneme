<?php


$config = require_once __DIR__ . '/config.php';

class Database
{
    private $servername;
    private $username;
    private $pass;
    private $dbname;

    public $conn;

    public function __construct($config)
    {
        $this->servername = $config['servername'];
        $this->username = $config['username'];
        $this->pass = $config['password'];
        $this->dbname = $config['dbname'];
    }

    public function connect()
    {
        $this->conn = new mysqli(
            $this->servername,
            $this->username,
            $this->pass,
            $this->dbname
        );

        if ($this->conn->connect_error) {
            die("Bağlantı başarısız: " . $this->conn->connect_error);
        }
    }
}

$db = new Database($config);
/* $db->connect();  */


?>