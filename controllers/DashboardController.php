<?php
require_once __DIR__ . '/../config/database.php';
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

        require_once __DIR__ . '/../views/auth/dashboard.php';
    }

    public function crearFicha() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numeroFicha = trim($_POST['numero_ficha'] ?? '');
            $programaFormacion = trim($_POST['programa_formacion'] ?? '');

            if (!empty($numeroFicha) && !empty($programaFormacion)) {
                $database = new Database();
                $db = $database->getConnection();
                $dashboardModel = new Dashboard($db);

                $resultado = $dashboardModel->crearFicha($numeroFicha, $programaFormacion);

                if ($resultado) {
                    $_SESSION['mensaje'] = "Ficha registrada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al guardar la ficha en la base de datos.";
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documento = trim($_POST['documento'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');

            if (!empty($documento) && !empty($nombre) && !empty($apellido) && !empty($correo) && !empty($contrasena)) {
                $database = new Database();
                $db = $database->getConnection();
                $dashboardModel = new Dashboard($db);

                $resultado = $dashboardModel->crearInstructor($documento, $nombre, $apellido, $correo, $contrasena);

                if ($resultado) {
                    $_SESSION['mensaje'] = "Instructor registrado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al registrar el instructor en la base de datos.";
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