<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/helpers.php';

/**
 * CalendarioController - Responsable exclusivo del Cronograma de Horarios y Calendario Formativo (SRP)
 */
class CalendarioController
{
    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id']) && empty($_SESSION['id_usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    private function verificarRolGestionCalendario()
    {
        $this->verificarSesion();
        $rol = $_SESSION['rol'] ?? '';
        if ($rol !== 'Administrador' && $rol !== 'Instructor') {
            header("Location: index.php?action=calendario&msg=acceso_denegado");
            exit();
        }
    }

    // Vista principal del Calendario SENA
    public function mostrarCalendario()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';
        $fichas = $dashboardModel->obtenerFichas();

        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $comitesPendientes = $comiteModel->obtenerTotalPendientes();
        $vistaActiva = 'calendario';

        // Ficha seleccionada (por defecto la del aprendiz si es rol Aprendiz, o la primera si existe)
        $idFichaSeleccionada = isset($_GET['id_ficha']) ? (int)$_GET['id_ficha'] : 0;
        if ($rolUsuario === 'Aprendiz' && $idFichaSeleccionada === 0) {
            $idUsuario = (int)($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? 0);
            $stmtAp = $db->prepare("SELECT fk_ficha FROM aprendiz WHERE fk_usuario = ? LIMIT 1");
            if ($stmtAp) {
                $stmtAp->bind_param("i", $idUsuario);
                $stmtAp->execute();
                $resAp = $stmtAp->get_result()->fetch_assoc();
                if ($resAp && !empty($resAp['fk_ficha'])) {
                    $idFichaSeleccionada = (int)$resAp['fk_ficha'];
                }
            }
        }
        if ($idFichaSeleccionada === 0 && !empty($fichas)) {
            $idFichaSeleccionada = (int)$fichas[0]['id_ficha'];
        }

        // Obtener bloques de formación configurados para el cronograma
        $horarios = $dashboardModel->obtenerHorariosCalendario($idFichaSeleccionada);

        // Ficha activa
        $fichaActual = null;
        foreach ($fichas as $f) {
            if ((int)$f['id_ficha'] === $idFichaSeleccionada) {
                $fichaActual = $f;
                break;
            }
        }

        // Mes y año seleccionados (por defecto el mes y año actuales)
        $mesActual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
        $anioActual = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
        if ($mesActual < 1 || $mesActual > 12) $mesActual = (int)date('n');
        if ($anioActual < 2020 || $anioActual > 2035) $anioActual = (int)date('Y');

        require_once __DIR__ . '/../views/calendario/calendario.php';
    }

    // Endpoint para importar archivo Excel/CSV con el horario al calendario
    public function importarHorarioCalendario()
    {
        $this->verificarRolGestionCalendario();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=calendario");
            exit();
        }

        $idFicha = isset($_POST['id_ficha']) ? (int)$_POST['id_ficha'] : 0;
        $sobrescribir = isset($_POST['sobrescribir']) && $_POST['sobrescribir'] == '1';

        if ($idFicha <= 0 || !isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=error_archivo");
            exit();
        }

        $archivo = $_FILES['archivo_excel'];
        $nombreOriginal = $archivo['name'];
        $rutaTemporal = $archivo['tmp_name'];

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $resultado = $dashboardModel->procesarArchivoHorarioCalendario($rutaTemporal, $nombreOriginal, $idFicha, $sobrescribir);

        if ($resultado['exito']) {
            $insertados = $resultado['insertados'] ?? 0;
            $instructores = $resultado['instructores'] ?? 0;
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=importado_ok&bloques={$insertados}&instructores={$instructores}");
        } else {
            $errorMsg = urlencode($resultado['mensaje'] ?? 'Error desconocido');
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=error_import&err={$errorMsg}");
        }
        exit();
    }

    // Endpoint para descargar plantilla CSV de ejemplo para el calendario
    public function descargarPlantillaCalendario()
    {
        $this->verificarRolGestionCalendario();

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $csv = $dashboardModel->generarPlantillaCsvCalendario();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="plantilla_horario_sena.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit();
    }

    // Endpoint para agregar un bloque manual al calendario
    public function agregarBloqueCalendario()
    {
        $this->verificarRolGestionCalendario();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=calendario");
            exit();
        }

        $idFicha = isset($_POST['id_ficha']) ? (int)$_POST['id_ficha'] : 0;
        $diaSemana = trim($_POST['dia_semana'] ?? '');
        $entrada = trim($_POST['entrada'] ?? '');
        $salida = trim($_POST['salida'] ?? '');
        $instructorNombre = trim($_POST['instructor_nombre'] ?? '');
        $competencia = trim($_POST['competencia'] ?? '');
        $ambiente = trim($_POST['ambiente'] ?? '');

        if ($idFicha <= 0 || empty($diaSemana) || empty($entrada) || empty($salida)) {
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=error_manual");
            exit();
        }

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $idInstructor = null;
        if (!empty($instructorNombre)) {
            $idInstructor = $dashboardModel->buscarOCrearInstructorPorNombre($instructorNombre);
        }

        $guardado = $dashboardModel->agregarBloqueCalendario($diaSemana, $entrada, $salida, $idFicha, $idInstructor, $competencia, $ambiente);

        if ($guardado) {
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=bloque_agregado");
        } else {
            header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=error_manual");
        }
        exit();
    }

    // Endpoint para eliminar un bloque de horario del calendario
    public function eliminarBloqueCalendario()
    {
        $this->verificarRolGestionCalendario();

        $idHorario = isset($_GET['id_horario']) ? (int)$_GET['id_horario'] : 0;
        $idFicha = isset($_GET['id_ficha']) ? (int)$_GET['id_ficha'] : 0;

        if ($idHorario > 0) {
            $database = new Database();
            $db = $database->getConnection();
            $dashboardModel = new Dashboard($db);
            $dashboardModel->eliminarBloqueCalendario($idHorario);
        }

        header("Location: index.php?action=calendario&id_ficha={$idFicha}&msg=bloque_eliminado");
        exit();
    }
}
?>