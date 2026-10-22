<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/../librerias/HorarioParser.php';

/**
 * Modelo Horario - Responsable exclusivo de la persistencia y consultas de horarios en la base de datos
 */
class Horario
{
    private $conn;
    private $usuarioModel;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->usuarioModel = new Usuario($this->conn);
    }

    /**
     * Obtiene los horarios configurados, opcionalmente filtrados por ficha
     */
    public function obtenerHorarios(int $idFicha = 0): array
    {
        $sql = "SELECT h.*, 
                       f.numero_ficha, 
                       f.nombre_programa, 
                       CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, '')) AS instructor_nombre
                FROM horario h
                INNER JOIN ficha f ON h.fk_ficha = f.id_ficha
                LEFT JOIN usuario u ON h.fk_usuario_instructor = u.id_usuario";

        if ($idFicha > 0) {
            $sql .= " WHERE h.fk_ficha = ? ORDER BY FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Sabado', 'Domingo'), h.entrada ASC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param("i", $idFicha);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $sql .= " ORDER BY f.numero_ficha ASC, FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Sabado', 'Domingo'), h.entrada ASC";
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    /**
     * Agrega un bloque de formación a la tabla horario
     */
    public function agregarBloque(string $diaSemana, string $entrada, string $salida, int $idFicha, ?int $idInstructor = null, ?string $competencia = null, ?string $ambiente = null): bool
    {
        $sql = "INSERT INTO horario (dia_semana, entrada, salida, fk_ficha, fk_usuario_instructor, competencia, ambiente) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("sssiiss", $diaSemana, $entrada, $salida, $idFicha, $idInstructor, $competencia, $ambiente);
        return $stmt->execute();
    }

    /**
     * Elimina los horarios existentes de una ficha (útil para sobreescribir)
     */
    public function eliminarHorariosPorFicha(int $idFicha): bool
    {
        $sql = "DELETE FROM horario WHERE fk_ficha = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idFicha);
        return $stmt->execute();
    }

    /**
     * Elimina un bloque específico del calendario
     */
    public function eliminarBloque(int $idHorario): bool
    {
        $sql = "DELETE FROM horario WHERE id_horario = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idHorario);
        return $stmt->execute();
    }

    /**
     * Procesa un archivo (Excel o CSV) extrayendo sus bloques con HorarioParser y guardándolos en base de datos
     */
    public function procesarArchivoHorario(string $rutaArchivo, string $nombreOriginal, int $idFicha, bool $sobrescribir = true): array
    {
        $bloquesExtraidos = HorarioParser::extraerBloques($rutaArchivo, $nombreOriginal);

        if (empty($bloquesExtraidos)) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontraron bloques de formación válidos en el archivo o formato no soportado (.xlsx o .csv).'
            ];
        }

        if ($sobrescribir) {
            $this->eliminarHorariosPorFicha($idFicha);
        }

        $insertados = 0;
        $instructoresProcesados = [];

        foreach ($bloquesExtraidos as $b) {
            $instructorId = null;
            if (!empty($b['instructor'])) {
                $instructorId = $this->usuarioModel->buscarOCrearInstructorPorNombre($b['instructor']);
                if ($instructorId) {
                    $instructoresProcesados[$instructorId] = true;
                }
            }

            $guardado = $this->agregarBloque(
                $b['dia_semana'],
                $b['entrada'],
                $b['salida'],
                $idFicha,
                $instructorId,
                $b['competencia'] ?? null,
                $b['ambiente'] ?? null
            );

            if ($guardado) {
                $insertados++;
            }
        }

        return [
            'exito' => true,
            'insertados' => $insertados,
            'instructores' => count($instructoresProcesados),
            'mensaje' => "Se registraron {$insertados} bloques de formación con éxito."
        ];
    }

    /**
     * Genera la plantilla CSV de descarga delegando en el parser
     */
    public function generarPlantillaCsv(): string
    {
        return HorarioParser::generarPlantillaCsv();
    }
}
