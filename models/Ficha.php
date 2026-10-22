<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Ficha - Responsable de la gestión de programas de formación y fichas SENA
 */
class Ficha
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
     * Retorna el número total de fichas en la base de datos
     */
    public function obtenerTotal(): int
    {
        $sql = "SELECT COUNT(*) as total FROM ficha";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    /**
     * Retorna el listado de todas las fichas ordenadas descendentemente
     */
    public function obtenerTodas(): array
    {
        $sql = "SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha ORDER BY id_ficha DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una ficha por su ID primario
     */
    public function obtenerPorId(int $idFicha): ?array
    {
        $stmt = $this->conn->prepare("SELECT id_ficha, numero_ficha, nombre_programa, jornada, fk_usuario FROM ficha WHERE id_ficha = ? LIMIT 1");
        $stmt->bind_param("i", $idFicha);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Verifica si un número de ficha ya existe, opcionalmente excluyendo un ID específico (útil en updates)
     */
    public function existeNumero(string $numeroFicha, ?int $excluirId = null): bool
    {
        if ($excluirId && $excluirId > 0) {
            $stmt = $this->conn->prepare("SELECT id_ficha FROM ficha WHERE numero_ficha = ? AND id_ficha != ? LIMIT 1");
            $stmt->bind_param("si", $numeroFicha, $excluirId);
        } else {
            $stmt = $this->conn->prepare("SELECT id_ficha FROM ficha WHERE numero_ficha = ? LIMIT 1");
            $stmt->bind_param("s", $numeroFicha);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Inserta una nueva ficha validando previamente que el número no esté duplicado
     */
    public function crear(string $numeroFicha, string $programa, string $jornada = 'Diurna', ?int $fkInstructor = null): bool
    {
        if ($this->existeNumero($numeroFicha)) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO ficha (numero_ficha, nombre_programa, jornada, fk_usuario) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error al preparar crear ficha: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssi", $numeroFicha, $programa, $jornada, $fkInstructor);
        return $stmt->execute();
    }

    /**
     * Actualiza una ficha existente validando que el número no pertenezca a otra
     */
    public function actualizar(int $idFicha, string $numeroFicha, string $programa, string $jornada = 'Diurna', ?int $fkInstructor = null): bool
    {
        if ($this->existeNumero($numeroFicha, $idFicha)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE ficha SET numero_ficha = ?, nombre_programa = ?, jornada = ?, fk_usuario = ? WHERE id_ficha = ?");
        $stmt->bind_param("sssii", $numeroFicha, $programa, $jornada, $fkInstructor, $idFicha);
        return $stmt->execute();
    }

    /**
     * Retorna las fichas con el conteo de aprendices y el instructor líder
     */
    public function obtenerFichasConConteo(int $limite = 10): array
    {
        $sql = "SELECT f.id_ficha, f.numero_ficha, f.nombre_programa, f.jornada,
                       COUNT(ap.id_aprendiz) as total_aprendices,
                       CONCAT(IFNULL(u.nombre,''), ' ', IFNULL(u.apellido,'')) as instructor_lider
                FROM ficha f
                LEFT JOIN aprendiz ap ON f.id_ficha = ap.fk_ficha
                LEFT JOIN usuario u ON f.fk_usuario = u.id_usuario
                GROUP BY f.id_ficha, f.numero_ficha, f.nombre_programa, f.jornada, u.nombre, u.apellido
                ORDER BY f.id_ficha DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
