<?php
// Variables con inicialización segura y tolerante a fallos
$fichas = (isset($fichas) && is_array($fichas)) ? $fichas : [];
$ultimosMarcajes = (isset($ultimosMarcajes) && is_array($ultimosMarcajes)) ? $ultimosMarcajes : [];
$fichasResumen = (isset($fichasResumen) && is_array($fichasResumen)) ? $fichasResumen : [];
$instructoresResumen = (isset($instructoresResumen) && is_array($instructoresResumen)) ? $instructoresResumen : [];
$totalFichas = $totalFichas ?? count($fichas);
$totalAprendices = $totalAprendices ?? 0;
$totalInstructores = $totalInstructores ?? 0;
$retardosHoy = $retardosHoy ?? 0;
$asistenciasHoy = $asistenciasHoy ?? 0;
$comitesPendientes = $comitesPendientes ?? 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css?v=<?= file_exists('public/css/dashboard.css') ? filemtime('public/css/dashboard.css') : time(); ?>">
</head>

<body>
    <div class="dashboard-container">
        <?php 
        $activo = (isset($vistaActiva) && $vistaActiva === 'escaner') ? 'escaner' : 'dashboard';
        require_once __DIR__ . '/../partials/sidebar.php'; 
        ?>

        <main class="main-content">
            <?php require_once __DIR__ . '/../partials/mensajes.php'; ?>
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-white">¡Hola, <?= htmlspecialchars((string)($nombreUsuario ?? 'Usuario')); ?>!</h2>
                    <p class="text-muted small mb-0">Resumen del sistema de gestión de asistencia</p>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">

                    <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver datos del administrador">
                        <div class="header-avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="fw-bold mb-0 text-white profile-title"><?= htmlspecialchars((string)($nombreUsuario ?? 'Administrador')); ?></h6>
                            <span class="text-muted profile-subtitle"><?= htmlspecialchars((string)($rolUsuario ?? 'Administrador')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($vistaActiva) && $vistaActiva === 'escaner'): ?>

                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="card shadow-lg text-center p-5 card-dark-panel">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="bi bi-credit-card-2-front fs-1 text-info"></i>
                                </div>
                                <h3 class="card-title text-white mb-3">Acerque su Tarjeta o Llavero RFID</h3>
                                <p class="text-muted mb-4">El lector procesará el código automáticamente y registrará la entrada o salida.</p>
                                <form id="formEscaner">
                                    <div class="mb-3">
                                        <input type="text" id="codigo_rfid" name="codigo_rfid" class="form-control form-control-lg text-center scanner-input-box" placeholder="Esperando lectura de RFID..." autofocus autocomplete="off" required>
                                    </div>
                                </form>
                                <div id="respuestaAsistencia" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="public/js/escaner.js"></script>

            <?php else: ?>

                <!-- Notificación de Remisiones a Comité de Evaluación y Seguimiento -->
                <?php if (!empty($comitesPendientes) && $comitesPendientes > 0): ?>
                    <div class="alert alert-warning border-warning d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-4 rounded-3 shadow-sm alert-comite-admin" role="alert">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-circle bg-dark border border-warning text-warning">
                                <i class="bi bi-gavel fs-4"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1 text-warning">
                                    ¡Atención Administrativa! Casos Pendientes de Comité (<?= (int)$comitesPendientes; ?>)
                                </h6>
                                <p class="mb-0 small text-white-50">
                                    Los instructores han remitido <?= (int)$comitesPendientes; ?> caso(s) de aprendices con inasistencias reiteradas o causales de deserción pendientes por citar.
                                </p>
                            </div>
                        </div>
                        <a href="index.php?action=gestion_comite&estado=Pendiente" class="btn btn-sm btn-warning text-dark fw-semibold rounded-pill px-3 text-nowrap">
                            <i class="bi bi-arrow-right-circle me-1"></i> Ver y Gestionar Casos
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Tarjetas de Métricas Institucionales (Fichas, Aprendices, Instructores, Retardos, Asistencias) -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-xl">
                        <a href="index.php?action=gestion_fichas" class="text-decoration-none">
                            <div class="dashboard-card stat-card shadow-sm h-100">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3 stat-icon-green">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0 text-white"><?= $totalFichas ?? 0; ?></h3>
                                        <span class="text-muted small fw-semibold">Número de Fichas</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-4 col-xl">
                        <a href="index.php?action=gestion_aprendices" class="text-decoration-none">
                            <div class="dashboard-card stat-card shadow-sm h-100">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3 stat-icon-blue">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0 text-white"><?= $totalAprendices ?? 0; ?></h3>
                                        <span class="text-muted small fw-semibold">Aprendices Activos</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-4 col-xl">
                        <a href="index.php?action=gestion_instructores" class="text-decoration-none">
                            <div class="dashboard-card stat-card shadow-sm h-100">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon me-3 stat-icon-purple">
                                        <i class="bi bi-person-video3"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0 text-white"><?= $totalInstructores ?? 0; ?></h3>
                                        <span class="text-muted small fw-semibold">Instructores</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-4 col-xl">
                        <div class="dashboard-card stat-card shadow-sm h-100">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3 stat-icon-yellow">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?= $retardosHoy ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Llegadas Tarde Hoy</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl">
                        <div class="dashboard-card stat-card shadow-sm h-100">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3 stat-icon-emerald">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?= $asistenciasHoy ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Asistencias Hoy</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Las 3 Columnas Institucionales: Fichas, Instructores e Historial de Marcajes -->
                <div class="row g-4 mb-4">
                    <!-- Columna 1: Resumen de Fichas de Formación -->
                    <div class="col-12 col-xl-4 col-lg-4">
                        <div class="dashboard-card card-dark-panel p-3 shadow-sm h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-white">
                                        <i class="bi bi-journal-bookmark text-success me-2"></i>Fichas Activas
                                    </h5>
                                    <small class="text-muted">Programas de formación</small>
                                </div>
                                <a href="index.php?action=gestion_fichas" class="btn btn-outline-success btn-sm rounded-pill px-3 btn-pill-xs">
                                    Ver Todas <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>

                            <div class="table-responsive flex-grow-1 table-scrollable-container">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>FICHA</th>
                                            <th>PROGRAMA</th>
                                            <th class="text-center">APRENDICES</th>
                                            <th class="text-end">ACCIÓN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($fichasResumen) && is_array($fichasResumen)): ?>
                                            <?php foreach ($fichasResumen as $fch): ?>
                                                <tr>
                                                    <td class="fw-bold text-white">
                                                        <span class="badge bg-dark border border-secondary px-2 py-1">
                                                            <?= htmlspecialchars($fch['numero_ficha']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-light small">
                                                        <div class="fw-semibold"><?= htmlspecialchars($fch['nombre_programa'] ?: 'ADSO'); ?></div>
                                                        <span class="text-muted btn-pill-xxs"><?= htmlspecialchars($fch['jornada'] ?: 'Diurna'); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1 fw-semibold badge-pill-xs">
                                                            <i class="bi bi-people me-1"></i><?= (int)($fch['total_aprendices'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="index.php?action=reporte_asistencia_excel&tipo=mensual&ficha_id=<?= $fch['id_ficha']; ?>" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2 me-1 btn-pill-xxs" title="Descargar Sábana Mensual en Excel">
                                                            <i class="bi bi-file-earmark-excel"></i>
                                                        </a>
                                                        <a href="index.php?action=calendario&ficha_id=<?= $fch['id_ficha']; ?>" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2 btn-pill-xxs" title="Ver horario de la ficha">
                                                            <i class="bi bi-calendar3"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No hay fichas registradas actualmente.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Resumen de Instructores Registrados -->
                    <div class="col-12 col-xl-4 col-lg-4">
                        <div class="dashboard-card card-dark-panel p-3 shadow-sm h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-white">
                                        <i class="bi bi-person-video3 text-info me-2"></i>Instructores SENA
                                    </h5>
                                    <small class="text-muted">Equipo docente vinculado</small>
                                </div>
                                <a href="index.php?action=gestion_instructores" class="btn btn-outline-info btn-sm rounded-pill px-3 btn-pill-xs">
                                    Gestionar <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>

                            <div class="table-responsive flex-grow-1 table-scrollable-container">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>INSTRUCTOR</th>
                                            <th>DOCUMENTO</th>
                                            <th>USUARIO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($instructoresResumen) && is_array($instructoresResumen)): ?>
                                            <?php foreach ($instructoresResumen as $inst): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="avatar-circle-sm avatar-icon-purple">
                                                                <i class="bi bi-person-fill"></i>
                                                            </div>
                                                            <span class="fw-semibold text-white small"><?= htmlspecialchars(trim($inst['nombre'] . ' ' . $inst['apellido'])); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($inst['identificacion']); ?></td>
                                                    <td class="text-muted small">
                                                        <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($inst['nombre_usuario']); ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No hay instructores registrados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 3: Historial de Marcajes Recientes (Supervisión) -->
                    <div class="col-12 col-xl-4 col-lg-4" id="seccionMarcajes">
                        <div class="dashboard-card card-dark-panel p-3 shadow-sm h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-white">
                                        <i class="bi bi-clock-history text-warning me-2"></i>Historial Marcajes
                                    </h5>
                                    <small class="text-muted">Marcajes de hoy en tiempo real</small>
                                </div>
                                <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-3 py-1 fw-semibold btn-pill-xs">
                                    <?= (!empty($ultimosMarcajes) && is_array($ultimosMarcajes)) ? count($ultimosMarcajes) : 0; ?> reg.
                                </span>
                            </div>

                            <div class="table-responsive flex-grow-1 table-scrollable-container">
                                <table class="table table-custom align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>APRENDIZ</th>
                                            <th>ENTRADA</th>
                                            <th>ESTADO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($ultimosMarcajes) && is_array($ultimosMarcajes)): ?>
                                            <?php foreach ($ultimosMarcajes as $registro): ?>
                                                <?php
                                                $nombreReg = (string)($registro['nombre'] ?? 'Sin Nombre');
                                                $apellidoReg = (string)($registro['apellido'] ?? '');
                                                $fechaReg = (string)($registro['fecha_asistencia'] ?? '-');
                                                $entradaReg = (string)($registro['entrada'] ?? '-');
                                                $estadoReg = (string)($registro['estado_entrada'] ?? 'N/A');
                                                $salidaReg = (string)($registro['salida'] ?? '-');
                                                $estadoLower = strtolower($estadoReg);

                                                $badgeClass = ($estadoLower === 'a tiempo' || $estadoLower === 'presente' || $estadoLower === 'asistió')
                                                    ? 'badge-status-presente'
                                                    : (($estadoLower === 'retardo' || $estadoLower === 'tarde') ? 'badge-status-tarde' : 'badge-status-ausente');
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="avatar-circle-sm avatar-icon-orange">
                                                                <i class="bi bi-person-badge"></i>
                                                            </div>
                                                            <div>
                                                                <span class="fw-semibold text-white small d-block"><?= htmlspecialchars(trim($nombreReg . ' ' . $apellidoReg)); ?></span>
                                                                <span class="text-muted text-size-xxs"><?= htmlspecialchars($fechaReg); ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted small">
                                                        <div><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= htmlspecialchars($entradaReg); ?></div>
                                                        <?php if ($salidaReg && $salidaReg !== '-'): ?>
                                                            <div class="text-size-xxs"><i class="bi bi-box-arrow-left text-danger me-1"></i><?= htmlspecialchars($salidaReg); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge rounded-pill px-2 py-1 fw-semibold <?= $badgeClass; ?> text-size-xxs">
                                                            <?= htmlspecialchars($estadoReg); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No hay registros de asistencias recientes.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

    <div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark modal-content-rounded">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-white"><i class="bi bi-journal-plus me-2 text-info"></i>Nueva Ficha</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=crear_ficha" method="POST">
                    <input type="hidden" name="origen" value="dashboard">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-white">Número de Ficha</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="numero_ficha" placeholder="Ej. 3234082" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-white">Programa de Formación</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="programa_formacion" placeholder="Ej. ADSO" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-cancel-modal input-radius-custom" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-action-custom btn-action-green">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
    </main>
</div>

<div class="modal fade" id="modalInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-content-dark modal-content-rounded">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-person-plus-fill me-2 text-warning"></i>Nuevo Instructor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crear_instructor" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Documento / Identificación</label>
                        <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="documento" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-white">Nombre</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-white">Apellido</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Nombre de Usuario / Correo</label>
                        <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Contraseña</label>
                        <input type="password" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="contrasena" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-cancel-modal input-radius-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action-orange btn-action-green">Guardar Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAprendiz" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-content-dark modal-content-rounded">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-people-fill me-2 text-success"></i>Registrar Nuevo Aprendiz
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crearAprendiz" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Documento de Identidad</label>
                        <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="documento" placeholder="Número de documento" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-white">Nombres</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="nombre" placeholder="Nombres" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-white">Apellidos</label>
                            <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="apellido" placeholder="Apellidos" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Nombre de Usuario (para Iniciar Sesión)</label>
                        <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="nombre_usuario" placeholder="Ej. usuario.aprendiz o correo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Contraseña</label>
                        <input type="password" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="contrasena" placeholder="Contraseña de acceso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Ficha de Formación</label>
                        <select class="form-select form-select-dark form-select-lg fs-6 text-white input-radius-custom" name="id_ficha" required>
                            <option value="" disabled selected>-- Seleccione una ficha --</option>
                            <?php if (!empty($fichas)): ?>
                                <?php foreach ($fichas as $f): ?>
                                    <option value="<?php echo htmlspecialchars($f['id_ficha']); ?>">
                                        Ficha <?php echo htmlspecialchars($f['numero_ficha']); ?> - <?php echo htmlspecialchars($f['nombre_programa'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay fichas registradas</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-cancel-modal input-radius-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action-custom btn-action-green">Guardar Aprendiz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcusas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-content-dark modal-content-rounded">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-file-earmark-arrow-up me-2 text-info"></i>Registrar Excusa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=guardar_excusa" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Documento del Aprendiz</label>
                        <input type="text" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="documento_aprendiz" placeholder="Número de documento" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Fecha de la Falta / Incapacidad</label>
                        <input type="date" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="fecha_falta" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Motivo / Observación</label>
                        <textarea class="form-control form-control-dark fs-6 text-white input-radius-custom" name="motivo" rows="3" placeholder="Describe brevemente el motivo..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-white">Adjuntar Evidencia (PDF o Imagen)</label>
                        <input type="file" class="form-control form-control-dark form-control-lg fs-6 text-white input-radius-custom" name="archivo_excusa" accept=".pdf, .jpg, .jpeg, .png" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-cancel-modal input-radius-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action-custom btn-action-green">Subir Excusa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-content-dark">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-person-badge-fill me-2 text-success"></i>Información del Administrador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="modal-avatar-container">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars((string)($nombreUsuario ?? 'Administrador')); ?></h4>
                <p class="text-light small mb-3"><?= htmlspecialchars((string)($rolUsuario ?? 'Administrador del Sistema')); ?></p>

                <div class="row text-center py-3 my-3 g-0 modal-stats-box">
                    <div class="col-6 border-end border-secondary">
                        <span class="d-block fw-bold fs-5 text-success"><?= (int)($totalAprendices ?? 0); ?></span>
                        <span class="text-white-50 small">Registrados</span>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-bold fs-5 text-success"><?= (int)($asistenciasHoy ?? 0); ?></span>
                        <span class="text-white-50 small">Activos Hoy</span>
                    </div>
                </div>

            </div>

            <a class="dropdown-item text-danger fw-bold text-center py-2 w-100 modal-logout-link"
                href="index.php?action=logout">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
            </a>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="public/js/admin.js"></script>
</body>

</html>