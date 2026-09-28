public function dashboard() {
    // Asumiendo que usas PDO para conectar a la base de datos ($this->db)
    
    // 1. Total de aprendices inscritos
    $stmtTotal = $this->db->prepare("SELECT COUNT(*) as total FROM aprendices");
    $stmtTotal->execute();
    $totalAprendices = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Asistencias de hoy (fecha actual)
    $hoy = date('Y-m-d');
    $stmtAsistencias = $this->db->prepare("SELECT COUNT(*) as total FROM ingresos WHERE fecha = ? AND estado = 'A tiempo'");
    $stmtAsistencias->execute([$hoy]);
    $asistenciasHoy = $stmtAsistencias->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 3. Retardos de hoy
    $stmtRetardos = $this->db->prepare("SELECT COUNT(*) as total FROM ingresos WHERE fecha = ? AND estado = 'Retardo'");
    $stmtRetardos->execute([$hoy]);
    $retardosHoy = $stmtRetardos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 4. Excusas pendientes de aprobación
    $stmtExcusas = $this->db->prepare("SELECT COUNT(*) as total FROM excusas WHERE estado = 'Pendiente'");
    $stmtExcusas->execute();
    $excusasPendientes = $stmtExcusas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 5. Últimos marcajes para la tabla inferior
    $stmtTabla = $this->db->prepare("SELECT a.nombre_aprendiz, i.fecha, i.entrada, i.estado, i.salida FROM ingresos i JOIN aprendices a ON i.id_aprendiz = a.id ORDER BY i.id DESC LIMIT 5");
    $stmtTabla->execute();
    $ultimosMarcajes = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);

    // Pasamos todas estas variables a tu vista del dashboard
    include_once __DIR__ . '/../Views/auth/dashboard.php';
}