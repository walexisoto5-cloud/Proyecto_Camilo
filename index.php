<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    // --- RUTAS DE GESTIÓN Y ESCÁNER RFID ---
    case 'crear_ficha':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->crearFicha();
        break;

    case 'crear_instructor':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->crearInstructor();
        break;

    case 'crearAprendiz':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->crearAprendiz();
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