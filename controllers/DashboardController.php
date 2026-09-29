<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {

    // Cargar datos y mostrar la vista principal del Dashboard
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        
        $totalAprendices = $dashboardModel->obtenerTotalAprendices();
        $asistenciasHoy = $dashboardModel->obtenerAsistenciasHoy();
        $retardosHoy = $dashboardModel->obtenerRetardosHoy();
        $excusasPendientes = $dashboardModel->obtenerExcusasPendientes();
        $ultimosMarcajes = $dashboardModel->obtenerUltimasAsistencias(5);
        $totalFichas = $dashboardModel->obtenerTotalFichas();

        
        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['user'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        
        require_once __DIR__ . '/../views/auth/dashboard.php';
    }

    //Guardar nueva ficha desde el modal
    public function crearFicha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numeroFicha = $_POST['numero_ficha'] ?? '';
            $programa = $_POST['programa_formacion'] ?? '';

            if (!empty($numeroFicha) && !empty($programa)) {
                $database = new Database();
                $db = $database->getConnection();
                $dashboardModel = new Dashboard($db);

                $dashboardModel->crearFicha($numeroFicha, $programa);
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }

    //Guardar nuevo instructor desde el modal
    public function crearInstructor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documento = $_POST['documento'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            if (!empty($documento) && !empty($nombre) && !empty($apellido) && !empty($correo) && !empty($contrasena)) {
                $database = new Database();
                $db = $database->getConnection();
                $dashboardModel = new Dashboard($db);

                $dashboardModel->crearInstructor($documento, $nombre, $apellido, $correo, $contrasena);
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }
}
?>