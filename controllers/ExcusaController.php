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

    // Procesar subida de excusa médica o justificación
    public function guardarExcusa(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentoAprendiz = trim($_POST['documento_aprendiz'] ?? '');
            $fechaFalta = trim($_POST['fecha_falta'] ?? '');
            $motivo = trim($_POST['motivo'] ?? '');

            if (!empty($documentoAprendiz) && !empty($fechaFalta) && !empty($motivo)) {
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
                            require_once __DIR__ . '/../models/Excusa.php';
                            $db = $this->conectar();
                            $excusaModel = new Excusa($db);
                            $excusaModel->guardar($documentoAprendiz, $fechaFalta, $motivo, $nombreArchivoFinal);
                        }
                    }
                }
            }
        }

        $origen = trim($_POST['origen'] ?? '');
        $redirect = ($origen === 'portal_aprendiz') ? "index.php?action=portal_aprendiz&excusa_subida=1" : "index.php?action=dashboard";
        header("Location: {$redirect}");
        exit();
    }
}
