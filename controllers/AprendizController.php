<?php

require_once __DIR__ . '/../config/database.php';

class AprendizController
{
    // Verifica que haya una sesión iniciada
    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    
    public function portal()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $idUsuario = (int)$_SESSION['usuario_id'];

        //Obtener los datos del aprendiz y la ficha a la que pertenece
        $sqlAprendiz = "SELECT ap.id_aprendiz, ap.fk_ficha, ap.codigo_rfid, 
                               f.numero_ficha, f.nombre_programa, f.jornada,
                               u.nombre, u.apellido, u.identificacion, u.nombre_usuario
                        FROM aprendiz ap
                        INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                        LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                        WHERE u.id_usuario = ? LIMIT 1";

        $stmtAp = $db->prepare($sqlAprendiz);
        $stmtAp->bind_param("i", $idUsuario);
        $stmtAp->execute();
        $datosAprendiz = $stmtAp->get_result()->fetch_assoc();

        if (!$datosAprendiz) {
            $nombreSesion = $_SESSION['nombre_completo'] ?? 'Usuario Demo';
            $partes = explode(' ', $nombreSesion, 2);

            $datosAprendiz = [
                'id_aprendiz' => 0,
                'nombre' => $partes[0] ?? 'Aprendiz',
                'apellido' => $partes[1] ?? 'Demo',
                'identificacion' => '---',
                'numero_ficha' => '3234082',
                'nombre_programa' => 'ADSO - Análisis y Desarrollo de Software',
                'jornada' => 'Diurna'
            ];
            $mensajeError = "Modo Previsualización: Tu usuario actual (" . htmlspecialchars($_SESSION['rol'] ?? 'Administrador') . ") no tiene registro como aprendiz. Mostrando datos de prueba para visualización.";
            $porcentajeAsistencia = 100;
            $totalAsistencias = 0;
            $totalRetardos = 0;
            $totalFaltas = 0;
            $asistenciaHoy = null;
            $historial = [];
            $misExcusas = [];

            require_once __DIR__ . '/../views/aprendiz/dashboard.php';
            return;
        }

        $idAprendiz = (int)$datosAprendiz['id_aprendiz'];

        $sqlTotal = "SELECT COUNT(*) as total FROM asistencia WHERE fk_aprendiz = ?";
        $stmtTotal = $db->prepare($sqlTotal);
        $stmtTotal->bind_param("i", $idAprendiz);
        $stmtTotal->execute();
        $totalSesiones = (int)($stmtTotal->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlAsistencias = "SELECT COUNT(*) as total FROM asistencia 
                           WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) = 'a tiempo' OR LOWER(estado_entrada) = 'presente')";
        $stmtAsist = $db->prepare($sqlAsistencias);
        $stmtAsist->bind_param("i", $idAprendiz);
        $stmtAsist->execute();
        $totalAsistencias = (int)($stmtAsist->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlRetardos = "SELECT COUNT(*) as total FROM asistencia 
                        WHERE fk_aprendiz = ? AND LOWER(estado_entrada) LIKE '%retardo%'";
        $stmtRet = $db->prepare($sqlRetardos);
        $stmtRet->bind_param("i", $idAprendiz);
        $stmtRet->execute();
        $totalRetardos = (int)($stmtRet->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlFaltas = "SELECT COUNT(*) as total FROM asistencia 
                      WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) LIKE '%inasistencia%' OR LOWER(estado_entrada) LIKE '%ausente%' OR LOWER(estado_entrada) LIKE '%falta%')";
        $stmtFaltas = $db->prepare($sqlFaltas);
        $stmtFaltas->bind_param("i", $idAprendiz);
        $stmtFaltas->execute();
        $totalFaltas = (int)($stmtFaltas->get_result()->fetch_assoc()['total'] ?? 0);

        $asistenciasValidas = $totalAsistencias + $totalRetardos;
        if ($totalSesiones > 0) {
            $porcentajeAsistencia = round(($asistenciasValidas / $totalSesiones) * 100);
        } else {
            $porcentajeAsistencia = 100;
        }

        $hoy = date('Y-m-d');
        $sqlHoy = "SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtHoy = $db->prepare($sqlHoy);
        $stmtHoy->bind_param("is", $idAprendiz, $hoy);
        $stmtHoy->execute();
        $asistenciaHoy = $stmtHoy->get_result()->fetch_assoc();

        $sqlHistorial = "SELECT a.*, f.nombre_programa, 
                                u_inst.nombre AS nombre_instructor, u_inst.apellido AS apellido_instructor
                         FROM asistencia a
                         LEFT JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                         LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                         LEFT JOIN usuario u_inst ON f.fk_usuario = u_inst.id_usuario
                         WHERE a.fk_aprendiz = ?
                         ORDER BY a.fecha_asistencia DESC, a.entrada DESC";
        $stmtHist = $db->prepare($sqlHistorial);
        $stmtHist->bind_param("i", $idAprendiz);
        $stmtHist->execute();
        $historial = $stmtHist->get_result()->fetch_all(MYSQLI_ASSOC);

        $sqlExcusas = "SELECT e.*, a.fecha_asistencia
                       FROM excusa e
                       INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                       WHERE a.fk_aprendiz = ?
                       ORDER BY e.id_excusa DESC";
        $stmtExc = $db->prepare($sqlExcusas);
        $stmtExc->bind_param("i", $idAprendiz);
        $stmtExc->execute();
        $misExcusas = $stmtExc->get_result()->fetch_all(MYSQLI_ASSOC);

        require_once __DIR__ . '/../views/aprendiz/dashboard.php';
    }

    public function marcarAsistencia()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $idUsuario = (int)$_SESSION['usuario_id'];

        $sql = "SELECT id_aprendiz FROM aprendiz WHERE fk_usuario = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $aprendiz = $stmt->get_result()->fetch_assoc();

        if (!$aprendiz) {
            header("Location: index.php?action=portal_aprendiz");
            exit();
        }

        $idAprendiz = (int)$aprendiz['id_aprendiz'];
        $hoy = date('Y-m-d');
        $ahora = date('Y-m-d H:i:s');
        $horaActual = date('H:i:s');

        $sqlCheck = "SELECT id_asistencia, entrada, salida FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bind_param("is", $idAprendiz, $hoy);
        $stmtCheck->execute();
        $asist = $stmtCheck->get_result()->fetch_assoc();

        if (!$asist) {
            $estado = ($horaActual > '08:15:00') ? 'Retardo' : 'A tiempo';
            $sqlIns = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, fk_aprendiz) VALUES (?, ?, ?, ?)";
            $stmtIns = $db->prepare($sqlIns);
            $stmtIns->bind_param("sssi", $hoy, $ahora, $estado, $idAprendiz);
            $stmtIns->execute();
        } else if (empty($asist['salida']) || $asist['salida'] === '0000-00-00 00:00:00') {
            $sqlUp = "UPDATE asistencia SET salida = ?, estado_salida = 'Salida normal' WHERE id_asistencia = ?";
            $stmtUp = $db->prepare($sqlUp);
            $stmtUp->bind_param("si", $ahora, $asist['id_asistencia']);
            $stmtUp->execute();
        }

        header("Location: index.php?action=portal_aprendiz");
        exit();
    }
}
?>
