<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * ComiteController - Responsable exclusivo de la gestión de casos del Comité de Evaluación y Seguimiento (SRP)
 */
class ComiteController
{
    private function conectar()
    {
        $database = new Database();
        return $database->getConnection();
    }

    public function viewComite(): void
    {
        requiereRol('Administrador');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Al ingresar a gestionar los comités, se marca la alerta como atendida para no estorbar en el dashboard
        $_SESSION['alerta_comite_descartada'] = true;

        $db = $this->conectar();
        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);

        $filtroEstado = trim($_GET['estado'] ?? '');
        $casosComite = $comiteModel->obtenerTodos($filtroEstado);

        $nombreUsuario = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolUsuario = $_SESSION['rol'] ?? 'Administrador';

        require_once __DIR__ . '/../views/gestion/comite.php';
    }

    public function actualizarEstadoComite(): void
    {
        requiereRol('Administrador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_comite");
            exit();
        }

        $idComite = (int)($_POST['id_comite'] ?? $_POST['id'] ?? 0);
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        $observacionAdmin = trim($_POST['observacion_admin'] ?? '');

        $estadosValidos = ['Pendiente', 'Citado', 'Finalizado', 'Archivado'];
        if ($idComite <= 0 || !in_array($nuevoEstado, $estadosValidos, true)) {
            header("Location: index.php?action=gestion_comite&error=" . urlencode("Parámetros inválidos o incompletos para actualizar el caso del comité."));
            exit();
        }

        $db = $this->conectar();
        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $ok = $comiteModel->actualizarEstado($idComite, $nuevoEstado, $observacionAdmin);

        if ($ok) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['alerta_comite_descartada'] = true;
            header("Location: index.php?action=gestion_comite&actualizado=1");
        } else {
            header("Location: index.php?action=gestion_comite&error=" . urlencode("No se pudo guardar la actualización del comité en la base de datos."));
        }
        exit();
    }

    public function remitirComite(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id']) && empty($_SESSION['id_usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=portal_instructor");
            exit();
        }

        $idAprendiz = (int)($_POST['id_aprendiz'] ?? 0);
        $totalFaltas = (int)($_POST['total_faltas'] ?? 0);
        $porcentajeFaltas = (float)($_POST['porcentaje_faltas'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $idInstructor = (int)($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? 0);

        if ($idAprendiz <= 0 || empty($motivo)) {
            header("Location: index.php?action=portal_instructor&error=" . urlencode("Debe indicar un motivo claro para la remisión a comité."));
            exit();
        }

        $db = $this->conectar();
        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);

        $ok = $comiteModel->crearRemision($idAprendiz, $idInstructor, $totalFaltas, $porcentajeFaltas, $motivo);

        if ($ok) {
            unset($_SESSION['alerta_comite_descartada']);
            header("Location: index.php?action=portal_instructor&msg=" . urlencode("El aprendiz fue remitido exitosamente a Comité de Evaluación y Seguimiento. El caso fue notificado a la Administración."));
        } else {
            header("Location: index.php?action=portal_instructor&error=" . urlencode("El aprendiz ya cuenta con un caso activo en Comité o no se pudo completar la remisión."));
        }
        exit();
    }
}
