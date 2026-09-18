<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'login':
        $authController = new AuthController();
        $authController->login();
        break;

    case 'logout':
        $authController = new AuthController();
        $authController->logout();
        break;

    case 'dashboard':
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;

    case 'crear_ficha':
        $dashboardController = new DashboardController();
        $dashboardController->crearFicha();
        break;

    case 'crear_instructor':
        $dashboardController = new DashboardController();
        $dashboardController->crearInstructor();
        break;

    default:
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;
}
?>