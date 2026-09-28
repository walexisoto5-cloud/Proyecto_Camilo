<?php
// Activar errores temporalmente para depurar si algo falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener la acción solicitada por la URL (por defecto carga 'dashboard')
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        // 1. Recuperar el nombre y rol del usuario de la sesión (o mostrar 'Administrador' por defecto)
        $nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['user'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        // 2. Incluir la vista del dashboard ubicada en la carpeta views/auth/
        if (file_exists('views/auth/dashboard.php')) {
            include 'views/auth/dashboard.php';
        } else {
            echo "<h3 style='color:red; text-align:center; margin-top:50px;'>Error crítico: No se encontró el archivo views/auth/dashboard.php.</h3>";
        }
        break;

    case 'guardar_excusa':
        // Procesar los datos cuando se envía el formulario del modal
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoAprendiz = $_POST['documento_aprendiz'] ?? '';
            $fechaFalta = $_POST['fecha_falta'] ?? '';
            $motivo = $_POST['motivo'] ?? '';
            
            // Validar y mover el archivo adjunto
            if (isset($_FILES['archivo_excusa']) && $_FILES['archivo_excusa']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['archivo_excusa']['tmp_name'];
                $fileName = $_FILES['archivo_excusa']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadFileDir = 'public/uploads/excusas/';
                    
                    // Crear la carpeta de subidas si no existe
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    
                    // Generar un nombre único para el archivo
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        // TODO: Aquí debes agregar tu consulta SQL (INSERT INTO excusas...) para guardarlo en la base de datos
                    }
                }
            }

            // REDIRECCIÓN OBLIGATORIA: Limpia la URL y regresa al dashboard sin dejar rastro de la acción
            header("Location: index.php?action=dashboard");
            exit();
        }
        break;

    case 'logout':
        // Limpiar la sesión por completo y regresar al login físico
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redirección directa al archivo login.php dentro de views/auth/
        header("Location: views/auth/login.php");
        exit();
        break;

    default:
        // Si escriben una acción inválida, redirigir al dashboard por seguridad
        header("Location: index.php?action=dashboard");
        exit();
        break;
}