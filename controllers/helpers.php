<?php
// Helpers de sesión, autenticación y rutas por rol
// Se incluye en index.php para disponibilidad global en todo el sistema.

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Devuelve la URL del portal según el rol del usuario
 */
if (!function_exists('rutaPortalPorRol')) {
    function rutaPortalPorRol(?string $rol = null): string
    {
        $rol = $rol ?? ($_SESSION['rol'] ?? '');
        switch ($rol) {
            case 'Aprendiz':
                return 'index.php?action=portal_aprendiz';
            case 'Instructor':
                return 'index.php?action=portal_instructor';
            case 'Administrador':
            default:
                return 'index.php?action=dashboard';
        }
    }
}

/**
 * Exige una sesión iniciada. Si no existe, redirige al login.
 */
if (!function_exists('requiereLogin')) {
    function requiereLogin(): void
    {
        if (empty($_SESSION['usuario_id']) && empty($_SESSION['id_usuario'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }
}

/**
 * Exige que el usuario tenga uno de los roles indicados.
 * Si no cuenta con el rol, se redirige a su respectivo portal.
 */
if (!function_exists('requiereRol')) {
    function requiereRol($rolesPermitidos): void
    {
        requiereLogin();
        $rolesPermitidos = (array)$rolesPermitidos;
        $rolActual = $_SESSION['rol'] ?? '';

        if (!in_array($rolActual, $rolesPermitidos, true)) {
            header('Location: ' . rutaPortalPorRol($rolActual));
            exit();
        }
    }
}

/**
 * Devuelve los datos básicos del usuario en sesión
 */
if (!function_exists('usuarioActual')) {
    function usuarioActual(): array
    {
        return [
            'id' => (int)($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? 0),
            'nombre' => $_SESSION['nombre'] ?? $_SESSION['user'] ?? 'Usuario',
            'nombre_completo' => $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Usuario'),
            'rol' => $_SESSION['rol'] ?? 'Invitado',
        ];
    }
}
