<?php
$fichas = $fichas ?? [];
$idFichaSeleccionada = $idFichaSeleccionada ?? 0;
$fichaActual = $fichaActual ?? null;
$horarios = $horarios ?? [];
$nombreUsuario = !empty($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : ($nombreUsuario ?? 'Administrador');
$rolUsuario = !empty($_SESSION['rol']) ? $_SESSION['rol'] : ($rolUsuario ?? 'Administrador');
$puedeGestionar = in_array($rolUsuario, ['Administrador', 'Instructor'], true);

// Organizar horarios por día de la semana para la visualización semanal
$diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$horariosPorDia = [];
foreach ($diasSemana as $d) {
    $horariosPorDia[$d] = [];
}

foreach ($horarios as $h) {
    $diaNorm = ucfirst(strtolower(trim($h['dia_semana'])));
    if ($diaNorm === 'Miercoles') $diaNorm = 'Miércoles';
    if ($diaNorm === 'Sabado') $diaNorm = 'Sábado';

    if (isset($horariosPorDia[$diaNorm])) {
        $horariosPorDia[$diaNorm][] = $h;
    }
}

// Cálculo dinámico de mes y año
$mesActual = isset($mesActual) ? (int)$mesActual : (int)date('n');
$anioActual = isset($anioActual) ? (int)$anioActual : (int)date('Y');

$mesesNombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$primerDiaTimestamp = mktime(0, 0, 0, $mesActual, 1, $anioActual);
$totalDiasMes = (int)date('t', $primerDiaTimestamp);
$diaSemanaInicio = (int)date('N', $primerDiaTimestamp); // 1 = Lunes, 7 = Domingo

// Mes anterior
$mesAnterior = $mesActual - 1;
$anioAnterior = $anioActual;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anioAnterior--;
}
$totalDiasMesAnterior = (int)date('t', mktime(0, 0, 0, $mesAnterior, 1, $anioAnterior));

// Mes siguiente
$mesSiguiente = $mesActual + 1;
$anioSiguiente = $anioActual;
if ($mesSiguiente > 12) {
    $mesSiguiente = 1;
    $anioSiguiente++;
}

// Festivos oficiales Colombia
$festivosColombia = [
    '2026-01-01' => 'Año Nuevo',
    '2026-01-12' => 'Reyes Magos',
    '2026-03-23' => 'San José',
    '2026-04-02' => 'Jueves Santo',
    '2026-04-03' => 'Viernes Santo',
    '2026-05-01' => 'Día del Trabajo',
    '2026-05-18' => 'Ascensión de Jesús',
    '2026-06-08' => 'Corpus Christi',
    '2026-06-15' => 'Sagrado Corazón',
    '2026-06-29' => 'San Pedro y San Pablo',
    '2026-07-20' => 'Día de la Independencia',
    '2026-08-07' => 'Batalla de Boyacá',
    '2026-08-17' => 'Asunción de la Virgen',
    '2026-10-12' => 'Día de la Raza',
    '2026-11-02' => 'Todos los Santos',
    '2026-11-16' => 'Independencia de Cartagena',
    '2026-12-08' => 'Inmaculada Concepción',
    '2026-12-25' => 'Navidad'
];

