<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesion de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
   case 'dashboard':
        if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'config/database.php';
        require_once 'models/Dashboard.php';
        require_once 'controllers/DashboardController.php';

        $database = new Database();
        $db = $database->getConnection();
        
        $controller = new DashboardController();
        $controller->index(); 
        break;

        case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;


    case 'crear_ficha':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->crearFicha();
        break;

    case 'crear_instructor':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->crearInstructor();
        break;

        case 'escaner_rfid':
        // Verificar sesión activa
        if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->mostrarEscaner();
        break;

    case 'procesar_rfid':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->procesarRfId();
        break;

    case 'guardar_excusa':
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoAprendiz = $_POST['documento_aprendiz'] ?? '';
            $fechaFalta = $_POST['fecha_falta'] ?? '';
            $motivo = $_POST['motivo'] ?? '';
        
            if (isset($_FILES['archivo_excusa']) && $_FILES['archivo_excusa']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['archivo_excusa']['tmp_name'];
                $fileName = $_FILES['archivo_excusa']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadFileDir = 'public/uploads/excusas/';
                    
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    
                    // Generar un nombre unico para el archivo
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    }
                }
            }


            header("Location: index.php?action=dashboard");
            exit();
        }
        break;

    case 'logout':
        // limpiar la sesion por completo y regresar al login fisico
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        header("Location: views/auth/login.php");
        exit();
        break;

    default:
        header("Location: index.php?action=dashboard");
        exit();
        break;

        
}
?>