<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * ExcusaController - Responsable exclusivo de la gestión de Excusas Médicas por Administración (SRP)
 */
class ExcusaController
{
    private function conectar()
    {
        $database = new Database();
        return $database->getConnection();
    }

    public function viewExcusas(): void
    {
        requiereRol('Administrador');
        $db = $this->conectar();

        $sql = "SELECT e.id_excusa, e.archivo, e.observacion, e.estado, e.fecha_subida, e.fecha_revision,
                       u.nombre, u.apellido, u.identificacion, f.numero_ficha, a.fecha_asistencia
                FROM excusa e
                INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                ORDER BY (e.estado = 'Pendiente') DESC, e.fecha_subida DESC";
        $res = $db->query($sql);
        $excusas = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $activo = 'gestion_excusas';
        $rol = $_SESSION['rol'] ?? 'Administrador';
        $nombreUsuario = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolUsuario = $rol;
        require_once __DIR__ . '/../views/gestion/excusas.php';
    }

    public function procesarExcusa(string $nuevoEstado): void
    {
        requiereRol('Administrador');
        $idExcusa = (int)($_GET['id'] ?? 0);
        if ($idExcusa > 0) {
            $db = $this->conectar();
            $stmt = $db->prepare("UPDATE excusa SET estado = ?, fecha_revision = NOW() WHERE id_excusa = ?");
            $stmt->bind_param("si", $nuevoEstado, $idExcusa);
            $stmt->execute();

            // Si se aprueba, actualizar la asistencia asociada a Justificada
            if ($nuevoEstado === 'Aprobada') {
                $stmtAsis = $db->prepare("UPDATE asistencia a 
                                          INNER JOIN excusa e ON e.fk_asistencia = a.id_asistencia 
                                          SET a.estado_entrada = 'Justificada' 
                                          WHERE e.id_excusa = ?");
                $stmtAsis->bind_param("i", $idExcusa);
                $stmtAsis->execute();
            }
        }
        header("Location: index.php?action=gestion_excusas&actualizado=1");
        exit();
    }
}
