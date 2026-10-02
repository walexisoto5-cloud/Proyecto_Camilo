<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
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