// Determinamos la ruta de retorno según el rol actual
$urlInicio = 'index.php?action=dashboard';
if ($rolUsuario === 'Instructor') {
    $urlInicio = 'index.php?action=portal_instructor';
} elseif ($rolUsuario === 'Aprendiz') {
    $urlInicio = 'index.php?action=portal_aprendiz';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario SENA - Cronograma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css?v=<?= file_exists('public/css/dashboard.css') ? filemtime('public/css/dashboard.css') : time(); ?>">
    <link rel="stylesheet" href="public/css/calendario.css?v=<?= file_exists('public/css/calendario.css') ? filemtime('public/css/calendario.css') : time(); ?>">
</head>

<body class="calendar-body bg-dark-app">

    <div class="dashboard-container dashboard-container-dark min-vh-100">

        <!-- Sidebar unificado de navegación modular -->
        <?php 
        $activo = 'calendario';
        $rol = $rolUsuario;
        require_once __DIR__ . '/../partials/sidebar.php'; 
        ?>

        <!-- Contenido Principal -->
        <main class="main-content calendar-main-content">
            <?php require_once __DIR__ . '/../partials/mensajes.php'; ?>

            <!-- Barra Superior Institucional -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom border-secondary">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle bg-dark border border-secondary text-success">
                        <i class="bi bi-calendar3 fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Calendario</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <?php if ($puedeGestionar): ?>
                        <a href="index.php?action=descargar_plantilla_calendario" class="btn btn-outline-secondary rounded-pill px-3 text-white btn-border-slate" title="Descargar plantilla Excel">
                            <i class="bi bi-download me-1 text-info"></i> Plantilla Excel
                        </a>
                        <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Cargar Horario Excel
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEvento">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Evento
                        </button>
                    <?php else: ?>
                    <?php endif; ?>

                    <div class="header-profile-card d-flex align-items-center gap-3 cursor-pointer" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver perfil">
                        <div class="header-avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="fw-bold mb-0 text-white user-name-title"><?= htmlspecialchars((string)$nombreUsuario); ?></h6>
                            <span class="text-muted user-doc-subtitle"><?= htmlspecialchars((string)$rolUsuario); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selector de Ficha Activa -->
            <div class="card bg-dark border-secondary p-3 mb-4 rounded-4 shadow-sm">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <form method="GET" action="index.php" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="action" value="calendario">
                            <label class="text-white small fw-bold text-nowrap mb-0">
                                <i class="bi bi-journal-bookmark-fill text-warning me-1"></i> Ficha Formación:
                            </label>
                            <select name="id_ficha" class="form-select form-select-dark text-white rounded-pill" onchange="this.form.submit()">
                                <?php foreach ($fichas as $f): ?>
                                    <option value="<?= $f['id_ficha']; ?>" <?= ((int)$f['id_ficha'] === $idFichaSeleccionada) ? 'selected' : ''; ?>>
                                        Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa']); ?> (<?= htmlspecialchars($f['jornada']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="badge bg-secondary text-white py-2 px-3 rounded-pill">
                            <i class="bi bi-clock-history me-1 text-warning"></i> Jornada: 06:00 AM - 12:00 PM (Descanso: 08:45 - 09:15)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Controles del Calendario (Navegación de Mes y Filtros) -->
            <div class="row g-4">

                <!-- Columna Izquierda: Panel de Filtros y Calendarios -->
                <div class="col-xl-3 col-lg-4">
                    <div class="calendar-sidebar p-3 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-white mb-0">
                                <i class="bi bi-funnel-fill text-success me-2"></i>Mis Calendarios
                            </h6>
                            <span class="badge bg-dark border border-secondary text-white-50"><?= htmlspecialchars($rolUsuario); ?></span>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="clases" id="filtroClases" checked>
                            <label class="form-check-label text-white small" for="filtroClases">
                                <span class="legend-dot dot-green"></span>
                                Clases y Sesiones ADSO
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="asistencias" id="filtroAsistencias" checked>
                            <label class="form-check-label text-white small" for="filtroAsistencias">
                                <span class="legend-dot dot-blue"></span>
                                Control de Asistencia RFID
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="entregas" id="filtroEntregas" checked>
                            <label class="form-check-label text-white small" for="filtroEntregas">
                                <span class="legend-dot dot-yellow"></span>
                                Entregas y Evaluaciones
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="comites" id="filtroComites" checked>
                            <label class="form-check-label text-white small" for="filtroComites">
                                <span class="legend-dot dot-purple"></span>
                                Comités y Reuniones
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="festivos" id="filtroFestivos" checked>
                            <label class="form-check-label text-white small" for="filtroFestivos">
                                <span class="legend-dot dot-red"></span>
                                Festivos Nacionales
                            </label>
                        </div>

                        <hr class="border-secondary my-3">

                        <!-- Resumen del Horario Cargado -->
                        <div class="p-3 rounded bg-dark border border-secondary mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-semibold text-white small">
                                    <i class="bi bi-clock-fill text-warning me-1"></i> Franjas de Formación
                                </span>
                                <span class="badge bg-success rounded-pill"><?= count($horarios); ?> bloques</span>
                            </div>
                            <div class="small text-white-50">
                                <div><i class="bi bi-circle-fill text-success icon-bullet-xs"></i> <b>06:00 - 08:45:</b> Instructor 1</div>
                                <div><i class="bi bi-cup-hot-fill text-warning icon-bullet-xs"></i> <b>08:45 - 09:15:</b> Receso / Descanso</div>
                                <div><i class="bi bi-circle-fill text-primary icon-bullet-xs"></i> <b>09:15 - 12:00:</b> Instructor 2</div>
                            </div>
                        </div>



                    </div>
                </div>

                <!-- Columna Derecha: Vista del Calendario Principal -->
                <div class="col-xl-9 col-lg-8">
                    <div class="calendar-main-card p-3 shadow-sm">

                        <!-- Barra de controles y pestañas de vista -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 btn-border-slate" id="btnMesAnterior" title="Mes anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 btn-border-slate" id="btnMesSiguiente" title="Mes siguiente">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="btnHoy">
                                    Hoy
                                </button>
                                <h5 class="fw-bold text-white mb-0 ms-2" id="labelMesActual"><?= htmlspecialchars($mesesNombres[$mesActual] . ' ' . $anioActual); ?></h5>
                            </div>

                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success px-3 active" id="btnTabMes">Mes</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white px-3 btn-border-slate" id="btnTabSemana">
                                    <i class="bi bi-table me-1"></i> Horario Semanal
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white px-3 btn-border-slate" id="btnTabAgenda">Lista</button>
                            </div>
                        </div>

                        <!-- VISTA 1: CALENDARIO MENSUAL SENA -->
                        <div id="vistaMesContainer">
                            <!-- Encabezado de Días de la Semana -->
                            <div class="calendar-grid-header">
                                <div>Lun</div>
                                <div>Mar</div>
                                <div>Mié</div>
                                <div>Jue</div>
                                <div>Vie</div>
                                <div>Sáb</div>
                                <div>Dom</div>
                            </div>

                            <!-- Grid Calendario SENA -->
                            <div class="calendar-grid-body" id="gridCalendarioBody">
                                <?php
                                $diasNombreMap = [
                                    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                                    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
                                ];

                                // 1. Celdas de relleno del mes anterior
                                $celdasPrevias = $diaSemanaInicio - 1;
                                for ($k = $celdasPrevias; $k >= 1; $k--):
                                    $diaPrevNum = $totalDiasMesAnterior - $k + 1;
                                ?>
                                    <div class="calendar-day-cell other-month">
                                        <span class="day-number"><?= $diaPrevNum; ?></span>
                                    </div>
                                <?php endfor; ?>

                                <?php
                                // 2. Días del mes actual
                                $diaHoyNum = (int)date('j');
                                $mesHoyNum = (int)date('n');
                                $anioHoyNum = (int)date('Y');

                                for ($dia = 1; $dia <= $totalDiasMes; $dia++):
                                    $fechaActualIso = sprintf('%04d-%02d-%02d', $anioActual, $mesActual, $dia);
                                    $timestampDia = mktime(0, 0, 0, $mesActual, $dia, $anioActual);
                                    $numDiaSemana = (int)date('N', $timestampDia);
                                    $nombreDiaSemana = $diasNombreMap[$numDiaSemana] ?? '';
                                    $bloquesDelDia = $horariosPorDia[$nombreDiaSemana] ?? [];
                                    $esHoy = ($dia === $diaHoyNum && $mesActual === $mesHoyNum && $anioActual === $anioHoyNum);
                                    $esFestivo = isset($festivosColombia[$fechaActualIso]);
                                    $nombreFestivo = $esFestivo ? $festivosColombia[$fechaActualIso] : '';

                                    $infoDiaJson = htmlspecialchars(json_encode([
                                        'fecha' => sprintf('%02d de %s de %04d', $dia, $mesesNombres[$mesActual], $anioActual),
                                        'dia_semana' => $nombreDiaSemana,
                                        'bloques' => $bloquesDelDia,
                                        'festivo' => $nombreFestivo,
                                        'ficha' => $fichaActual ? ('Ficha ' . $fichaActual['numero_ficha'] . ' - ' . $fichaActual['nombre_programa']) : 'General'
                                    ]), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <div class="calendar-day-cell <?= $esHoy ? 'today' : ''; ?> celda-dia-click" 
                                         data-dia-info='<?= $infoDiaJson; ?>'
                                         title="Haz clic para ver instructores y horario de este día">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="day-number mb-0"><?= $dia; ?></span>
                                            <?php if ($esHoy): ?>
                                                <span class="badge bg-success rounded-pill badge-today">HOY</span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($esFestivo): ?>
                                            <div class="event-badge event-danger-custom mb-1" title="<?= htmlspecialchars($nombreFestivo); ?>">
                                                <i class="bi bi-flag-fill me-1"></i> <?= htmlspecialchars($nombreFestivo); ?>
                                            </div>
                                        <?php elseif (!empty($bloquesDelDia)): ?>
                                            <?php foreach ($bloquesDelDia as $blk): ?>
                                                <div class="event-badge <?= (substr($blk['entrada'], 0, 2) < '09') ? 'event-sena' : 'event-blue event-google'; ?> mb-1" 
                                                     title="<?= htmlspecialchars(($blk['instructor_nombre'] ?? 'Instructor') . ' - ' . ($blk['competencia'] ?? 'Formación')); ?>">
                                                    <span class="fw-bold"><?= substr($blk['entrada'], 0, 5); ?></span> 
                                                    <?= htmlspecialchars(!empty($blk['instructor_nombre']) ? explode(' ', trim($blk['instructor_nombre']))[0] : 'Clase'); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php elseif ($nombreDiaSemana === 'Domingo'): ?>
                                            <small class="text-white-50 text-descanso-sm">Descanso</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>

                                <?php
                                // 3. Celdas de relleno del mes siguiente
                                $totalCeldasRenderizadas = $celdasPrevias + $totalDiasMes;
                                $celdasSiguientes = (7 - ($totalCeldasRenderizadas % 7)) % 7;
                                for ($s = 1; $s <= $celdasSiguientes; $s++):
                                ?>
                                    <div class="calendar-day-cell other-month">
                                        <span class="day-number"><?= $s; ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- VISTA 2: HORARIO FORMATIVO SEMANAL (06:00 A 12:00) -->
                        <div id="vistaSemanaContainer" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="text-white fw-bold mb-0">
                                        <i class="bi bi-calendar-week-fill text-success me-2"></i>Horario Semanal de Formación (Jornada 06:00 AM - 12:00 PM)
                                    </h6>
                                    <small class="text-white-50">Distribución de bloques con instructores y descanso intermedio</small>
                                </div>
                                <?php if ($puedeGestionar): ?>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBloqueManual">
                                        <i class="bi bi-plus-circle me-1"></i> Agregar Bloque Manual
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="row g-2">
                                <?php foreach ($diasSemana as $diaNombre): ?>
                                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                                        <div class="timetable-col h-100">
                                            <div class="timetable-day-header">
                                                <?= $diaNombre; ?>
                                            </div>

                                            <?php 
                                            $bloquesDia = $horariosPorDia[$diaNombre] ?? [];
                                            $primerBloque = null;
                                            $segundoBloque = null;

                                            foreach ($bloquesDia as $blk) {
                                                $hIni = substr($blk['entrada'], 0, 5);
                                                if ($hIni <= '08:00') {
                                                    $primerBloque = $blk;
                                                } else {
                                                    $segundoBloque = $blk;
                                                }
                                            }
                                            ?>

                                            <!-- Bloque 1: 06:00 AM - 08:45 AM -->
                                            <?php if ($primerBloque): ?>
                                                <div class="block-card bloque-1">
                                                    <span class="time-badge time-badge-green">
                                                        <i class="bi bi-clock me-1"></i><?= substr($primerBloque['entrada'], 0, 5); ?> - <?= substr($primerBloque['salida'], 0, 5); ?>
                                                    </span>
                                                    <h6 class="text-white fw-bold mb-1 text-sm-bold">
                                                        <?= htmlspecialchars($primerBloque['competencia'] ?? 'Sesión Formativa'); ?>
                                                    </h6>
                                                    <div class="text-white-50 small mb-1 text-xs">
                                                        <i class="bi bi-person-badge-fill text-success me-1"></i>
                                                        <?= htmlspecialchars(!empty($primerBloque['instructor_nombre']) ? $primerBloque['instructor_nombre'] : 'Por Asignar'); ?>
                                                    </div>
                                                    <?php if (!empty($primerBloque['ambiente'])): ?>
                                                        <span class="badge bg-dark border border-secondary text-white-50 badge-xxs">
                                                            <?= htmlspecialchars($primerBloque['ambiente']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="block-card bloque-1 opacity-50 text-center py-2">
                                                    <span class="time-badge time-badge-green">06:00 - 08:45</span>
                                                    <p class="text-white-50 small mb-0 text-xxs">Sin clase asignada</p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Receso Obligatorio: 08:45 AM - 09:15 AM -->
                                            <div class="receso-card">
                                                ☕ 08:45 - 09:15<br><span class="descanso-label">Descanso</span>
                                            </div>

                                            <!-- Bloque 2: 09:15 AM - 12:00 PM -->
                                            <?php if ($segundoBloque): ?>
                                                <div class="block-card bloque-2">
                                                    <span class="time-badge time-badge-blue">
                                                        <i class="bi bi-clock me-1"></i><?= substr($segundoBloque['entrada'], 0, 5); ?> - <?= substr($segundoBloque['salida'], 0, 5); ?>
                                                    </span>
                                                    <h6 class="text-white fw-bold mb-1 text-sm-bold">
                                                        <?= htmlspecialchars($segundoBloque['competencia'] ?? 'Sesión Formativa'); ?>
                                                    </h6>
                                                    <div class="text-white-50 small mb-1 text-xs">
                                                        <i class="bi bi-person-badge-fill text-primary me-1"></i>
                                                        <?= htmlspecialchars(!empty($segundoBloque['instructor_nombre']) ? $segundoBloque['instructor_nombre'] : 'Por Asignar'); ?>
                                                    </div>
                                                    <?php if (!empty($segundoBloque['ambiente'])): ?>
                                                        <span class="badge bg-dark border border-secondary text-white-50 badge-xxs">
                                                            <?= htmlspecialchars($segundoBloque['ambiente']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="block-card bloque-2 opacity-50 text-center py-2">
                                                    <span class="time-badge time-badge-blue">09:15 - 12:00</span>
                                                    <p class="text-white-50 small mb-0 text-xxs">Sin clase asignada</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- VISTA 3: AGENDA / TABLA DETALLADA -->
                        <div id="vistaAgendaContainer" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-white fw-bold mb-0">
                                    <i class="bi bi-list-task text-info me-2"></i>Lista Detallada de Bloques de Formación
                                </h6>
                                <span class="badge bg-dark border border-secondary text-white">Total: <?= count($horarios); ?></span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0 table-agenda-dark">
                                    <thead>
                                        <tr class="text-white-50 small table-agenda-header">
                                            <th>DÍA</th>
                                            <th>HORARIO</th>
                                            <th>INSTRUCTOR</th>
                                            <th>COMPETENCIA / AMBIENTE</th>
                                            <?php if ($puedeGestionar): ?>
                                                <th class="text-end">ACCIONES</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($horarios)): ?>
                                            <?php foreach ($horarios as $h): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary text-white fw-semibold">
                                                            <?= htmlspecialchars($h['dia_semana']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="time-badge <?= (substr($h['entrada'], 0, 2) < '09') ? 'time-badge-green' : 'time-badge-blue'; ?>">
                                                            <?= substr($h['entrada'], 0, 5) . ' - ' . substr($h['salida'], 0, 5); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold text-white">
                                                            <i class="bi bi-person-fill text-success me-1"></i>
                                                            <?= htmlspecialchars(!empty($h['instructor_nombre']) ? $h['instructor_nombre'] : 'Sin Asignar'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-white small fw-semibold">
                                                            <?= htmlspecialchars(!empty($h['competencia']) ? $h['competencia'] : 'General'); ?>
                                                        </div>
                                                        <?php if (!empty($h['ambiente'])): ?>
                                                            <small class="text-white-50"><i class="bi bi-geo-alt-fill text-warning me-1"></i><?= htmlspecialchars($h['ambiente']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($puedeGestionar): ?>
                                                        <td class="text-end">
                                                            <a href="index.php?action=eliminar_bloque_calendario&id_horario=<?= $h['id_horario']; ?>&id_ficha=<?= $idFichaSeleccionada; ?>" 
                                                               class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-eliminar-calendario"
                                                               data-dia="<?= htmlspecialchars($h['dia_semana']); ?>"
                                                               data-hora="<?= substr($h['entrada'], 0, 5) . ' - ' . substr($h['salida'], 0, 5); ?>">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?= $puedeGestionar ? 5 : 4; ?>" class="text-center py-4 text-white-50">
                                                    <?= $puedeGestionar ? 'No hay horarios cargados para esta ficha. ¡Puedes usar el botón "Cargar Horario Excel" arriba!' : 'No hay horarios programados para esta ficha de formación.'; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </main>
    </div>

    <?php if ($puedeGestionar): ?>
    <!-- Modal: Cargar Horario desde Excel / CSV -->
    <div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Cargar Horario desde Excel al Calendario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formImportarHorario" action="index.php?action=importar_horario_calendario" method="POST" enctype="multipart/form-data">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Ficha de Formación Destino</label>
                            <select name="id_ficha" class="form-select form-select-dark text-white" required>
                                <?php foreach ($fichas as $f): ?>
                                    <option value="<?= $f['id_ficha']; ?>" <?= ((int)$f['id_ficha'] === $idFichaSeleccionada) ? 'selected' : ''; ?>>
                                        Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Archivo Excel (.xlsx) o Archivo (.csv)</label>
                            <input type="file" name="archivo_excel" class="form-control form-control-dark text-white" accept=".xlsx, .csv, .txt" required>
                            <small class="text-white-50 d-block mt-1">Formato soportado: Horario trimestral SENA (.xlsx o .csv)</small>
                        </div>

                        <div class="form-check mb-3 text-start">
                            <input class="form-check-input" type="checkbox" name="sobrescribir" value="1" id="checkSobrescribir" checked>
                            <label class="form-check-label text-white small" for="checkSobrescribir">
                                Reemplazar el horario existente de esta ficha
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Procesar e Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar Bloque Manual al Calendario -->
    <div class="modal fade" id="modalBloqueManual" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-plus-circle-fill text-success me-2"></i>Agregar Bloque Manual al Calendario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=agregar_bloque_calendario" method="POST">
                    <input type="hidden" name="id_ficha" value="<?= $idFichaSeleccionada; ?>">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Día de la Semana</label>
                            <select name="dia_semana" class="form-select form-select-dark text-white" required>
                                <?php foreach ($diasSemana as $d): ?>
                                    <option value="<?= $d; ?>"><?= $d; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Hora Inicio</label>
                                <input type="time" name="entrada" class="form-control form-control-dark text-white" value="06:00" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Hora Fin</label>
                                <input type="time" name="salida" class="form-control form-control-dark text-white" value="08:45" required>
                            </div>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Nombre del Instructor</label>
                            <input type="text" name="instructor_nombre" class="form-control form-control-dark text-white" placeholder="Ej. Juan Camilo Vanegas" required>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Competencia / Asignatura</label>
                            <input type="text" name="competencia" class="form-control form-control-dark text-white" placeholder="Ej. Programación de Software ADSO">
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Ambiente / Aula</label>
                            <input type="text" name="ambiente" class="form-control form-control-dark text-white" placeholder="Ej. Ambiente 204">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">
                            Guardar Bloque
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Nuevo Evento -->
    <div class="modal fade" id="modalNuevoEvento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-calendar-plus text-success me-2"></i>Nuevo Evento / Clase en Calendario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevoEvento">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Título del Evento / Sesión</label>
                            <input type="text" class="form-control form-control-dark text-white" id="eventoTitulo" placeholder="Ej. Sesión Programación Web ADSO" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Fecha</label>
                                <input type="date" class="form-control form-control-dark text-white" id="eventoFecha" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Hora de Inicio</label>
                                <input type="time" class="form-control form-control-dark text-white" id="eventoHora" value="08:00" required>
                            </div>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Categoría / Tipo de Evento</label>
                            <select class="form-select form-select-dark text-white" id="eventoTipo" required>
                                <option value="sena">Clase / Formación SENA (Verde)</option>
                                <option value="virtual">Sesión Virtual (Azul)</option>
                                <option value="warning">Entrega / Evaluación (Amarillo)</option>
                                <option value="purple">Comité / Administrativo (Púrpura)</option>
                            </select>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Ficha Asociada (Opcional)</label>
                            <select class="form-select form-select-dark text-white" id="eventoFicha">
                                <option value="">Todas las Fichas / General</option>
                                <?php if (!empty($fichas)): ?>
                                    <?php foreach ($fichas as $f): ?>
                                        <option value="<?= htmlspecialchars($f['id_ficha']); ?>">
                                            Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Descripción o Enlace</label>
                            <textarea class="form-control form-control-dark text-white" id="eventoDescripcion" rows="2" placeholder="Detalles de la sesión..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Guardar Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal de Perfil & Cierre de Sesión -->
    <?php require_once __DIR__ . '/../partials/modal_perfil.php'; ?>

    <!-- Bootstrap & SweetAlert2 & JS Modular -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.CALENDARIO_CONFIG = {
            mes: <?= (int)$mesActual; ?>,
            anio: <?= (int)$anioActual; ?>,
            fichaId: <?= (int)$idFichaSeleccionada; ?>,
            fichaTexto: <?= json_encode($fichaActual ? ('Ficha ' . $fichaActual['numero_ficha'] . ' - ' . $fichaActual['nombre_programa']) : 'General'); ?>,
            horariosPorDia: <?= json_encode($horariosPorDia); ?>,
            festivos: <?= json_encode($festivosColombia); ?>
        };
    </script>
    <script src="public/js/calendario.js?v=<?= file_exists('public/js/calendario.js') ? filemtime('public/js/calendario.js') : time(); ?>"></script>
</body>

</html>
