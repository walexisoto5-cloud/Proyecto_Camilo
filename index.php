<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/helpers.php';

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        // Redirección inteligente según el rol si no es administrador
        if (!empty($_SESSION['rol'])) {
            if ($_SESSION['rol'] === 'Aprendiz') {
                header("Location: index.php?action=portal_aprendiz");
                exit();
            } else if ($_SESSION['rol'] === 'Instructor') {
                header("Location: index.php?action=portal_instructor");
                exit();
            }
        }
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'login':
        require_once __DIR__ . '/controllers/authController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/authController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // --- RUTAS DEL PORTAL DEL APRENDIZ ---
    case 'portal_aprendiz':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->portal();
        break;

    case 'marcar_asistencia_aprendiz':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->marcarAsistencia();
        break;

    // --- RUTAS DEL PANEL DEL INSTRUCTOR ---
    case 'portal_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->portal();
        break;

    case 'guardar_asistencia_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->guardarLlamadoLista();
        break;

    case 'procesar_excusa_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->procesarExcusa();
        break;

    case 'remitir_comite':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->remitirComite();
        break;

    case 'reporte_asistencia_pdf':
        require_once __DIR__ . '/controllers/ReporteController.php';
        $controller = new ReporteController();
        $controller->generarReporteAsistenciaPdf();
        break;

    case 'reporte_asistencia_excel':
        require_once __DIR__ . '/controllers/ReporteController.php';
        $controller = new ReporteController();
        $controller->generarReporteAsistenciaExcel();
        break;

    // --- RUTAS DE GESTIÓN CRUD DESACOPLADAS (SRP) ---
    case 'gestion_aprendices':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->viewAprendices();
        break;

    case 'crear_aprendiz':
    case 'crearAprendiz':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->crearAprendiz();
        break;

    case 'importar_aprendices':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->importarAprendicesCsv();
        break;

    case 'actualizar_aprendiz':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->actualizarAprendiz();
        break;

    case 'eliminar_aprendiz':
        require_once __DIR__ . '/controllers/AprendizController.php';
        $controller = new AprendizController();
        $controller->eliminarAprendiz();
        break;

    case 'gestion_instructores':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->viewInstructores();
        break;

    case 'crear_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->crearInstructor();
        break;

    case 'actualizar_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->actualizarInstructor();
        break;

    case 'eliminar_instructor':
        require_once __DIR__ . '/controllers/InstructorController.php';
        $controller = new InstructorController();
        $controller->eliminarInstructor();
        break;

    case 'gestion_fichas':
        require_once __DIR__ . '/controllers/FichaController.php';
        $controller = new FichaController();
        $controller->viewFichas();
        break;

    case 'crear_ficha':
        require_once __DIR__ . '/controllers/FichaController.php';
        $controller = new FichaController();
        $controller->crearFicha();
        break;

    case 'actualizar_ficha':
        require_once __DIR__ . '/controllers/FichaController.php';
        $controller = new FichaController();
        $controller->actualizarFicha();
        break;

    case 'eliminar_ficha':
        require_once __DIR__ . '/controllers/FichaController.php';
        $controller = new FichaController();
        $controller->eliminarFicha();
        break;

    case 'gestion_excusas':
        require_once __DIR__ . '/controllers/ExcusaController.php';
        $controller = new ExcusaController();
        $controller->viewExcusas();
        break;

    case 'aprobar_excusa':
        require_once __DIR__ . '/controllers/ExcusaController.php';
        $controller = new ExcusaController();
        $controller->procesarExcusa('Aprobada');
        break;

    case 'rechazar_excusa':
        require_once __DIR__ . '/controllers/ExcusaController.php';
        $controller = new ExcusaController();
        $controller->procesarExcusa('Rechazada');
        break;

    case 'gestion_comite':
        require_once __DIR__ . '/controllers/ComiteController.php';
        $controller = new ComiteController();
        $controller->viewComite();
        break;

    case 'actualizar_estado_comite':
        require_once __DIR__ . '/controllers/ComiteController.php';
        $controller = new ComiteController();
        $controller->actualizarEstadoComite();
        break;

    case 'guardar_excusa':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->guardarExcusa();
        break;

    case 'escaner_rfid':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->mostrarEscaner();
        break;

    case 'calendario':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->mostrarCalendario();
        break;

    case 'importar_horario_calendario':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->importarHorarioCalendario();
        break;

    case 'descargar_plantilla_calendario':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->descargarPlantillaCalendario();
        break;

    case 'agregar_bloque_calendario':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->agregarBloqueCalendario();
        break;

    case 'eliminar_bloque_calendario':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->eliminarBloqueCalendario();
        break;

    case 'procesar_rfid':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->procesarRfId();
        break;

    default:
        header("Location: index.php?action=dashboard");
        exit();
        break;
}
?>