<?php


require_once __DIR__ . '/../config/database.php';

class InstructorController
{
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

    // Vista principal: Panel de toma de asistencia ágil
    public function portal()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();

        $sqlFichas = "SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha ORDER BY numero_ficha ASC";
        $resFichas = $db->query($sqlFichas);
        $fichas = $resFichas ? $resFichas->fetch_all(MYSQLI_ASSOC) : [];

        $idFichaSeleccionada = isset($_GET['ficha_id']) ? (int)$_GET['ficha_id'] : ($fichas[0]['id_ficha'] ?? 0);
        $fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $competenciaActiva = isset($_GET['competencia']) ? trim($_GET['competencia']) : 'ADSO - Desarrollo y Programación';

        $aprendicesLista = [];
        if ($idFichaSeleccionada > 0) {
            $sqlAprendices = "SELECT ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, u.identificacion
                              FROM aprendiz ap
                              INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                              WHERE ap.fk_ficha = ?
                              ORDER BY u.apellido ASC, u.nombre ASC";
            $stmtAp = $db->prepare($sqlAprendices);
            $stmtAp->bind_param("i", $idFichaSeleccionada);
            $stmtAp->execute();
            $resAp = $stmtAp->get_result();

            while ($row = $resAp->fetch_assoc()) {
                $idAp = (int)$row['id_aprendiz'];

                $sqlAsistHoy = "SELECT id_asistencia, entrada, salida, estado_entrada, estado_salida 
                                FROM asistencia 
                                WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
                $stmtHoy = $db->prepare($sqlAsistHoy);
                $stmtHoy->bind_param("is", $idAp, $fechaSeleccionada);
                $stmtHoy->execute();
                $asistHoy = $stmtHoy->get_result()->fetch_assoc();

                $estadoActual = 'Presente';
                if ($asistHoy) {
                    $est = strtolower($asistHoy['estado_entrada'] ?? '');
                    if (strpos($est, 'retardo') !== false || strpos($est, 'tarde') !== false) {
                        $estadoActual = 'Tarde';
                    } else if (strpos($est, 'ausente') !== false || strpos($est, 'falta') !== false || strpos($est, 'inasistencia') !== false) {
                        $estadoActual = 'Ausente';
                    } else {
                        $estadoActual = 'Presente';
                    }
                }

                // Cálculo de Inasistencias y Alerta del 15% según Reglamento SENA
                $sqlTot = "SELECT COUNT(*) as total FROM asistencia WHERE fk_aprendiz = ?";
                $stmtTot = $db->prepare($sqlTot);
                $stmtTot->bind_param("i", $idAp);
                $stmtTot->execute();
                $totalSesiones = (int)($stmtTot->get_result()->fetch_assoc()['total'] ?? 0);

                $sqlFaltas = "SELECT COUNT(*) as faltas FROM asistencia 
                              WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) LIKE '%ausente%' OR LOWER(estado_entrada) LIKE '%falta%' OR LOWER(estado_entrada) LIKE '%inasistencia%')";
                $stmtFaltas = $db->prepare($sqlFaltas);
                $stmtFaltas->bind_param("i", $idAp);
                $stmtFaltas->execute();
                $totalFaltas = (int)($stmtFaltas->get_result()->fetch_assoc()['faltas'] ?? 0);

                $porcentajeFaltas = ($totalSesiones > 0) ? round(($totalFaltas / $totalSesiones) * 100) : 0;
                $alertaSena = ($porcentajeFaltas >= 15 || $totalFaltas >= 3);

                $row['estado_actual'] = $estadoActual;
                $row['asistencia_id'] = $asistHoy['id_asistencia'] ?? null;
                $row['total_faltas'] = $totalFaltas;
                $row['porcentaje_faltas'] = $porcentajeFaltas;
                $row['alerta_sena'] = $alertaSena;

                $aprendicesLista[] = $row;
            }
        }

        $sqlExcusas = "SELECT e.*, u.nombre, u.apellido, u.identificacion, a.fecha_asistencia
                       FROM excusa e
                       INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                       INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                       INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                       WHERE e.estado = 'Pendiente'
                       ORDER BY e.id_excusa DESC LIMIT 5";
        $resExcusas = $db->query($sqlExcusas);
        $excusasPendientes = $resExcusas ? $resExcusas->fetch_all(MYSQLI_ASSOC) : [];

        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Instructor';
        $rolUsuario = $_SESSION['rol'] ?? 'Instructor';

        require_once __DIR__ . '/../views/instructor/dashboard.php';
    }

    // Guardar el llamado a lista masivo enviado desde el formulario
    public function guardarLlamadoLista()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $idFicha = (int)($_POST['ficha_id'] ?? 0);
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $asistencias = $_POST['asistencia'] ?? [];
            $observaciones = $_POST['observacion'] ?? [];
            $ahora = date('Y-m-d H:i:s');

            foreach ($asistencias as $idAprendiz => $estado) {
                $idAprendiz = (int)$idAprendiz;
                $obs = trim($observaciones[$idAprendiz] ?? '');

                $estadoEntrada = 'A tiempo';
                if ($estado === 'Tarde') $estadoEntrada = 'Retardo';
                if ($estado === 'Ausente') $estadoEntrada = 'Inasistencia';

                $sqlCheck = "SELECT id_asistencia FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->bind_param("is", $idAprendiz, $fecha);
                $stmtCheck->execute();
                $existe = $stmtCheck->get_result()->fetch_assoc();

                if ($existe) {
                    $idAsist = $existe['id_asistencia'];
                    $sqlUp = "UPDATE asistencia SET estado_entrada = ?, estado_salida = ? WHERE id_asistencia = ?";
                    $stmtUp = $db->prepare($sqlUp);
                    $stmtUp->bind_param("ssi", $estadoEntrada, $obs, $idAsist);
                    $stmtUp->execute();
                } else {
                    $sqlIns = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, estado_salida, fk_aprendiz) 
                               VALUES (?, ?, ?, ?, ?)";
                    $stmtIns = $db->prepare($sqlIns);
                    $stmtIns->bind_param("ssssi", $fecha, $ahora, $estadoEntrada, $obs, $idAprendiz);
                    $stmtIns->execute();
                }
            }

            header("Location: index.php?action=portal_instructor&ficha_id=" . $idFicha . "&fecha=" . urlencode($fecha) . "&guardado=1");
            exit();
        }
    }

    // Acción para Aprobar o Rechazar una Excusa Médica
    public function procesarExcusa()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $idExcusa = (int)($_POST['id_excusa'] ?? 0);
            $nuevoEstado = ($_POST['estado'] === 'Aprobada') ? 'Aprobada' : 'Rechazada';
            $idInstructor = (int)$_SESSION['usuario_id'];

            if ($idExcusa > 0) {
                $sql = "UPDATE excusa SET estado = ?, fecha_revision = CURDATE(), fk_usuario_instructor = ? WHERE id_excusa = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sii", $nuevoEstado, $idInstructor, $idExcusa);
                $stmt->execute();
            }
        }

        header("Location: index.php?action=portal_instructor&msg=excusa_actualizada");
        exit();
    }
}
?>
