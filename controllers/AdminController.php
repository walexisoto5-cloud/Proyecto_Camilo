<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class AdminController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function dashboard() {
        // Asegurar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        //total de aprendices inscritos
        $sqlAprendices = "SELECT COUNT(*) as total FROM aprendiz";
        $resultAprendices = $this->conn->query($sqlAprendices);
        $totalAprendices = $resultAprendices ? $resultAprendices->fetch_assoc()['total'] : 0;

        //asistencias de hoy
        $sqlAsistencias = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE()";
        $resultAsistencias = $this->conn->query($sqlAsistencias);
        $asistenciasHoy = $resultAsistencias ? $resultAsistencias->fetch_assoc()['total'] : 0;

        //retardos de hoy
        $sqlRetardos = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE() AND estado_entrada = 'retardo'";
        $resultRetardos = $this->conn->query($sqlRetardos);
        $retardosHoy = $resultRetardos ? $resultRetardos->fetch_assoc()['total'] : 0;

        //excusas pendientes de aprobación
        $sqlExcusas = "SELECT COUNT(*) as total FROM excusa WHERE estado = 'Pendiente'";
        $resultExcusas = $this->conn->query($sqlExcusas);
        $excusasPendientes = $resultExcusas ? $resultExcusas->fetch_assoc()['total'] : 0;

        //ultimos marcajes para la tabla inferior
        $sqlTabla = "SELECT a.*, u.nombre, u.apellido 
                     FROM asistencia a 
                     INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz 
                     INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                     ORDER BY a.fecha_asistencia DESC, a.entrada DESC LIMIT 5";
        $resultTabla = $this->conn->query($sqlTabla);
        $ultimosMarcajes = $resultTabla ? $resultTabla->fetch_all(MYSQLI_ASSOC) : [];

        //datos del usuario logueado para la vista
        $nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['user'] ?? 'Administrador';
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        //dashboard
        $rutaVista = __DIR__ . '/../views/auth/dashboard.php';
        if (file_exists($rutaVista)) {
            require_once $rutaVista;
        } else {
            echo "<h3 style='color:red; text-align:center;'>Error: No se encontró la vista del dashboard.</h3>";
        }
    }
}
?>