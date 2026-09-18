<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    
    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya hay sesión activa, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');

            if (empty($nombre_usuario) || empty($contrasena)) {
                $error = "Por favor ingrese usuario y contraseña.";
                require_once __DIR__ . '/../views/auth/login.php';
                return;
            }

            $database = new Database();
            $db = $database->getConnection();
            $usuarioModel = new Usuario($db);

            $usuario = $usuarioModel->obtenerPorUsuario($nombre_usuario);

            if ($usuario && (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena'])) {
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre_completo'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['rol'] = $usuario['nombre_rol'];

                header("Location: index.php?action=dashboard");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
                require_once __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>