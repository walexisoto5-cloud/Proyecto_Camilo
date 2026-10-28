<?php

date_default_timezone_set('America/Bogota');

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Busca el archivo .env un nivel arriba de la carpeta config
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->host = $env['DB_HOST'];
            $this->username = $env['DB_USER'];
            $this->password = $env['DB_PASS'];
            $this->db_name = $env['DB_NAME'];
        } else {
            die("Error: No se encontró el archivo .env. Asegúrate de crearlo.");
        }
    }

    public function getConnection() {
        $this->conn = null;

        // Se usa $this->password que se lee del .env
        $this->conn = @new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die("Error de conexión a la base de datos (" . $this->db_name . "): " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
        $this->conn->query("SET time_zone = '-05:00'");

        return $this->conn;
    }
}
?>