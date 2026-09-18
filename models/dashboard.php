<?php
class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Total de aprendices registrados y activos
    public function obtenerTotalAprendices() {
        $query = "SELECT COUNT(*) as total FROM aprendiz WHERE estado = 'activo'";
        $resultado = $this->conn->query($query);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // Asistencias registradas el día de hoy
    public function obtenerAsistenciasHoy() {
        $fechaHoy = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM asistencia WHERE fecha = '$fechaHoy' AND estado = 'presente'";
        $resultado = $this->conn->query($query);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // Retardos / novedades registrados el día de hoy
    public function obtenerRetardosHoy() {
        $fechaHoy = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM novedad WHERE fecha = '$fechaHoy' OR tipo_novedad = 'retardo'";
        $resultado = $this->conn->query($query);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // Excusas pendientes por revisar
    public function obtenerExcusasPendientes() {
        $query = "SELECT COUNT(*) as total FROM excusa WHERE estado = 'pendiente'";
        $resultado = $this->conn->query($query);
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // Últimos marcajes/asistencias registradas para mostrar en tabla
    public function obtenerUltimasAsistencias($limite = 5) {
        $query = "SELECT a.fecha, a.hora_ingreso, a.estado, ap.nombre, ap.apellido, f.numero_ficha 
                  FROM asistencia a
                  INNER JOIN aprendiz ap ON a.fk_id_aprendiz = ap.id_aprendiz
                  LEFT JOIN ficha f ON ap.fk_id_ficha = f.id_ficha
                  ORDER BY a.fecha DESC, a.hora_ingreso DESC 
                  LIMIT $limite";
        $resultado = $this->conn->query($query);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Método para crear una nueva Ficha
    public function crearFicha($numeroFicha, $programa) {
        $stmt = $this->conn->prepare("INSERT INTO ficha (numero_ficha, programa_formacion) VALUES (?, ?)");
        $stmt->bind_param("ss", $numeroFicha, $programa);
        return $stmt->execute();
    }

    // Método para registrar un nuevo Instructor/Usuario
    public function crearInstructor($documento, $nombre, $apellido, $correo, $contrasena, $fk_id_rol = 2) {
        $passHash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO usuario (documento, nombre, apellido, correo, contrasena, fk_id_rol, estado) VALUES (?, ?, ?, ?, ?, ?, 'activo')");
        $stmt->bind_param("sssssi", $documento, $nombre, $apellido, $correo, $passHash, $fk_id_rol);
        return $stmt->execute();
    }
}
?>