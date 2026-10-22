<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Ficha.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Excusa.php';
require_once __DIR__ . '/Horario.php';

/**
 * Modelo Dashboard - Fachada limpia para indicadores del panel principal y delegación a modelos especializados
 */
class Dashboard
{
    private $conn;
    private $fichaModel;
    private $usuarioModel;
    private $excusaModel;
    private $horarioModel;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->fichaModel = new Ficha($db);
        $this->usuarioModel = new Usuario($db);
        $this->excusaModel = new Excusa($db);
        $this->horarioModel = new Horario($db);
    }

    // =========================================================================
    // 1. MÉTRICAS DEL PANEL DE CONTROL (ADMINISTRADOR)
    // =========================================================================

    public function obtenerTotalAprendices(): int
    {
        $sql = "SELECT COUNT(*) as total FROM aprendiz";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    public function obtenerAsistenciasHoy(): int
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE()";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    public function obtenerRetardosHoy(): int
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia WHERE fecha_asistencia = CURDATE() AND LOWER(estado_entrada) LIKE '%retardo%'";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    public function obtenerExcusasPendientes(): int
    {
        return $this->excusaModel->obtenerTotalPendientes();
    }

    public function obtenerTotalFichas(): int
    {
        return $this->fichaModel->obtenerTotal();
    }

    public function obtenerFichas(): array
    {
        return $this->fichaModel->obtenerTodas();
    }

    public function obtenerFichasConConteo(int $limite = 10): array
    {
        return $this->fichaModel->obtenerFichasConConteo($limite);
    }

    public function obtenerTotalInstructores(): int
    {
        return $this->usuarioModel->obtenerTotalInstructores();
    }

    public function obtenerInstructores(int $limite = 5): array
    {
        return $this->usuarioModel->obtenerInstructores($limite);
    }

    public function obtenerUltimasAsistencias(int $limite = 5): array
    {
        $sql = "SELECT a.*, u.nombre, u.apellido 
                FROM asistencia a 
                INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz 
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                ORDER BY a.fecha_asistencia DESC, a.entrada DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error SQL en obtenerUltimasAsistencias: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // =========================================================================
    // 2. DELEGACIÓN A MODELOS ESPECIALIZADOS
    // =========================================================================

    // Delegación a Ficha
    public function crearFicha(string $numeroFicha, string $programa, string $jornada = 'Diurna'): bool
    {
        return $this->fichaModel->crear($numeroFicha, $programa, $jornada);
    }

    // Delegación a Usuario
    public function crearInstructor(string $identificacion, string $nombre, string $apellido, string $correo, string $contrasena): bool
    {
        return $this->usuarioModel->crearInstructor($identificacion, $nombre, $apellido, $correo, $contrasena);
    }

    public function crearAprendiz(string $identificacion, string $nombre, string $apellido, string $correo, string $contrasena, int $idFicha, ?string $codigoRfid = null): bool
    {
        return $this->usuarioModel->crearAprendiz($identificacion, $nombre, $apellido, $correo, $contrasena, $idFicha, $codigoRfid);
    }

    public function buscarOCrearInstructorPorNombre(string $nombreCompleto): ?int
    {
        return $this->usuarioModel->buscarOCrearInstructorPorNombre($nombreCompleto);
    }

    // Delegación a Excusa
    public function guardarExcusa(string $documentoAprendiz, string $fechaFalta, string $motivo, string $nombreArchivo): bool
    {
        return $this->excusaModel->guardar($documentoAprendiz, $fechaFalta, $motivo, $nombreArchivo);
    }

    // Delegación a Horario
    public function obtenerHorariosCalendario(int $idFicha = 0): array
    {
        return $this->horarioModel->obtenerHorarios($idFicha);
    }

    public function agregarBloqueCalendario(string $diaSemana, string $entrada, string $salida, int $idFicha, ?int $idInstructor = null, ?string $competencia = null, ?string $ambiente = null): bool
    {
        return $this->horarioModel->agregarBloque($diaSemana, $entrada, $salida, $idFicha, $idInstructor, $competencia, $ambiente);
    }

    public function eliminarHorariosPorFicha(int $idFicha): bool
    {
        return $this->horarioModel->eliminarHorariosPorFicha($idFicha);
    }

    public function eliminarBloqueCalendario(int $idHorario): bool
    {
        return $this->horarioModel->eliminarBloque($idHorario);
    }

    public function procesarArchivoHorarioCalendario(string $rutaArchivo, string $nombreOriginal, int $idFicha, bool $sobrescribir = true): array
    {
        return $this->horarioModel->procesarArchivoHorario($rutaArchivo, $nombreOriginal, $idFicha, $sobrescribir);
    }

    public function generarPlantillaCsvCalendario(): string
    {
        return $this->horarioModel->generarPlantillaCsv();
    }
}
?>