<?php
class Dashboard
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // 1. Total Aprendices
    public function obtenerTotalAprendices()
    {
        $sql = "SELECT COUNT(*) as total FROM aprendiz";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // 2. Asistencias de hoy
    public function obtenerAsistenciasHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE()";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // 3. Retardos de hoy
    public function obtenerRetardosHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE() AND estado_entrada = 'retardo'";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // 4. Excusas pendientes
    public function obtenerExcusasPendientes()
    {
        $sql = "SELECT COUNT(*) as total FROM excusa WHERE estado = 'Pendiente'";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    // 5. Últimos marcajes (JOIN con aprendiz y usuario)
    public function obtenerUltimasAsistencias($limite = 5)
    {
        $sql = "SELECT a.*, u.nombre, u.apellido 
                FROM asistencia a 
                INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz 
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                ORDER BY a.fecha_asistencia DESC, a.entrada DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error SQL en obtenerUltimasAsistencias: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 6. Crear Ficha
    public function crearFicha($numeroFicha, $programa, $jornada = '')
    {
        $stmt = $this->conn->prepare("INSERT INTO ficha (numero_ficha, nombre_programa, jornada) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $numeroFicha, $programa, $jornada);
        return $stmt->execute();
    }

    // 7. Crear Instructor (Adaptado exactamente al controlador)
    // $correo actúa como $nombre_usuario en la BD para el inicio de sesión
    public function crearInstructor($identificacion, $nombre, $apellido, $correo, $contrasena)
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                VALUES (?, ?, ?, ?, ?, 2)";
                
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Error en prepare SQL crearInstructor: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssss", $identificacion, $nombre, $apellido, $correo, $hash);
        return $stmt->execute();
    }
}
?>