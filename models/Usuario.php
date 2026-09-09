<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Buscar usuario por su número de documento o correo usando MySQLi
    public function obtenerPorIdentificador($identificador) {
        $query = "SELECT u.id, u.numero_documento, u.correo, u.password_hash, u.estado, r.nombre AS rol
                  FROM " . $this->table . " u
                  INNER JOIN roles r ON u.rol_id = r.id
                  WHERE u.numero_documento = ? OR u.correo = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $identificador, $identificador);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
}