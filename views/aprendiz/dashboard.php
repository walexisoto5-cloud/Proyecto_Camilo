<?php
$datosAprendiz = $datosAprendiz ?? [
    'nombre' => 'Aprendiz',
    'apellido' => 'SENA',
    'identificacion' => '---',
    'numero_ficha' => '---',
    'nombre_programa' => 'ADSO',
    'jornada' => 'Diurna'
];
$porcentajeAsistencia = $porcentajeAsistencia ?? 100;
$totalAsistencias = $totalAsistencias ?? 0;
$totalRetardos = $totalRetardos ?? 0;
$totalFaltas = $totalFaltas ?? 0;
$asistenciaHoy = $asistenciaHoy ?? null;
$historial = $historial ?? [];
$misExcusas = $misExcusas ?? [];
$mensajeError = $mensajeError ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Aprendiz - Control de Asistencia SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css">
    <link rel="stylesheet" href="public/css/aprendiz.css">
</head>

<body class="portal-aprendiz-body">

    <div class="dashboard-container" style="background-color: #0d1117;">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <nav class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100" style="width: 80px; position: fixed; top: 0; left: 0; z-index: 1000;">
                <a href="index.php?action=portal_aprendiz" class="nav-link text-white active bg-dark rounded d-flex flex-column align-items-center justify-content-center py-2 mb-2" title="Inicio / Mi Portal">
                    <i class="bi bi-house-door-fill fs-5 mb-1 text-success"></i>
                    <span style="font-size: 9px; line-height: 1;">Inicio</span>
                </a>
                <a href="#modalSubirExcusa" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#modalSubirExcusa" title="Radicar Excusa">
                    <i class="bi bi-file-earmark-medical-fill fs-5 mb-1 text-info"></i>
                    <span style="font-size: 9px; line-height: 1;">Excusa</span>
                </a>
                <a href="#seccion-historial" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Historial de Asistencias">
                    <i class="bi bi-clock-history fs-5 mb-1 text-warning"></i>
                    <span style="font-size: 9px; line-height: 1;">Historial</span>
                </a>
                <a href="#seccion-excusas" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Mis Excusas Médicas">
                    <i class="bi bi-folder-check fs-5 mb-1 text-primary"></i>
                    <span style="font-size: 9px; line-height: 1;">Mis Excusas</span>
                </a>
            </nav>
        </aside>

        <main class="main-content p-0" style="background-color: transparent; min-width: 0;">
            <nav class="navbar navbar-expand-lg navbar-aprendiz py-3 px-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center text-white fw-bold gap-2" href="#">
                <span class="badge rounded-circle p-2 sena-badge-icon">
                    <i class="bi bi-person-badge text-white fs-5"></i>
                </span>
                <span>Portal Aprendiz <span class="badge bg-success ms-1 small">SENA</span></span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver información del aprendiz">
                    <div class="header-avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="text-start pe-2 d-none d-sm-block">
                        <h6 class="fw-bold mb-0 text-white" style="font-size: 0.9rem;"><?= htmlspecialchars($datosAprendiz['nombre'] . ' ' . $datosAprendiz['apellido']); ?></h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Doc: <?= htmlspecialchars($datosAprendiz['identificacion']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <?php if (!empty($mensajeError)): ?>
            <div class="alert alert-warning alert-dismissible fade show py-2 px-3 small mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($mensajeError); ?>
                <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card portal-card p-4 mb-4 shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1 text-white">¡Hola, <?= htmlspecialchars($datosAprendiz['nombre']); ?>!</h3>
                    <p class="text-muted mb-0">Bienvenido a tu panel de seguimiento y autogestión de asistencia.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-dark border border-secondary text-white py-2 px-3 rounded-pill">
                        <i class="bi bi-journal-bookmark me-1 text-success"></i> Ficha: <?= htmlspecialchars($datosAprendiz['numero_ficha'] ?? 'Sin asignar'); ?>
                    </span>
                    <span class="badge bg-dark border border-secondary text-white py-2 px-3 rounded-pill">
                        <i class="bi bi-mortarboard me-1 text-success"></i> <?= htmlspecialchars($datosAprendiz['nombre_programa'] ?? 'ADSO'); ?>
                    </span>
                    <span class="badge bg-dark border border-secondary text-white py-2 px-3 rounded-pill">
                        <i class="bi bi-clock me-1 text-success"></i> <?= htmlspecialchars($datosAprendiz['jornada'] ?? 'Diurna'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="card portal-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Rendimiento General</span>
                        <i class="bi bi-speedometer2 text-success fs-5"></i>
                    </div>
                    <h2 class="fw-bold text-white mb-2"><?= $porcentajeAsistencia; ?>%</h2>

                    <?php 
                        $barraClase = 'bg-success';
                        if ($porcentajeAsistencia < 85) $barraClase = 'bg-warning';
                        if ($porcentajeAsistencia < 75) $barraClase = 'bg-danger';
                    ?>
                    <div class="progress progress-dark">
                        <div class="progress-bar <?= $barraClase; ?>" role="progressbar" style="width: <?= $porcentajeAsistencia; ?>%;"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= ($porcentajeAsistencia >= 85) ? 'Buen nivel de asistencia' : '¡Atención a tus inasistencias!'; ?>
                    </small>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card portal-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Asistencias</span>
                        <div class="p-2 rounded stat-icon-asistio">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?= $totalAsistencias; ?></h2>
                    <small class="text-muted">Días cumplidos a tiempo</small>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card portal-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Tardanzas</span>
                        <div class="p-2 rounded stat-icon-tarde">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?= $totalRetardos; ?></h2>
                    <small class="text-muted">Llegadas con retardo</small>
                </div>
            </div>

            
            <div class="col-12 col-md-3">
                <div class="card portal-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Inasistencias</span>
                        <div class="p-2 rounded stat-icon-falta">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?= $totalFaltas; ?></h2>
                    <small class="text-muted">Faltas acumuladas</small>
                </div>
            </div>

        </div>

        
        <div class="card portal-card p-4 mb-4 shadow-sm text-center">
            <div class="row align-items-center">
                <div class="col-md-7 text-md-start mb-3 mb-md-0">
                    <h5 class="fw-bold text-white mb-1">
                        <i class="bi bi-calendar2-check text-success me-2"></i>Asistencia del Día: <?= date('d/m/Y'); ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <?php if (!$asistenciaHoy): ?>
                            Aún no has registrado tu entrada de hoy. Puedes marcarla con el botón si te encuentras en formación.
                        <?php elseif (empty($asistenciaHoy['salida']) || $asistenciaHoy['salida'] === '0000-00-00 00:00:00'): ?>
                            Entrada registrada a las <?= date('h:i A', strtotime($asistenciaHoy['entrada'])); ?>. No olvides marcar tu salida al finalizar.
                        <?php else: ?>
                            ¡Jornada completada! Entrada: <?= date('h:i A', strtotime($asistenciaHoy['entrada'])); ?> | Salida: <?= date('h:i A', strtotime($asistenciaHoy['salida'])); ?>.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-5 text-md-end">
                    <?php if (!$asistenciaHoy): ?>
                        <a href="index.php?action=marcar_asistencia_aprendiz" class="btn btn-sena-green px-4 py-2 fw-semibold shadow-sm rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Marcar Entrada de Hoy
                        </a>
                    <?php elseif (empty($asistenciaHoy['salida']) || $asistenciaHoy['salida'] === '0000-00-00 00:00:00'): ?>
                        <a href="index.php?action=marcar_asistencia_aprendiz" class="btn btn-warning px-4 py-2 fw-semibold shadow-sm rounded-pill text-dark">
                            <i class="bi bi-box-arrow-right me-1"></i> Marcar Salida de Hoy
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-success px-4 py-2 fw-semibold rounded-pill" disabled>
                            <i class="bi bi-check2-all me-1"></i> Asistencia de Hoy Completada
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card portal-card p-3 shadow-sm h-100" id="seccion-historial">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-white mb-0">
                            <i class="bi bi-clock-history text-success me-2"></i>Historial de Asistencias
                        </h5>
                        <span class="badge bg-dark border border-secondary text-muted"><?= count($historial); ?> Registros</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom border-secondary">
                                    <th>FECHA</th>
                                    <th>PROGRAMA / COMPETENCIA</th>
                                    <th>INSTRUCTOR</th>
                                    <th>HORARIO</th>
                                    <th>ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($historial)): ?>
                                    <?php foreach ($historial as $fila): ?>
                                        <?php 
                                            $estado = $fila['estado_entrada'] ?? 'Presente';
                                            $badgeClase = 'badge-asistio';
                                            if (stripos($estado, 'retardo') !== false || stripos($estado, 'tarde') !== false) {
                                                $badgeClase = 'badge-tarde';
                                            } else if (stripos($estado, 'falta') !== false || stripos($estado, 'ausente') !== false || stripos($estado, 'inasistencia') !== false) {
                                                $badgeClase = 'badge-falta';
                                            }
                                        ?>
                                        <tr class="border-bottom border-secondary">
                                            <td class="fw-semibold text-white"><?= htmlspecialchars($fila['fecha_asistencia']); ?></td>
                                            <td><small class="text-white"><?= htmlspecialchars($fila['nombre_programa'] ?? 'ADSO'); ?></small></td>
                                            <td><small class="text-muted"><?= htmlspecialchars(trim(($fila['nombre_instructor'] ?? 'Instructor') . ' ' . ($fila['apellido_instructor'] ?? ''))); ?></small></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= !empty($fila['entrada']) ? date('h:i A', strtotime($fila['entrada'])) : '--:--'; ?>
                                                    -
                                                    <?= !empty($fila['salida']) ? date('h:i A', strtotime($fila['salida'])) : '--:--'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-3 py-1 <?= $badgeClase; ?>">
                                                    <?= htmlspecialchars($estado); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Aún no tienes registros de asistencia en el sistema.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card portal-card p-3 shadow-sm h-100" id="seccion-excusas">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-white mb-0">
                            <i class="bi bi-file-medical text-success me-2"></i>Excusas Médicas
                        </h5>
                        <button class="btn btn-sm btn-sena-green rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSubirExcusa">
                            <i class="bi bi-plus-lg me-1"></i> Radicar
                        </button>
                    </div>

                    <p class="text-muted small">Consulta el estado de las justificaciones que has subido:</p>

                    <div class="d-flex flex-column gap-2">
                        <?php if (!empty($misExcusas)): ?>
                            <?php foreach ($misExcusas as $exc): ?>
                                <?php 
                                    $estExcusa = $exc['estado'] ?? 'Pendiente';
                                    $badgeExcusa = 'bg-warning text-dark';
                                    if ($estExcusa === 'Aprobada') $badgeExcusa = 'bg-success text-white';
                                    if ($estExcusa === 'Rechazada') $badgeExcusa = 'bg-danger text-white';
                                ?>
                                <div class="p-3 excusa-card-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold text-white small">Falta del: <?= htmlspecialchars($exc['fecha_asistencia']); ?></span>
                                        <span class="badge rounded-pill <?= $badgeExcusa; ?> small"><?= htmlspecialchars($estExcusa); ?></span>
                                    </div>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($exc['observacion']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted" style="font-size: 0.75rem;">Radicado: <?= htmlspecialchars($exc['fecha_subida']); ?></small>
                                        <a href="public/uploads/excusas/<?= htmlspecialchars($exc['archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Ver adjunto
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-folder-check fs-2 d-block mb-2 text-muted"></i>
                                No tienes excusas radicadas actualmente.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

    </div>
        </main>
    </div>

    <div class="modal fade" id="modalSubirExcusa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark shadow-lg border-0">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-file-earmark-medical me-2 text-success"></i>Radicar Excusa Médica
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=guardar_excusa" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4 text-start">
                        <input type="hidden" name="documento_aprendiz" value="<?= htmlspecialchars($datosAprendiz['identificacion'] ?? ''); ?>">

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Fecha de la Falta</label>
                            <input type="date" class="form-control input-dark" name="fecha_falta" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Motivo / Explicación de la falta</label>
                            <textarea class="form-control input-dark" name="motivo" rows="3" placeholder="Ej. Incapacidad médica EPS, cita médica..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Adjuntar Evidencia (PDF o Imagen)</label>
                            <input type="file" class="form-control input-dark" name="archivo_excusa" accept=".pdf, .jpg, .jpeg, .png" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sena-green rounded-pill px-4">Enviar Excusa</button>
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
                        <i class="bi bi-person-badge-fill me-2" style="color: #22c55e;"></i>Información del Aprendiz
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="modal-avatar-container">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($datosAprendiz['nombre'] . ' ' . $datosAprendiz['apellido']); ?></h4>
                    <p class="text-muted small mb-1">Doc: <?= htmlspecialchars($datosAprendiz['identificacion']); ?></p>
                    <p class="text-muted small mb-3">Ficha <?= htmlspecialchars($datosAprendiz['numero_ficha'] ?? 'Sin asignar'); ?> - <?= htmlspecialchars($datosAprendiz['nombre_programa'] ?? 'ADSO'); ?> (<?= htmlspecialchars($datosAprendiz['jornada'] ?? 'Diurna'); ?>)</p>

                    <div class="row text-center py-3 my-3 g-0 modal-stats-box">
                        <div class="col-6 border-end border-secondary">
                            <span class="d-block fw-bold fs-5 text-success"><?= $totalAsistencias; ?></span>
                            <span class="text-muted small">Asistencias</span>
                        </div>
                        <div class="col-6">
                            <span class="d-block fw-bold fs-5 text-danger"><?= $totalFaltas; ?></span>
                            <span class="text-muted small">Inasistencias</span>
                        </div>
                    </div>

                    <div class="text-start px-2 mt-3">
                        <p class="mb-2 text-muted small fw-semibold"><i class="bi bi-shield-check text-success me-2"></i>Estado de Sesión: <span class="text-white">Activa y Segura</span></p>
                        <p class="mb-0 text-muted small fw-semibold"><i class="bi bi-check2-circle text-success me-2"></i>Porcentaje Asistencia: <span class="text-white"><?= $porcentajeAsistencia; ?>%</span></p>
                    </div>
                </div>

                <div class="p-3 border-top border-secondary d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning w-50 fw-semibold rounded-3 py-2" data-bs-toggle="modal" data-bs-target="#modalEditarAprendiz">
                        <i class="bi bi-pencil-square me-1"></i> Editar Perfil
                    </button>
                    <a class="btn btn-outline-danger w-50 fw-semibold rounded-3 py-2 d-flex align-items-center justify-content-center"
                        style="background-color: rgba(220, 53, 69, 0.1);"
                        href="index.php?action=logout">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarAprendiz" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Editar Datos del Aprendiz
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=portal_aprendiz" method="POST">
                    <div class="modal-body py-4">
                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label text-muted small fw-semibold">Nombres</label>
                                <input type="text" class="form-control form-control-dark" name="nombre" value="<?= htmlspecialchars($datosAprendiz['nombre']); ?>" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label text-muted small fw-semibold">Apellidos</label>
                                <input type="text" class="form-control form-control-dark" name="apellido" value="<?= htmlspecialchars($datosAprendiz['apellido']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-muted small fw-semibold">Documento de Identidad</label>
                            <input type="text" class="form-control form-control-dark" value="<?= htmlspecialchars($datosAprendiz['identificacion']); ?>" readonly disabled>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-muted small fw-semibold">Ficha de Formación</label>
                            <input type="text" class="form-control form-control-dark" value="Ficha <?= htmlspecialchars($datosAprendiz['numero_ficha'] ?? 'Sin asignar'); ?> - <?= htmlspecialchars($datosAprendiz['nombre_programa'] ?? 'ADSO'); ?> (<?= htmlspecialchars($datosAprendiz['jornada'] ?? 'Diurna'); ?>)" readonly disabled>
                        </div>
                        <div class="p-3 rounded bg-dark border border-secondary text-muted small text-start">
                            <i class="bi bi-info-circle text-info me-1"></i> La ficha y documento son gestionados por el instructor o administración.
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="public/js/aprendiz.js"></script>
</body>

</html>
