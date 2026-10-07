<?php
class Dashboard
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerTotalAprendices()
    {
        $sql = "SELECT COUNT(*) as total FROM aprendiz";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    public function obtenerAsistenciasHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE()";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    public function obtenerRetardosHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE() AND LOWER(estado_entrada) LIKE '%retardo%'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    public function obtenerExcusasPendientes()
    {
        $sql = "SELECT COUNT(*) as total FROM excusa WHERE LOWER(estado) = 'pendiente'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    public function obtenerTotalFichas()
    {
        $sql = "SELECT COUNT(*) as total FROM ficha";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    public function obtenerFichas()
    {
        $sql = "SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha ORDER BY id_ficha DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

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

    public function crearFicha($numeroFicha, $programa, $jornada = '')
    {
        $stmt = $this->conn->prepare("INSERT INTO ficha (numero_ficha, nombre_programa, jornada) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Error al preparar crearFicha: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sss", $numeroFicha, $programa, $jornada);
        return $stmt->execute();
    }

    // Asegura dinámicamente el ID del rol en la base de datos para evitar errores de clave foránea
    private function obtenerOcrearIdRol($nombreRol)
    {
        $nombreLimpio = $this->conn->real_escape_string($nombreRol);
        $sql = "SELECT id_rol FROM rol WHERE LOWER(nombre_rol) = LOWER('$nombreLimpio') LIMIT 1";
        $res = $this->conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $fila = $res->fetch_assoc();
            $id = (int)$fila['id_rol'];
            $res->free();
            return $id;
        }

        $this->conn->query("INSERT INTO rol (nombre_rol) VALUES ('$nombreLimpio')");
        return (int)$this->conn->insert_id;
    }

    public function crearInstructor($identificacion, $nombre, $apellido, $correo, $contrasena)
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $idRolInstructor = $this->obtenerOcrearIdRol('Instructor');
        
        $sql = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                VALUES (?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Error en prepare SQL crearInstructor: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssssi", $identificacion, $nombre, $apellido, $correo, $hash, $idRolInstructor);
        return $stmt->execute();
    }

    public function crearAprendiz($identificacion, $nombre, $apellido, $correo, $contrasena, $idFicha, $codigoRfid = null)
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $idRolAprendiz = $this->obtenerOcrearIdRol('Aprendiz');

        $sqlUsuario = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmtUser = $this->conn->prepare($sqlUsuario);
        if (!$stmtUser) {
            error_log("Error al preparar usuario aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtUser->bind_param("sssssi", $identificacion, $nombre, $apellido, $correo, $hash, $idRolAprendiz);
        if (!$stmtUser->execute()) {
            error_log("Error al ejecutar usuario aprendiz: " . $stmtUser->error);
            return false;
        }

        $idUsuario = $this->conn->insert_id;

        $sqlAprendiz = "INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)";
        $stmtAp = $this->conn->prepare($sqlAprendiz);
        if (!$stmtAp) {
            error_log("Error al preparar aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtAp->bind_param("sii", $codigoRfid, $idFicha, $idUsuario);
        return $stmtAp->execute();
    }

    public function guardarExcusa($documentoAprendiz, $fechaFalta, $motivo, $nombreArchivo)
    {
        $sqlBuscar = "SELECT ap.id_aprendiz, u.id_usuario 
                      FROM aprendiz ap 
                      INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                      WHERE u.identificacion = ? OR u.nombre_usuario = ? 
                      LIMIT 1";
        $stmtBuscar = $this->conn->prepare($sqlBuscar);
        if (!$stmtBuscar) return false;

        $stmtBuscar->bind_param("ss", $documentoAprendiz, $documentoAprendiz);
        $stmtBuscar->execute();
        $aprendiz = $stmtBuscar->get_result()->fetch_assoc();

        if (!$aprendiz) {
            error_log("No se encontró aprendiz con documento: " . $documentoAprendiz);
            return false;
        }

        $idAprendiz = $aprendiz['id_aprendiz'];

        // Si no existe asistencia previa para esa fecha, se genera para asociar la excusa
        $sqlAsist = "SELECT id_asistencia FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtAsist = $this->conn->prepare($sqlAsist);
        $stmtAsist->bind_param("is", $idAprendiz, $fechaFalta);
        $stmtAsist->execute();
        $asistRes = $stmtAsist->get_result()->fetch_assoc();

        if ($asistRes) {
            $idAsistencia = $asistRes['id_asistencia'];
        } else {
            $sqlInsAsist = "INSERT INTO asistencia (fecha_asistencia, estado_entrada, estado_salida, fk_aprendiz) 
                            VALUES (?, 'Inasistencia', 'Justificada', ?)";
            $stmtInsAsist = $this->conn->prepare($sqlInsAsist);
            $stmtInsAsist->bind_param("si", $fechaFalta, $idAprendiz);
            $stmtInsAsist->execute();
            $idAsistencia = $this->conn->insert_id;
        }

        $sqlExcusa = "INSERT INTO excusa (archivo, observacion, estado, fecha_subida, fk_asistencia) 
                      VALUES (?, ?, 'Pendiente', CURDATE(), ?)";
        $stmtExcusa = $this->conn->prepare($sqlExcusa);
        if (!$stmtExcusa) return false;

        $stmtExcusa->bind_param("ssi", $nombreArchivo, $motivo, $idAsistencia);
        return $stmtExcusa->execute();
    }
}
?>