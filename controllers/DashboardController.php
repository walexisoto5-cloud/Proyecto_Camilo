<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/dashboard.php';

class DashboardController
{
    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    // Cargar datos y mostrar la vista principal del Dashboard
    public function index()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $totalAprendices = $dashboardModel->obtenerTotalAprendices();
        $asistenciasHoy = $dashboardModel->obtenerAsistenciasHoy();
        $retardosHoy = $dashboardModel->obtenerRetardosHoy();
        $excusasPendientes = $dashboardModel->obtenerExcusasPendientes();
        $ultimosMarcajes = $dashboardModel->obtenerUltimasAsistencias(5);
        $totalFichas = $dashboardModel->obtenerTotalFichas();
        $fichas = $dashboardModel->obtenerFichas();

        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        require_once __DIR__ . '/../views/auth/dashboard.php';
    }

    // Guardar nueva ficha desde el modal
    public function crearFicha()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numeroFicha = trim($_POST['numero_ficha'] ?? '');
            $programa = trim($_POST['programa_formacion'] ?? '');

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

    // Guardar nuevo instructor desde el modal
    public function crearInstructor()
    {
        $this->verificarSesion();

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

                $dashboardModel->crearInstructor($documento, $nombre, $apellido, $correo, $contrasena);
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }

    // Guardar nuevo aprendiz desde el modal
    public function crearAprendiz()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documento = trim($_POST['documento'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
            if (empty($nombreUsuario)) {
                $nombreUsuario = trim($_POST['correo'] ?? '');
            }
            $contrasena = trim($_POST['contrasena'] ?? '');
            $idFicha = (int)($_POST['id_ficha'] ?? 0);

            if (!empty($documento) && !empty($nombre) && !empty($apellido) && !empty($nombreUsuario) && !empty($contrasena) && $idFicha > 0) {
                $database = new Database();
                $db = $database->getConnection();
                $dashboardModel = new Dashboard($db);

                $dashboardModel->crearAprendiz($documento, $nombre, $apellido, $nombreUsuario, $contrasena, $idFicha);
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }

    // Procesar subida de excusa médica o justificación
    public function guardarExcusa()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoAprendiz = trim($_POST['documento_aprendiz'] ?? '');
            $fechaFalta = trim($_POST['fecha_falta'] ?? '');
            $motivo = trim($_POST['motivo'] ?? '');

            if (!empty($documentoAprendiz) && !empty($fechaFalta) && !empty($motivo)) {
                $nombreArchivoFinal = null;

                if (isset($_FILES['archivo_excusa']) && $_FILES['archivo_excusa']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['archivo_excusa']['tmp_name'];
                    $fileName = $_FILES['archivo_excusa']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                    if (in_array($fileExtension, $allowedExtensions)) {
                        $uploadFileDir = __DIR__ . '/../public/uploads/excusas/';

                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true);
                        }

                        $nombreArchivoFinal = 'excusa_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                        $destPath = $uploadFileDir . $nombreArchivoFinal;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $database = new Database();
                            $db = $database->getConnection();
                            $dashboardModel = new Dashboard($db);

                            $dashboardModel->guardarExcusa($documentoAprendiz, $fechaFalta, $motivo, $nombreArchivoFinal);
                        }
                    }
                }
            }
        }
        header("Location: index.php?action=dashboard");
        exit();
    }

    // Mostrar interfaz del escáner RFID
    public function mostrarEscaner()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $dashboardModel = new Dashboard($db);

        $vistaActiva = 'escaner';
        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';
        $fichas = $dashboardModel->obtenerFichas();

        if ($rolUsuario === 'Instructor') {
            $idFichaSeleccionada = $fichas[0]['id_ficha'] ?? 0;
            $fechaSeleccionada = date('Y-m-d');
            $competenciaActiva = 'ADSO - Registro Asistencia RFID';
            $aprendicesLista = [];
            $excusasPendientes = [];
            require_once __DIR__ . '/../views/instructor/dashboard.php';
            return;
        }

        require_once __DIR__ . '/../views/auth/dashboard.php';
    }

    // Endpoint AJAX para registrar lectura de tarjeta/llavero RFID
    public function procesarRfId()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "mensaje" => "Método no permitido."]);
            exit();
        }

        $codigo_rfid = trim($_POST['codigo_rfid'] ?? '');

        if (empty($codigo_rfid)) {
            echo json_encode(["status" => "error", "mensaje" => "Código RFID vacío."]);
            exit();
        }

        $database = new Database();
        $db = $database->getConnection();

        $hoy = date('Y-m-d');
        $hora_actual = date('H:i:s');
        $ahora_datetime = date('Y-m-d H:i:s');

        // 1. Buscar al aprendiz: primero por codigo_rfid en tabla aprendiz,
        // o por documento en usuario como alternativa de marcaje
        $sql = "SELECT ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, u.identificacion 
                FROM aprendiz ap 
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                WHERE ap.codigo_rfid = ? OR u.identificacion = ? 
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $codigo_rfid, $codigo_rfid);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            // Verificar si el usuario existe pero no está en la tabla aprendiz
            $sqlUser = "SELECT id_usuario, nombre, apellido FROM usuario WHERE identificacion = ? OR nombre_usuario = ? LIMIT 1";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->bind_param("ss", $codigo_rfid, $codigo_rfid);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();

            if ($resUser->num_rows > 0) {
                $uData = $resUser->fetch_assoc();
                echo json_encode([
                    "status" => "warning",
                    "mensaje" => "El usuario " . $uData['nombre'] . " no tiene perfil de aprendiz asignado a una ficha."
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Tarjeta RFID o documento (" . htmlspecialchars($codigo_rfid) . ") no registrado en el sistema."
                ]);
            }
            exit();
        }

        $aprendiz = $res->fetch_assoc();
        $id_aprendiz = (int)$aprendiz['id_aprendiz'];
        $id_usuario = (int)$aprendiz['id_usuario'];
        $nombreCompleto = trim($aprendiz['nombre'] . ' ' . $aprendiz['apellido']);

        // 2. Verificar si ya tiene registro de asistencia el día de hoy
        $sql_check = "SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmt_check = $db->prepare($sql_check);
        $stmt_check->bind_param("is", $id_aprendiz, $hoy);
        $stmt_check->execute();
        $asistencia = $stmt_check->get_result()->fetch_assoc();

        if (!$asistencia) {
            // --- REGISTRAR ENTRADA ---
            // Regla de negocio: entrada estándar hasta las 08:15 AM
            $estado = "A tiempo";
            if ($hora_actual > '08:15:00') {
                $estado = "Retardo";
            }

            $sql_insert = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, fk_aprendiz) 
                           VALUES (?, ?, ?, ?)";
            $stmt_insert = $db->prepare($sql_insert);
            $stmt_insert->bind_param("sssi", $hoy, $ahora_datetime, $estado, $id_aprendiz);
            $stmt_insert->execute();

            // Sincronizar tabla complementaria 'ingresos' si existe
            $sql_ingreso = "INSERT INTO ingresos (id_usuario, fecha, hora_entrada, estado) 
                            VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE hora_entrada = VALUES(hora_entrada)";
            $stmt_ingreso = $db->prepare($sql_ingreso);
            if ($stmt_ingreso) {
                $stmt_ingreso->bind_param("isss", $id_usuario, $hoy, $hora_actual, $estado);
                @$stmt_ingreso->execute();
            }

            echo json_encode([
                "status" => "success",
                "mensaje" => "¡Bienvenido(a), " . $nombreCompleto . "! Entrada registrada (" . $estado . ") a las " . date('h:i A')
            ]);
        } else if (!empty($asistencia['entrada']) && (empty($asistencia['salida']) || $asistencia['salida'] === '0000-00-00 00:00:00')) {
            // --- REGISTRAR SALIDA ---
            $estado_salida = "Salida normal";

            $sql_update = "UPDATE asistencia SET salida = ?, estado_salida = ? WHERE id_asistencia = ?";
            $stmt_update = $db->prepare($sql_update);
            $stmt_update->bind_param("ssi", $ahora_datetime, $estado_salida, $asistencia['id_asistencia']);
            $stmt_update->execute();

            // Sincronizar tabla ingresos
            $sql_ingreso_up = "UPDATE ingresos SET hora_salida = ? WHERE id_usuario = ? AND fecha = ?";
            $stmt_ingreso_up = $db->prepare($sql_ingreso_up);
            if ($stmt_ingreso_up) {
                $stmt_ingreso_up->bind_param("sis", $hora_actual, $id_usuario, $hoy);
                @$stmt_ingreso_up->execute();
            }

            echo json_encode([
                "status" => "success",
                "mensaje" => "¡Hasta luego, " . $nombreCompleto . "! Salida registrada a las " . date('h:i A')
            ]);
        } else {
            echo json_encode([
                "status" => "warning",
                "mensaje" => $nombreCompleto . " ya registró su entrada y salida por el día de hoy."
            ]);
        }
        exit();
    }
}
?>
