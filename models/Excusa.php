<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Excusa - Responsable de la radicación, control y consulta de justificaciones médicas
 */
class Excusa
{
    private $conn;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Retorna la cantidad de excusas que se encuentran en estado Pendiente
     */
    public function obtenerTotalPendientes(): int
    {
        $sql = "SELECT COUNT(*) as total FROM excusa WHERE LOWER(estado) = 'pendiente'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    /**
     * Radica una nueva justificación médica asociándola al aprendiz y a la fecha de inasistencia
     */
    public function guardar(string $documentoAprendiz, string $fechaFalta, string $motivo, string $nombreArchivo): bool
    {
        $sqlBuscar = "SELECT ap.id_aprendiz, u.id_usuario 
                      FROM aprendiz ap 
                      INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                      WHERE u.identificacion = ? OR u.nombre_usuario = ? 
                      LIMIT 1";
        $stmtBuscar = $this->conn->prepare($sqlBuscar);
        if (!$stmtBuscar) return false;

        $stmtBuscar->bind_param("ss", $documentoAprendiz, $documentoAprendiz);
        $stmtBuscar->execute();
        $aprendiz = $stmtBuscar->get_result()->fetch_assoc();

        if (!$aprendiz) {
            error_log("No se encontró aprendiz con documento: " . $documentoAprendiz);
            return false;
        }

        $idAprendiz = (int)$aprendiz['id_aprendiz'];

        // Si no existe asistencia previa para esa fecha, se genera para asociar la excusa
        $sqlAsist = "SELECT id_asistencia FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtAsist = $this->conn->prepare($sqlAsist);
        $stmtAsist->bind_param("is", $idAprendiz, $fechaFalta);
        $stmtAsist->execute();
        $asistRes = $stmtAsist->get_result()->fetch_assoc();

        if ($asistRes) {
            $idAsistencia = (int)$asistRes['id_asistencia'];
        } else {
            $sqlInsAsist = "INSERT INTO asistencia (fecha_asistencia, estado_entrada, estado_salida, fk_aprendiz) 
                            VALUES (?, 'Inasistencia', 'Justificada', ?)";
            $stmtInsAsist = $this->conn->prepare($sqlInsAsist);
            $stmtInsAsist->bind_param("si", $fechaFalta, $idAprendiz);
            $stmtInsAsist->execute();
            $idAsistencia = (int)$this->conn->insert_id;
        }

        $sqlExcusa = "INSERT INTO excusa (archivo, observacion, estado, fecha_subida, fk_asistencia) 
                      VALUES (?, ?, 'Pendiente', CURDATE(), ?)";
        $stmtExcusa = $this->conn->prepare($sqlExcusa);
        if (!$stmtExcusa) return false;

        $stmtExcusa->bind_param("ssi", $nombreArchivo, $motivo, $idAsistencia);
        return $stmtExcusa->execute();
    }
}
