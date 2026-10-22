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

        $idComite = (int)($_POST['id_comite'] ?? 0);
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? 'Pendiente');
        $observacionAdmin = trim($_POST['observacion_admin'] ?? '');

        if ($idComite > 0) {
            $db = $this->conectar();
            require_once __DIR__ . '/../models/Comite.php';
            $comiteModel = new Comite($db);
            $comiteModel->actualizarEstado($idComite, $nuevoEstado, $observacionAdmin);
        }

        header("Location: index.php?action=gestion_comite&actualizado=1");
        exit();
    }
}
