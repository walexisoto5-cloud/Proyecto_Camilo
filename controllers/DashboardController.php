<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {

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

        $nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario Sistema';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        $mensaje = $_SESSION['mensaje'] ?? null;
        $tipoMensaje = $_SESSION['tipo_mensaje'] ?? null;
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

        require_once __DIR__ . '/../views/dashboard.php';
    }

    public function crearFicha() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }

            $database = new Database();
            $db = $database->getConnection();
            $dashboardModel = new Dashboard($db);

            $numeroFicha = trim($_POST['numero_ficha'] ?? '');
            $programa = trim($_POST['programa_formacion'] ?? '');

            if (!empty($numeroFicha) && !empty($programa)) {
                if ($dashboardModel->crearFicha($numeroFicha, $programa)) {
                    $_SESSION['mensaje'] = "¡Ficha registrada exitosamente!";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al registrar la ficha.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } else {
                $_SESSION['mensaje'] = "Por favor completa todos los campos de la ficha.";
                $_SESSION['tipo_mensaje'] = "warning";
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }

    public function crearInstructor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }

            $database = new Database();
            $db = $database->getConnection();
            $dashboardModel = new Dashboard($db);

            $documento = trim($_POST['documento'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');

            if (!empty($documento) && !empty($nombre) && !empty($correo) && !empty($contrasena)) {
                if ($dashboardModel->crearInstructor($documento, $nombre, $apellido, $correo, $contrasena)) {
                    $_SESSION['mensaje'] = "¡Instructor registrado exitosamente!";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al registrar el instructor (quizá el documento o correo ya existe).";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } else {
                $_SESSION['mensaje'] = "Por favor completa todos los campos del instructor.";
                $_SESSION['tipo_mensaje'] = "warning";
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }
}
?>