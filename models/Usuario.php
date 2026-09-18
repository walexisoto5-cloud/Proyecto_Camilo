<?php
class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerPorUsuario($nombre_usuario) {
        $query = "SELECT u.*, r.nombre_rol 
                  FROM usuario u 
                  INNER JOIN rol r ON u.fk_rol = r.id_rol 
                  WHERE u.nombre_usuario = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_assoc();
    }
}
?>