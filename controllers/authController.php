<?php
session_start();
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function login() {
        // Verificar si la petición viene por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identificador = trim($_POST['identificador'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validar campos vacíos
            if (empty($identificador) || empty($password)) {
                $_SESSION['error'] = "Por favor, ingrese su documento/correo y contraseña.";
                header("Location: index.php?action=login");
                exit();
            }

            // Consultar datos del usuario
            $user = $this->usuarioModel->obtenerPorIdentificador($identificador);

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['estado'] !== 'activo') {
                    $_SESSION['error'] = "Su cuenta se encuentra inactiva.";
                    header("Location: index.php?action=login");
                    exit();
                }

                // Regenerar ID de sesión para prevenir Session Fixation
                session_regenerate_id(true);

                // Guardar datos clave en la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;

                // Redireccionar según el rol del usuario
                if ($user['rol'] === 'Administrador' || $user['rol'] === 'Instructor') {
                    header("Location: index.php?action=admin_dashboard");
                } else {
                    header("Location: index.php?action=aprendiz_dashboard");
                }
                exit();
            } else {
                $_SESSION['error'] = "Credenciales incorrectas.";
                header("Location: index.php?action=login");
                exit();
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}