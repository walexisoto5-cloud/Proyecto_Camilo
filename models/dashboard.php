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
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    // 2. Asistencias de hoy
    public function obtenerAsistenciasHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE()";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    // 3. Retardos de hoy
    public function obtenerRetardosHoy()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE() AND LOWER(estado_entrada) LIKE '%retardo%'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    // 4. Excusas pendientes
    public function obtenerExcusasPendientes()
    {
        $sql = "SELECT COUNT(*) as total FROM excusa WHERE LOWER(estado) = 'pendiente'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    // 5. Total de fichas
    public function obtenerTotalFichas()
    {
        $sql = "SELECT COUNT(*) as total FROM ficha";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }

    // 6. Lista de Fichas (para select en vistas)
    public function obtenerFichas()
    {
        $sql = "SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha ORDER BY id_ficha DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 7. Últimos marcajes (JOIN con aprendiz y usuario)
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

    // 8. Crear Ficha
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

    // 9. Crear Instructor
    // $correo actúa como $nombre_usuario en la BD
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

    // 10. Crear Aprendiz (Crea usuario con rol 3 y vincula a tabla aprendiz)
    public function crearAprendiz($identificacion, $nombre, $apellido, $correo, $contrasena, $idFicha, $codigoRfid = null)
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // 1. Insertar en tabla usuario con rol de Aprendiz (3)
        $sqlUsuario = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                       VALUES (?, ?, ?, ?, ?, 3)";
        $stmtUser = $this->conn->prepare($sqlUsuario);
        if (!$stmtUser) {
            error_log("Error al preparar usuario aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtUser->bind_param("sssss", $identificacion, $nombre, $apellido, $correo, $hash);
        if (!$stmtUser->execute()) {
            error_log("Error al ejecutar usuario aprendiz: " . $stmtUser->error);
            return false;
        }

        $idUsuario = $this->conn->insert_id;

        // 2. Insertar en tabla aprendiz vinculando ficha y usuario
        $sqlAprendiz = "INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)";
        $stmtAp = $this->conn->prepare($sqlAprendiz);
        if (!$stmtAp) {
            error_log("Error al preparar aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtAp->bind_param("sii", $codigoRfid, $idFicha, $idUsuario);
        return $stmtAp->execute();
    }

    // 11. Guardar Excusa vinculada al aprendiz y a su registro de asistencia
    public function guardarExcusa($documentoAprendiz, $fechaFalta, $motivo, $nombreArchivo)
    {
        // 1. Buscar aprendiz por número de identificación
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

        // 2. Comprobar si ya existe registro de asistencia para esa fecha o crearlo con estado falta
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

        // 3. Insertar la excusa
        $sqlExcusa = "INSERT INTO excusa (archivo, observacion, estado, fecha_subida, fk_asistencia) 
                      VALUES (?, ?, 'Pendiente', CURDATE(), ?)";
        $stmtExcusa = $this->conn->prepare($sqlExcusa);
        if (!$stmtExcusa) return false;

        $stmtExcusa->bind_param("ssi", $nombreArchivo, $motivo, $idAsistencia);
        return $stmtExcusa->execute();
    }
}
?>