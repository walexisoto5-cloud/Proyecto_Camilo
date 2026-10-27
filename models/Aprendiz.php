<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Aprendiz - Responsable exclusivo del acceso a datos, métricas e importación de aprendices
 */
class Aprendiz
{
    private $conn;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Obtiene los datos del perfil de un aprendiz según su id_usuario
     */
    public function obtenerPorUsuario(int $idUsuario): ?array
    {
        $sql = "SELECT ap.id_aprendiz, ap.fk_ficha, ap.codigo_rfid, 
                       f.numero_ficha, f.nombre_programa, f.jornada,
                       u.nombre, u.apellido, u.identificacion, u.nombre_usuario
                FROM aprendiz ap
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                WHERE u.id_usuario = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    /**
     * Obtiene las métricas consolidadas de asistencia, historial y excusas para el portal del aprendiz
     */
    public function obtenerResumenPortal(int $idAprendiz): array
    {
        $hoy = date('Y-m-d');

        // 1. Total sesiones
        $stmtTot = $this->conn->prepare("SELECT COUNT(*) as total FROM asistencia WHERE fk_aprendiz = ?");
        $stmtTot->bind_param("i", $idAprendiz);
        $stmtTot->execute();
        $totalSesiones = (int)($stmtTot->get_result()->fetch_assoc()['total'] ?? 0);

        // 2. Asistencias a tiempo
        $stmtAsist = $this->conn->prepare("SELECT COUNT(*) as total FROM asistencia 
                                           WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) = 'a tiempo' OR LOWER(estado_entrada) = 'presente')");
        $stmtAsist->bind_param("i", $idAprendiz);
        $stmtAsist->execute();
        $totalAsistencias = (int)($stmtAsist->get_result()->fetch_assoc()['total'] ?? 0);

        // 3. Retardos
        $stmtRet = $this->conn->prepare("SELECT COUNT(*) as total FROM asistencia 
                                         WHERE fk_aprendiz = ? AND LOWER(estado_entrada) LIKE '%retardo%'");
        $stmtRet->bind_param("i", $idAprendiz);
        $stmtRet->execute();
        $totalRetardos = (int)($stmtRet->get_result()->fetch_assoc()['total'] ?? 0);

        // 4. Inasistencias
        $stmtFaltas = $this->conn->prepare("SELECT COUNT(*) as total FROM asistencia 
                                            WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) LIKE '%inasistencia%' OR LOWER(estado_entrada) LIKE '%ausente%' OR LOWER(estado_entrada) LIKE '%falta%')");
        $stmtFaltas->bind_param("i", $idAprendiz);
        $stmtFaltas->execute();
        $totalFaltas = (int)($stmtFaltas->get_result()->fetch_assoc()['total'] ?? 0);

        // Cálculo porcentaje
        $asistenciasValidas = $totalAsistencias + $totalRetardos;
        $porcentajeAsistencia = ($totalSesiones > 0) ? round(($asistenciasValidas / $totalSesiones) * 100) : 100;

        // 5. Asistencia de hoy
        $stmtHoy = $this->conn->prepare("SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1");
        $stmtHoy->bind_param("is", $idAprendiz, $hoy);
        $stmtHoy->execute();
        $asistenciaHoy = $stmtHoy->get_result()->fetch_assoc();

        // 6. Historial de marcajes
        $sqlHist = "SELECT a.*, f.nombre_programa, 
                           u_inst.nombre AS nombre_instructor, u_inst.apellido AS apellido_instructor
                    FROM asistencia a
                    LEFT JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                    LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                    LEFT JOIN usuario u_inst ON f.fk_usuario = u_inst.id_usuario
                    WHERE a.fk_aprendiz = ?
                    ORDER BY a.fecha_asistencia DESC, a.entrada DESC";
        $stmtHist = $this->conn->prepare($sqlHist);
        $stmtHist->bind_param("i", $idAprendiz);
        $stmtHist->execute();
        $historial = $stmtHist->get_result()->fetch_all(MYSQLI_ASSOC);

        // 7. Mis excusas
        $sqlExc = "SELECT e.*, a.fecha_asistencia, 
                          u_rev.nombre AS revisor_nombre, u_rev.apellido AS revisor_apellido
                   FROM excusa e
                   INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                   LEFT JOIN usuario u_rev ON e.fk_usuario_instructor = u_rev.id_usuario
                   WHERE a.fk_aprendiz = ?
                   ORDER BY (CASE WHEN e.estado = 'Pendiente' THEN 1 ELSE 2 END), e.id_excusa DESC";
        $stmtExc = $this->conn->prepare($sqlExc);
        $stmtExc->bind_param("i", $idAprendiz);
        $stmtExc->execute();
        $misExcusas = $stmtExc->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'totalSesiones' => $totalSesiones,
            'totalAsistencias' => $totalAsistencias,
            'totalRetardos' => $totalRetardos,
            'totalFaltas' => $totalFaltas,
            'porcentajeAsistencia' => $porcentajeAsistencia,
            'asistenciaHoy' => $asistenciaHoy,
            'historial' => $historial,
            'misExcusas' => $misExcusas
        ];
    }

    /**
     * Procesa la importación masiva de aprendices desde un archivo CSV
     */
    public function importarDesdeCsv(string $rutaTmp, int $idFicha): array
    {
        $lineas = file($rutaTmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lineas)) {
            return ['exito' => false, 'mensaje' => 'El archivo CSV se encuentra vacío.'];
        }

        // Obtener rol aprendiz
        $resRol = $this->conn->query("SELECT id_rol FROM rol WHERE LOWER(nombre_rol) = 'aprendiz' LIMIT 1");
        $fkRolAprendiz = ($resRol && $row = $resRol->fetch_assoc()) ? (int)$row['id_rol'] : 3;

        $importados = 0;
        $omitidos = 0;

        foreach ($lineas as $idx => $linea) {
            $delim = (strpos($linea, ';') !== false) ? ';' : ',';
            $datos = str_getcsv(trim($linea), $delim);

            if (empty($datos) || count($datos) < 3) continue;

            // Omitir cabecera
            if ($idx === 0 && (stripos($datos[0], 'identificacion') !== false || stripos($datos[0], 'documento') !== false || stripos($datos[1], 'nombre') !== false)) {
                continue;
            }

            $identificacion = trim($datos[0] ?? '');
            $nombre = trim($datos[1] ?? '');
            $apellido = trim($datos[2] ?? '');
            $usuario = trim($datos[3] ?? '') ?: $identificacion;
            $telefono = trim($datos[4] ?? '');
            $rfid = trim($datos[5] ?? '');
            $rfidValor = ($rfid === '') ? null : $rfid;

            if ($identificacion === '' || $nombre === '' || $apellido === '') {
                $omitidos++;
                continue;
            }

            $checkUser = $this->conn->prepare("SELECT id_usuario FROM usuario WHERE identificacion = ? OR nombre_usuario = ?");
            $checkUser->bind_param("ss", $identificacion, $usuario);
            $checkUser->execute();
            $userRes = $checkUser->get_result()->fetch_assoc();

            if ($userRes) {
                $idUsuario = (int)$userRes['id_usuario'];
                $checkAp = $this->conn->prepare("SELECT id_aprendiz FROM aprendiz WHERE fk_usuario = ?");
                $checkAp->bind_param("i", $idUsuario);
                $checkAp->execute();
                if (!$checkAp->get_result()->fetch_assoc()) {
                    $insAp = $this->conn->prepare("INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)");
                    $insAp->bind_param("sii", $rfidValor, $idFicha, $idUsuario);
                    $insAp->execute();
                    $importados++;
                } else {
                    $omitidos++;
                }
            } else {
                $hash = password_hash($identificacion, PASSWORD_DEFAULT);
                $insUser = $this->conn->prepare("INSERT INTO usuario (nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, fk_rol, estado) 
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
                $insUser->bind_param("ssssssi", $usuario, $hash, $nombre, $apellido, $identificacion, $telefono, $fkRolAprendiz);
                if ($insUser->execute()) {
                    $idUsuario = (int)$this->conn->insert_id;
                    $insAp = $this->conn->prepare("INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)");
                    $insAp->bind_param("sii", $rfidValor, $idFicha, $idUsuario);
                    $insAp->execute();
                    $importados++;
                } else {
                    $omitidos++;
                }
            }
        }

        return [
            'exito' => true,
            'importados' => $importados,
            'omitidos' => $omitidos
        ];
    }
}
?>