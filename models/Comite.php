<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Comite - Gestión de remisiones de aprendices a Comité de Evaluación y Seguimiento
 * por inasistencias reiteradas o causales de deserción (Reglamento del Aprendiz SENA)
 */
class Comite
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

        $this->asegurarTablaExiste();
    }

    /**
     * Asegura la creación de la tabla comite si no existe en la base de datos
     */
    private function asegurarTablaExiste(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS comite (
            id_comite INT AUTO_INCREMENT PRIMARY KEY,
            fk_aprendiz INT NOT NULL,
            fk_usuario_instructor INT NOT NULL,
            fecha_remision DATETIME NOT NULL,
            total_faltas INT NOT NULL DEFAULT 0,
            porcentaje_fallas DECIMAL(5,2) DEFAULT 0,
            motivo TEXT NOT NULL,
            estado VARCHAR(30) DEFAULT 'Pendiente',
            observacion_admin TEXT NULL,
            fecha_resolucion DATETIME NULL,
            INDEX idx_comite_aprendiz (fk_aprendiz),
            INDEX idx_comite_instructor (fk_usuario_instructor),
            INDEX idx_comite_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->conn->query($sql);
    }

    /**
     * Registra una nueva remisión a comité emitida por un instructor
     */
    public function crearRemision(int $idAprendiz, int $idInstructor, int $totalFaltas, float $porcentaje, string $motivo): bool
    {
        // Verificar si ya existe una remisión pendiente para este aprendiz
        $sqlExiste = "SELECT id_comite FROM comite WHERE fk_aprendiz = ? AND estado IN ('Pendiente', 'Citado') LIMIT 1";
        $stmtExiste = $this->conn->prepare($sqlExiste);
        if ($stmtExiste) {
            $stmtExiste->bind_param("i", $idAprendiz);
            $stmtExiste->execute();
            $res = $stmtExiste->get_result();
            if ($res && $res->num_rows > 0) {
                // Ya se encuentra remitido con caso abierto
                return false;
            }
        }

        $ahora = date('Y-m-d H:i:s');
        $estadoInicial = 'Pendiente';

        $sql = "INSERT INTO comite (fk_aprendiz, fk_usuario_instructor, fecha_remision, total_faltas, porcentaje_fallas, motivo, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisidss", $idAprendiz, $idInstructor, $ahora, $totalFaltas, $porcentaje, $motivo, $estadoInicial);
        return $stmt->execute();
    }

    /**
     * Retorna el número de casos remitidos a comité con estado 'Pendiente'
     */
    public function obtenerTotalPendientes(): int
    {
        $sql = "SELECT COUNT(*) as total FROM comite WHERE estado = 'Pendiente'";
        $res = $this->conn->query($sql);
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtiene todos los casos de comité con datos cruzados de aprendiz, ficha e instructor
     */
    public function obtenerTodos(string $filtroEstado = ''): array
    {
        $sql = "SELECT c.*, 
                       u_ap.nombre AS aprendiz_nombre, u_ap.apellido AS aprendiz_apellido, u_ap.identificacion AS aprendiz_doc,
                       f.numero_ficha, f.nombre_programa, f.jornada,
                       u_inst.nombre AS instructor_nombre, u_inst.apellido AS instructor_apellido
                FROM comite c
                INNER JOIN aprendiz ap ON c.fk_aprendiz = ap.id_aprendiz
                INNER JOIN usuario u_ap ON ap.fk_usuario = u_ap.id_usuario
                LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                INNER JOIN usuario u_inst ON c.fk_usuario_instructor = u_inst.id_usuario";

        if (!empty($filtroEstado)) {
            $sql .= " WHERE c.estado = ?";
            $stmt = $this->conn->prepare($sql . " ORDER BY c.fecha_remision DESC");
            $stmt->bind_param("s", $filtroEstado);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        $sql .= " ORDER BY (CASE WHEN c.estado = 'Pendiente' THEN 1 WHEN c.estado = 'Citado' THEN 2 ELSE 3 END), c.fecha_remision DESC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene el caso de comité activo o más reciente para un aprendiz específico
     */
    public function obtenerPorAprendiz(int $idAprendiz): ?array
    {
        $sql = "SELECT c.*, 
                       u_inst.nombre AS instructor_nombre, u_inst.apellido AS instructor_apellido
                FROM comite c
                INNER JOIN usuario u_inst ON c.fk_usuario_instructor = u_inst.id_usuario
                WHERE c.fk_aprendiz = ?
                ORDER BY c.id_comite DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $idAprendiz);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Obtiene una lista de IDs de aprendices que tienen actualmente remisión activa (Pendiente o Citado)
     */
    public function obtenerIdsAprendicesEnComiteActivo(): array
    {
        $sql = "SELECT fk_aprendiz FROM comite WHERE estado IN ('Pendiente', 'Citado')";
        $res = $this->conn->query($sql);
        $ids = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ids[(int)$row['fk_aprendiz']] = true;
            }
        }
        return $ids;
    }

    /**
     * Actualiza el estado y resolución de un caso de comité por parte del administrador
     * Compatible con PDO y MySQLi
     */
    public function actualizarEstado(int $idComite, string $nuevoEstado, string $observacionAdmin = ''): bool
    {
        $ahora = date('Y-m-d H:i:s');
        $sql = "UPDATE comite SET estado = ?, observacion_admin = ?, fecha_resolucion = ? WHERE id_comite = ?";

        if ($this->conn instanceof PDO) {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            return $stmt->execute([$nuevoEstado, $observacionAdmin, $ahora, $idComite]);
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("sssi", $nuevoEstado, $observacionAdmin, $ahora, $idComite);
        return $stmt->execute();
    }
}
