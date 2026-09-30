<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{

    // Cargar datos y mostrar la vista principal del Dashboard
    public function index()
    {
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
    public function crearFicha()
    {
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
    public function crearInstructor()
    {
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

   public function mostrarEscaner() {
        // Verificar sesión activa
       if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $database = new Database();
        $db = $database->getConnection();

        // Puedes pasar una variable para indicarle a la vista que debe mostrar el escáner en el centro
        $vistaActiva = 'escaner'; 

        // Cargamos tu vista principal del dashboard (la que tiene el menú, los botones, el navbar, etc.)
        require_once __DIR__ . '/../views/auth/dashboard.php';
    }
    public function procesarRfId()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo_rfid = trim($_POST['codigo_rfid'] ?? '');

            header('Content-Type: application/json');

            if (empty($codigo_rfid)) {
                echo json_encode(["status" => "error", "mensaje" => "Código RFID vacío."]);
                exit();
            }

            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            $hoy = date('Y-m-d');
            $hora_actual = date('H:i:s');

            // 1. Buscar al usuario por su código RFID
            $sql = "SELECT id_usuario, nombre, apellido FROM usuario WHERE codigo_rfid = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $codigo_rfid);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {
                echo json_encode(["status" => "error", "mensaje" => "Llavero RFID no registrado en el sistema."]);
                exit();
            }

            $usuario = $resultado->fetch_assoc();
            $id_usuario = $usuario['id_usuario'];

            // 2. Verificar si ya marcó entrada hoy
            $sql_check = "SELECT * FROM ingresos WHERE id_usuario = ? AND fecha = ?";
            $stmt_check = $db->prepare($sql_check);
            $stmt_check->bind_param("is", $id_usuario, $hoy);
            $stmt_check->execute();
            $asistencia = $stmt_check->get_result()->fetch_assoc();

            if (!$asistencia) {
                // Registrar Entrada
                $sql_insert = "INSERT INTO ingresos (id_usuario, fecha, hora_entrada, estado) VALUES (?, ?, ?, 'A tiempo')";
                $stmt_insert = $db->prepare($sql_insert);
                $stmt_insert->bind_param("iss", $id_usuario, $hoy, $hora_actual);
                $stmt_insert->execute();

                echo json_encode([
                    "status" => "success",
                    "mensaje" => "¡Bienvenido, " . $usuario['nombre'] . "! Entrada registrada a las " . $hora_actual
                ]);
            } else if ($asistencia && empty($asistencia['hora_salida'])) {
                // Registrar Salida
                $sql_update = "UPDATE ingresos SET hora_salida = ? WHERE id_ingreso = ?";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->bind_param("si", $hora_actual, $asistencia['id_ingreso']);
                $stmt_update->execute();

                echo json_encode([
                    "status" => "success",
                    "mensaje" => "¡Hasta luego, " . $usuario['nombre'] . "! Salida registrada a las " . $hora_actual
                ]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "El aprendiz ya registró su entrada y salida hoy."]);
            }
            exit();
        }
    }
}
