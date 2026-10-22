<?php

$fichas = $fichas ?? [];
$idFichaSeleccionada = $idFichaSeleccionada ?? 0;
$fechaSeleccionada = $fechaSeleccionada ?? date('Y-m-d');
$competenciaActiva = $competenciaActiva ?? 'ADSO - Desarrollo y Programación';
$aprendicesLista = $aprendicesLista ?? [];
$excusasPendientes = $excusasPendientes ?? [];
$historialExcusas = $historialExcusas ?? [];
$nombreUsuario = $nombreUsuario ?? 'Instructor';
$rolUsuario = $rolUsuario ?? 'Instructor';
$ultimosMarcajes = (isset($ultimosMarcajes) && is_array($ultimosMarcajes)) ? $ultimosMarcajes : [];
$totalAprendices = $totalAprendices ?? count($aprendicesLista);
$asistenciasHoy = $asistenciasHoy ?? 0;
$retardosHoy = $retardosHoy ?? 0;
$totalFichas = $totalFichas ?? count($fichas);

$cntInstPendientes = 0;
$cntInstAprobadas = 0;
$cntInstRechazadas = 0;
foreach ($historialExcusas as $eHist) {
    $st = $eHist['estado'] ?? 'Pendiente';
    if ($st === 'Pendiente') $cntInstPendientes++;
    elseif ($st === 'Aprobada') $cntInstAprobadas++;
    elseif ($st === 'Rechazada') $cntInstRechazadas++;
}

