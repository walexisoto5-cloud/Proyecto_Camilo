<?php

date_default_timezone_set('America/Bogota');

class Database {
    private $host = "localhost";
    private $db_name = "asistencia_db"; 
    private $username = "root";
    private $password = ""; 
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8");
        $this->conn->query("SET time_zone = '-05:00'");

        return $this->conn;
    }
}