<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/dashboard.php';

/**
 * DashboardController - Responsable exclusivo del Panel de Control principal y métricas (SRP)
 */
class DashboardController
{
    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id']) && empty($_SESSION['id_usuario'])) {
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
        $totalFichas = $dashboardModel->obtenerTotalFichas();
        $totalInstructores = $dashboardModel->obtenerTotalInstructores();
        $ultimosMarcajes = $dashboardModel->obtenerUltimasAsistencias(8);
        $fichasResumen = $dashboardModel->obtenerFichasConConteo(8);
        $instructoresResumen = $dashboardModel->obtenerInstructores(6);
        $fichas = $dashboardModel->obtenerFichas();

        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $comitesPendientes = empty($_SESSION['alerta_comite_descartada']) ? $comiteModel->obtenerTotalPendientes() : 0;

        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        require_once __DIR__ . '/../views/auth/dashboard.php';
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

        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $comitesPendientes = empty($_SESSION['alerta_comite_descartada']) ? $comiteModel->obtenerTotalPendientes() : 0;

        if ($rolUsuario === 'Instructor') {
            $idFichaSeleccionada = $fichas[0]['id_ficha'] ?? 0;
            $fechaSeleccionada = date('Y-m-d');
            $competenciaActiva = 'ADSO - Registro Asistencia RFID';
            $aprendicesLista = [];
            $excusasPendientes = [];
            require_once __DIR__ . '/../views/instructor/dashboard.php';
            return;
        }

        if ($rolUsuario === 'Aprendiz') {
            require_once __DIR__ . '/../models/Aprendiz.php';
            $aprendizModel = new Aprendiz($db);
            $idUsuario = (int)$_SESSION['usuario_id'];
            $datosAprendiz = $aprendizModel->obtenerPorUsuario($idUsuario);

            if (!$datosAprendiz) {
                $nombreSesion = $_SESSION['nombre_completo'] ?? 'Usuario Demo';
                $partes = explode(' ', $nombreSesion, 2);
                $datosAprendiz = [
                    'id_aprendiz' => 0,
                    'nombre' => $partes[0] ?? 'Aprendiz',
                    'apellido' => $partes[1] ?? 'Demo',
                    'identificacion' => '---',
                    'numero_ficha' => '3234082',
                    'nombre_programa' => 'ADSO - Análisis y Desarrollo de Software',
                    'jornada' => 'Diurna'
                ];
                $porcentajeAsistencia = 100;
                $totalAsistencias = 0;
                $totalRetardos = 0;
                $totalFaltas = 0;
                $asistenciaHoy = null;
                $historial = [];
                $misExcusas = [];
                $comiteAprendiz = null;
            } else {
                $idAprendiz = (int)$datosAprendiz['id_aprendiz'];
                $resumen = $aprendizModel->obtenerResumenPortal($idAprendiz);
                $totalSesiones = $resumen['totalSesiones'];
                $totalAsistencias = $resumen['totalAsistencias'];
                $totalRetardos = $resumen['totalRetardos'];
                $totalFaltas = $resumen['totalFaltas'];
                $porcentajeAsistencia = $resumen['porcentajeAsistencia'];
                $asistenciaHoy = $resumen['asistenciaHoy'];
                $historial = $resumen['historial'];
                $misExcusas = $resumen['misExcusas'];
                $comiteAprendiz = $comiteModel->obtenerPorAprendiz($idAprendiz);
            }

            $vistaActiva = 'escaner';
            $seccionActiva = 'escaner';
            require_once __DIR__ . '/../views/aprendiz/dashboard.php';
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

        require_once __DIR__ . '/../models/Asistencia.php';
        $database = new Database();
        $db = $database->getConnection();
        $asistenciaModel = new Asistencia($db);

        $resultado = $asistenciaModel->registrarAccesoPorRfId($codigo_rfid);
        echo json_encode($resultado);
        exit();
    }
}
?>