$fichaActualInfo = null;
foreach ($fichas as $f) {
    if ((int)$f['id_ficha'] === (int)$idFichaSeleccionada) {
        $fichaActualInfo = $f;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Instructor - Toma de Asistencia SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css?v=<?= file_exists('public/css/dashboard.css') ? filemtime('public/css/dashboard.css') : time(); ?>">
    <link rel="stylesheet" href="public/css/instructor.css?v=<?= file_exists('public/css/instructor.css') ? filemtime('public/css/instructor.css') : time(); ?>">
</head>

<body class="portal-instructor-body">

    <div class="dashboard-container portal-container-bg">
        <?php 
        $activo = (isset($vistaActiva) && $vistaActiva === 'escaner') ? 'escaner' : 'dashboard';
        $rol = 'Instructor';
        require_once __DIR__ . '/../partials/sidebar.php'; 
        ?>

        <main class="main-content p-0 portal-main-content">
            <nav class="navbar navbar-expand-lg navbar-instructor py-3 px-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center text-white fw-bold gap-2" href="#">
                <span class="badge rounded-circle p-2 sena-badge-instructor">
                    <i class="bi bi-person-video3 text-white fs-5"></i>
                </span>
                <span>Panel Instructor <span class="badge bg-success ms-1 small">SENA</span></span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="index.php?action=reporte_asistencia_pdf&ficha_id=<?= $idFichaSeleccionada; ?>&fecha=<?= urlencode($fechaSeleccionada); ?>&competencia=<?= urlencode($competenciaActiva); ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm" title="Generar Reporte Oficial en PDF">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Reporte PDF
                </a>
                <div class="dropdown">
                    <button class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Exportar Reportes Oficiales en Excel">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar Excel
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary dropdown-menu-custom">
                        <li>
                            <a class="dropdown-item py-2" href="index.php?action=reporte_asistencia_excel&tipo=sesion&ficha_id=<?= $idFichaSeleccionada; ?>&fecha=<?= urlencode($fechaSeleccionada); ?>">
                                <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> Planilla Sesión del Día
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="index.php?action=reporte_asistencia_excel&tipo=mensual&ficha_id=<?= $idFichaSeleccionada; ?>">
                                <i class="bi bi-calendar3-range text-info me-2"></i> Sábana Mensual Consolidada
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="index.php?action=escaner_rfid" class="btn btn-outline-info btn-sm rounded-pill px-3">
                    <i class="bi bi-upc-scan me-1"></i> Lector RFID
                </a>
                <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver información del instructor">
                    <div class="header-avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="text-start pe-2 d-none d-sm-block">
                        <h6 class="fw-bold mb-0 text-white user-name-title"><?= htmlspecialchars((string)($nombreUsuario ?? 'Instructor')); ?></h6>
                        <span class="text-muted user-doc-subtitle"><?= htmlspecialchars((string)($rolUsuario ?? 'Instructor')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <?php require_once __DIR__ . '/../partials/mensajes.php'; ?>

        <?php if (isset($vistaActiva) && $vistaActiva === 'escaner'): ?>

            <div class="row justify-content-center mt-3">
                <div class="col-md-8">
                    <div class="card shadow-lg text-center p-5 scanner-card-box">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="bi bi-credit-card-2-front fs-1 text-info"></i>
                            </div>
                            <h3 class="card-title text-white mb-3">Lector de Asistencia RFID</h3>
                            <p class="text-muted mb-4">Acerque la tarjeta o llavero RFID del aprendiz. El sistema procesará el registro al instante.</p>
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

        <!-- Encabezado de Bienvenida y Acciones Rápidas del Instructor -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1 text-white">¡Hola, <?= htmlspecialchars((string)($nombreUsuario ?? 'Instructor')); ?>!</h2>
                <p class="text-muted small mb-0">Resumen de asistencia y control diario de aprendices</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="#formLlamadoLista" class="btn text-white shadow-sm fw-semibold btn-action-green">
                    <i class="bi bi-check2-square me-1"></i> Llamar a Lista
                </a>
                <a href="index.php?action=escaner_rfid" class="btn btn-outline-info shadow-sm fw-semibold btn-radius-10">
                    <i class="bi bi-upc-scan me-1"></i> Lector RFID
                </a>
            </div>
        </div>

        <!-- Tarjetas de Métricas de Asistencia del Instructor (Portada Oficial) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="dashboard-card stat-card card-dark-panel shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3 p-3 rounded-circle d-flex align-items-center justify-content-center stat-icon-circle stat-icon-blue">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white"><?= $totalAprendices ?? count($aprendicesLista); ?></h3>
                            <span class="text-muted small fw-semibold">Aprendices</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="dashboard-card stat-card card-dark-panel shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3 p-3 rounded-circle d-flex align-items-center justify-content-center stat-icon-circle stat-icon-green">
                            <i class="bi bi-check-circle fs-5"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white"><?= $asistenciasHoy ?? 0; ?></h3>
                            <span class="text-muted small fw-semibold">Asistencias Hoy</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="dashboard-card stat-card card-dark-panel shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3 p-3 rounded-circle d-flex align-items-center justify-content-center stat-icon-circle stat-icon-emerald">
                            <i class="bi bi-journal-text fs-5"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white"><?= $totalFichas ?? count($fichas); ?></h3>
                            <span class="text-muted small fw-semibold">Número de Fichas</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="dashboard-card stat-card card-dark-panel shadow-sm h-100">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3 p-3 rounded-circle d-flex align-items-center justify-content-center stat-icon-circle stat-icon-yellow">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white"><?= $retardosHoy ?? 0; ?></h3>
                            <span class="text-muted small fw-semibold">Retardos Hoy</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <a href="#panel-excusas" class="text-decoration-none">
                    <div class="dashboard-card stat-card card-dark-panel shadow-sm h-100">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3 p-3 rounded-circle d-flex align-items-center justify-content-center stat-icon-circle stat-icon-red">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-white"><?= count($excusasPendientes); ?></h3>
                                <span class="text-muted small fw-semibold">Excusas Pend.</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tabla de Últimos Marcajes en Tiempo Real para el Instructor -->
        <div class="dashboard-card card-dark-panel mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-white">
                        <i class="bi bi-upc-scan text-success me-2"></i>Últimos Marcajes
                    </h5>
                    <small class="text-muted">Registro en tiempo real de aprendices al marcar entrada o salida</small>
                </div>
                <span class="badge bg-dark text-muted fw-normal px-3 py-2 border border-secondary rounded-pill badge-text-sm">Recientes</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>APRENDIZ</th>
                            <th>FECHA</th>
                            <th>ENTRADA</th>
                            <th>ESTADO</th>
                            <th>SALIDA</th>
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

                                $badgeStyle = ($estadoLower === 'a tiempo' || $estadoLower === 'presente' || $estadoLower === 'asistió')
                                    ? 'background: rgba(34, 197, 94, 0.15); color: #22c55e;'
                                    : (($estadoLower === 'retardo' || $estadoLower === 'tarde') ? 'background: rgba(234, 179, 8, 0.15); color: #eab308;' : 'background: rgba(239, 68, 68, 0.15); color: #ef4444;');
                                ?>
                                <tr>
                                    <td class="fw-semibold text-white">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle-sm avatar-recent-badge">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span><?= htmlspecialchars(trim($nombreReg . ' ' . $apellidoReg)); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($fechaReg); ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($entradaReg); ?></td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $badgeClass; ?>">
                                            <?= htmlspecialchars($estadoReg); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($salidaReg); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay registros de asistencias recientes en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card instructor-card p-3 mb-4 shadow-sm">
            <form action="index.php" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="portal_instructor">

                <div class="col-md-4">
                    <label class="form-label text-muted small fw-semibold">
                        <i class="bi bi-journal-text me-1 text-success"></i> Seleccionar Ficha de Formación:
                    </label>
                    <select name="ficha_id" class="form-select form-select-dark" onchange="this.form.submit()">
                        <?php if (!empty($fichas)): ?>
                            <?php foreach ($fichas as $f): ?>
                                <?php 
                                    $cantAp = isset($f['total_aprendices']) ? (int)$f['total_aprendices'] : 0;
                                    $tagAp = ($cantAp > 0) ? " ({$cantAp} aprendices)" : " (0 aprendices)";
                                ?>
                                <option value="<?= $f['id_ficha']; ?>" <?= ($f['id_ficha'] == $idFichaSeleccionada) ? 'selected' : ''; ?>>
                                    Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa'] ?: 'ADSO'); ?><?= $tagAp; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay fichas registradas</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small fw-semibold">
                        <i class="bi bi-bookmark-check me-1 text-success"></i> Competencia / Sesión:
                    </label>
                    <input type="text" name="competencia" class="form-control form-control-dark" value="<?= htmlspecialchars($competenciaActiva); ?>" placeholder="Ej. Programación de Software">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted small fw-semibold">
                        <i class="bi bi-calendar3 me-1 text-success"></i> Fecha de Clase:
                    </label>
                    <input type="date" name="fecha" class="form-control form-control-dark" value="<?= htmlspecialchars($fechaSeleccionada); ?>" onchange="this.form.submit()">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-success w-100 rounded-pill">
                        <i class="bi bi-arrow-repeat me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>

        <div class="row g-4">

            <div class="col-lg-9">
                <div class="card instructor-card p-3 shadow-sm">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary">
                        <div>
                            <h5 class="fw-bold text-white mb-0">
                                <i class="bi bi-check2-square text-success me-2"></i>Llamado a Lista de Aprendices
                            </h5>
                            <small class="text-muted">Total en la ficha: <?= count($aprendicesLista); ?> aprendices registrados</small>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="btnMarcarTodosPresente">
                                <i class="bi bi-check-all me-1"></i> Marcar todos a Presente
                            </button>
                        </div>
                    </div>

                    <form action="index.php?action=guardar_asistencia_instructor" method="POST" id="formLlamadoLista">
                        <input type="hidden" name="ficha_id" value="<?= $idFichaSeleccionada; ?>">
                        <input type="hidden" name="fecha" value="<?= htmlspecialchars($fechaSeleccionada); ?>">

                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0" id="tablaAsistencia">
                                <thead>
                                    <tr class="text-white-50 small border-bottom border-secondary">
                                        <th class="text-white th-w-30">APRENDIZ</th>
                                        <th class="text-white text-center th-w-35">MARCADO RÁPIDO</th>
                                        <th class="text-white text-center th-w-15">ALERTA SENA</th>
                                        <th class="text-white th-w-20">NOVEDAD / OBSERVACIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($aprendicesLista)): ?>
                                        <?php foreach ($aprendicesLista as $ap): ?>
                                            <?php 
                                                $id = $ap['id_aprendiz'];
                                                $estado = $ap['estado_actual'];
                                            ?>
                                            <tr class="border-bottom border-secondary">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle-sm">
                                                            <i class="bi bi-person-fill"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-white"><?= htmlspecialchars($ap['nombre'] . ' ' . $ap['apellido']); ?></div>
                                                            <small class="text-muted">Doc: <?= htmlspecialchars($ap['identificacion']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <div class="btn-group" role="group" aria-label="Marcado">
                                                        <input type="radio" class="btn-check radio-asistencia radio-presente" name="asistencia[<?= $id; ?>]" id="pres_<?= $id; ?>" value="Presente" <?= ($estado === 'Presente') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-sm btn-outline-success px-3" for="pres_<?= $id; ?>">
                                                            <i class="bi bi-check-lg"></i> Presente
                                                        </label>

                                                        <input type="radio" class="btn-check radio-asistencia" name="asistencia[<?= $id; ?>]" id="tard_<?= $id; ?>" value="Tarde" <?= ($estado === 'Tarde') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-sm btn-outline-warning px-3" for="tard_<?= $id; ?>">
                                                            <i class="bi bi-clock"></i> Tarde
                                                        </label>

                                                        <input type="radio" class="btn-check radio-asistencia" name="asistencia[<?= $id; ?>]" id="ause_<?= $id; ?>" value="Ausente" <?= ($estado === 'Ausente') ? 'checked' : ''; ?>>
                                                        <label class="btn btn-sm btn-outline-danger px-3" for="ause_<?= $id; ?>">
                                                            <i class="bi bi-x-lg"></i> Ausente
                                                        </label>
                                                    </div>
                                                </td>

                                                <!-- Alerta Visual en Rojo del Reglamento SENA (>= 15% inasistencias) y Control de Comité -->
                                                <td class="text-center">
                                                    <?php if ($ap['alerta_sena']): ?>
                                                        <div>
                                                            <span class="badge bg-danger text-white py-1 px-2 rounded-pill shadow-sm" title="Supera el 15% de inasistencias permitido por el reglamento">
                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $ap['porcentaje_faltas']; ?>% Faltas (Alerta Deserción)
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div>
                                                            <span class="badge bg-dark border border-secondary text-muted rounded-pill">
                                                                <?= $ap['total_faltas']; ?> faltas (<?= $ap['porcentaje_faltas']; ?>%)
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Control de Remisión a Comité -->
                                                    <?php if (!empty($ap['en_comite'])): ?>
                                                        <span class="badge bg-warning text-dark py-1 px-2 rounded-pill mt-1 d-inline-block shadow-sm" title="Caso remitido a Comité de Evaluación y Seguimiento">
                                                            <i class="bi bi-gavel me-1"></i> En Comité
                                                        </span>
                                                    <?php elseif ($ap['total_faltas'] >= 2 || $ap['alerta_sena']): ?>
                                                        <button type="button" 
                                                                class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0 mt-1 btn-remitir-comite text-alerta-box" 
                                                                
                                                                data-id="<?= $id; ?>" 
                                                                data-nombre="<?= htmlspecialchars($ap['nombre'] . ' ' . $ap['apellido']); ?>" 
                                                                data-doc="<?= htmlspecialchars($ap['identificacion']); ?>" 
                                                                data-faltas="<?= $ap['total_faltas']; ?>" 
                                                                data-porcentaje="<?= $ap['porcentaje_faltas']; ?>"
                                                                title="Remitir a Comité de Evaluación y Seguimiento">
                                                            <i class="bi bi-send-exclamation me-1"></i> Remitir a Comité
                                                        </button>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <input type="text" name="observacion[<?= $id; ?>]" class="form-control form-control-sm form-control-dark text-white input-novedad" placeholder="Novedad opcional...">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No hay aprendices registrados en esta ficha.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($aprendicesLista)): ?>
                            <div class="mt-4 pt-3 border-top border-secondary text-end">
                                <button type="submit" class="btn btn-sena-action px-4 py-2 fw-semibold rounded-pill shadow-sm">
                                    <i class="bi bi-save me-1"></i> Guardar Asistencia de la Sesión
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>

                </div>
            </div>

            <div class="col-lg-3">
                <div class="card instructor-card p-3 shadow-sm h-100" id="panel-excusas">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="bi bi-folder2-open text-warning me-2"></i>Historial de Excusas
                        </h6>
                        <span class="badge bg-warning text-dark rounded-pill"><?= count($excusasPendientes); ?> Pendientes</span>
                    </div>

                    <p class="text-muted small mb-2">Registro de justificaciones médicas radicadas por aprendices:</p>

                    <!-- Pestañas de Filtro Rápido -->
                    <div class="btn-group w-100 mb-3" role="group" id="filtroExcusasInstructor">
                        <button type="button" class="btn btn-sm btn-outline-secondary text-white active py-1 btn-pill-xs" data-filtro="todas">Todas (<?= count($historialExcusas); ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline-warning py-1 btn-pill-xs" data-filtro="pendiente">Pend. (<?= $cntInstPendientes; ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline-success py-1 btn-pill-xs" data-filtro="aprobada">Aprob. (<?= $cntInstAprobadas; ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 btn-pill-xs" data-filtro="rechazada">Rech. (<?= $cntInstRechazadas; ?>)</button>
                    </div>

                    <div class="d-flex flex-column gap-2 excusas-scroll-list" id="listaExcusasInstructor">
                        <?php if (!empty($historialExcusas)): ?>
                            <?php foreach ($historialExcusas as $exc): ?>
                                <?php 
                                    $est = $exc['estado'] ?? 'Pendiente';
                                    $badgeColor = 'bg-warning text-dark';
                                    $borderClass = 'border-left-pendiente';
                                    if ($est === 'Aprobada') {
                                        $badgeColor = 'bg-success text-white';
                                        $borderClass = 'border-left-aprobada';
                                    } elseif ($est === 'Rechazada') {
                                        $badgeColor = 'bg-danger text-white';
                                        $borderClass = 'border-left-rechazada';
                                    }
                                ?>
                                <div class="p-3 excusa-item-card rounded bg-dark border <?= $borderClass; ?>" data-estado="<?= strtolower($est); ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-semibold text-white small">
                                            <?= htmlspecialchars($exc['nombre'] . ' ' . $exc['apellido']); ?>
                                        </div>
                                        <span class="badge rounded-pill <?= $badgeColor; ?> px-2 py-1 badge-text-xs">
                                            <?= htmlspecialchars($est); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted text-xxs">
                                        <span>Doc: <?= htmlspecialchars($exc['identificacion']); ?></span>
                                        <span>Ficha: <?= htmlspecialchars($exc['numero_ficha'] ?? ''); ?></span>
                                    </div>
                                    <small class="text-white-50 d-block mb-1 text-xs">
                                        <i class="bi bi-calendar-x text-warning me-1"></i>Falta: <?= htmlspecialchars($exc['fecha_asistencia']); ?>
                                    </small>
                                    
                                    <p class="text-light small mb-2 fst-italic excusa-motivo-box">
                                        "<?= htmlspecialchars($exc['observacion']); ?>"
                                    </p>

                                    <?php
                                        $diasDiferencia = 0;
                                        if (!empty($exc['fecha_asistencia']) && !empty($exc['fecha_subida'])) {
                                            $diasDiferencia = (int)floor((strtotime($exc['fecha_subida']) - strtotime($exc['fecha_asistencia'])) / 86400);
                                        }
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2 btn-ver-soporte" 
                                                data-archivo="public/uploads/excusas/<?= htmlspecialchars($exc['archivo']); ?>"
                                                data-aprendiz="<?= htmlspecialchars($exc['nombre'] . ' ' . $exc['apellido']); ?>"
                                                data-fecha="<?= htmlspecialchars($exc['fecha_asistencia']); ?>"
                                                data-motivo="<?= htmlspecialchars($exc['observacion']); ?>" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2 btn-ver-soporte btn-pill-xs">
                                            <i class="bi bi-eye"></i> Ver Soporte
                                        </button>
                                        <div class="d-flex align-items-center gap-1">
                                            <small class="text-muted badge-text-xs">Radicado: <?= htmlspecialchars($exc['fecha_subida']); ?></small>
                                            <?php if ($diasDiferencia > 3): ?>
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger badge-xxs" title="Radicado <?= $diasDiferencia; ?> días después de la falta (Reglamento SENA: máx. 3 días hábiles)">
                                                    ⚠️ >3 días
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success badge-xxs" title="Radicado dentro del plazo reglamentario SENA">
                                                    ✓ En término
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($est === 'Pendiente'): ?>
                                        <form action="index.php?action=procesar_excusa_instructor" method="POST" class="d-flex gap-2 mt-2 pt-2 border-top border-secondary">
                                            <input type="hidden" name="id_excusa" value="<?= $exc['id_excusa']; ?>">
                                            <button type="submit" name="estado" value="Aprobada" class="btn btn-sm btn-success flex-fill rounded-pill py-1 fw-semibold btn-pill-xs">
                                                <i class="bi bi-check-lg"></i> Aprobar
                                            </button>
                                            <button type="submit" name="estado" value="Rechazada" class="btn btn-sm btn-outline-danger flex-fill rounded-pill py-1 fw-semibold btn-pill-xs">
                                                <i class="bi bi-x-lg"></i> Rechazar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-muted border-top border-secondary pt-1 mt-1 badge-text-xs">
                                            <i class="bi bi-shield-check text-info me-1"></i>Revisada el: <?= htmlspecialchars($exc['fecha_revision'] ?? '---'); ?>
                                            <?php if (!empty($exc['revisor_nombre'])): ?>
                                                por <?= htmlspecialchars($exc['revisor_nombre']); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-folder-check fs-2 d-block mb-2 text-success"></i>
                                No hay excusas registradas en el sistema.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
        </main>
    </div>

    <div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-person-badge-fill me-2 text-sena-green"></i>Información del Instructor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="modal-avatar-container">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars((string)($nombreUsuario ?? 'Instructor')); ?></h4>
                    <p class="text-light small mb-1"><?= htmlspecialchars((string)($rolUsuario ?? 'Instructor')); ?> SENA</p>
                    <p class="text-light small mb-3">
                        <?= $fichaActualInfo ? 'Ficha ' . htmlspecialchars($fichaActualInfo['numero_ficha']) . ' - ' . htmlspecialchars($fichaActualInfo['nombre_programa']) : 'Gestión de Asistencia Académica'; ?>
                    </p>

                    <div class="row text-center py-3 my-3 g-0 modal-stats-box">
                        <div class="col-6 border-end border-secondary">
                            <span class="d-block fw-bold fs-5 text-success"><?= count($aprendicesLista); ?></span>
                            <span class="text-white-50 small">Aprendices en Ficha</span>
                        </div>
                        <div class="col-6">
                            <span class="d-block fw-bold fs-5 text-warning"><?= count($excusasPendientes); ?></span>
                            <span class="text-white-50 small">Excusas por Revisar</span>
                        </div>
                    </div>

                    <div class="text-start px-2 mt-3">
                        <p class="mb-2 text-white-50 small fw-semibold"><i class="bi bi-person-workspace text-success me-2"></i>Sesión Actual: <span class="text-white"><?= htmlspecialchars($competenciaActiva); ?></span></p>
                        <p class="mb-0 text-white-50 small fw-semibold"><i class="bi bi-calendar-event text-success me-2"></i>Fecha de Clase: <span class="text-white"><?= htmlspecialchars($fechaSeleccionada); ?></span></p>
                    </div>
                </div>

                <div class="p-3 border-top border-secondary d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning w-50 fw-semibold rounded-3 py-2" data-bs-toggle="modal" data-bs-target="#modalEditarInstructor">
                        <i class="bi bi-pencil-square me-1"></i> Editar Perfil
                    </button>
                    <a class="btn btn-outline-danger w-50 fw-semibold rounded-3 py-2 d-flex align-items-center justify-content-center modal-avatar-red"
                        href="index.php?action=logout">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarInstructor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Editar Datos del Instructor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=portal_instructor" method="POST">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Nombre Completo</label>
                            <input type="text" class="form-control form-control-dark text-white" name="nombre" value="<?= htmlspecialchars((string)($nombreUsuario ?? 'Instructor')); ?>" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Rol del Sistema</label>
                            <input type="text" class="form-control form-control-dark form-control-readonly-dark text-white" value="<?= htmlspecialchars((string)($rolUsuario ?? 'Instructor')); ?>" readonly disabled>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Competencia Predeterminada</label>
                            <input type="text" class="form-control form-control-dark text-white" name="competencia" value="<?= htmlspecialchars($competenciaActiva); ?>" placeholder="Ej. Programación de Software">
                        </div>
                        <div class="p-3 rounded bg-dark border border-secondary text-light small text-start">
                            <i class="bi bi-info-circle text-info me-1"></i> Puedes actualizar tus datos de sesión y configuración pedagógica.
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Visor Integrado de Soportes Médicos (PDF / Imagen) -->
    <div class="modal fade" id="modalVisorSoporte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-panel">
                <div class="modal-header border-0 pb-2">
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="visorTituloAprendiz">
                            <i class="bi bi-file-earmark-medical text-info me-2"></i>Evidencia de Justificación Médica
                        </h5>
                        <small class="text-muted" id="visorSubtitulo">Detalle de la falta y radicación</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center p-3 visor-modal-body">
                    <div id="visorContenedorPdf" class="w-100 h-100 d-none min-vh-500">
                        <iframe id="visorIframePdf" src="" class="visor-iframe-box"></iframe>
                    </div>
                    <div id="visorContenedorImg" class="w-100 text-center d-none">
                        <img id="visorElementoImg" src="" alt="Evidencia Médica" class="img-fluid rounded shadow-sm visor-img-box">
                    </div>
                    <div id="visorCargando" class="text-muted">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="mt-2 small">Cargando documento...</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2 d-flex justify-content-between">
                    <small class="text-white-50 fst-italic" id="visorObservacionMotivo"></small>
                    <div class="d-flex gap-2">
                        <a id="visorEnlaceExterno" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-white">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en otra pestaña
                        </a>
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Remisión a Comité de Evaluación y Seguimiento -->
    <div class="modal fade" id="modalRemitirComite" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-panel">
                <form action="index.php?action=remitir_comite" method="POST">
                    <input type="hidden" name="id_aprendiz" id="comiteIdAprendiz">
                    <input type="hidden" name="total_faltas" id="comiteTotalFaltas">
                    <input type="hidden" name="porcentaje_faltas" id="comitePorcentajeFaltas">

                    <div class="modal-header border-bottom border-secondary pb-3">
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">
                                <i class="bi bi-gavel text-warning me-2"></i>Remitir a Comité de Evaluación
                            </h5>
                            <small class="text-muted">Proceso formal según Reglamento del Aprendiz SENA</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-4 text-start">
                        <div class="p-3 mb-3 rounded bg-dark border border-secondary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-white fw-bold" id="comiteNombreAprendiz">Aprendiz</span>
                                <span class="badge bg-danger rounded-pill" id="comiteBadgeFaltas">0 faltas</span>
                            </div>
                            <div class="text-muted small">
                                Documento: <span class="text-white" id="comiteDocAprendiz">---</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-semibold">
                                <i class="bi bi-chat-left-text me-1 text-warning"></i> Motivo / Justificación de la Remisión:
                            </label>
                            <textarea name="motivo" id="comiteMotivo" class="form-control form-control-dark text-white" rows="4" required placeholder="Describa el motivo y las evidencias de las inasistencias injustificadas..."></textarea>
                            <small class="text-muted">Esta solicitud será enviada de inmediato a la Coordinación / Administración para citación a comité.</small>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold text-white shadow-sm">
                            <i class="bi bi-send-exclamation me-1"></i> Remitir y Notificar a Administración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="public/js/instructor.js"></script>
</body>

</html>
