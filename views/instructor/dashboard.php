<?php

$fichas = $fichas ?? [];
$idFichaSeleccionada = $idFichaSeleccionada ?? 0;
$fechaSeleccionada = $fechaSeleccionada ?? date('Y-m-d');
$competenciaActiva = $competenciaActiva ?? 'ADSO - Desarrollo y Programación';
$aprendicesLista = $aprendicesLista ?? [];
$excusasPendientes = $excusasPendientes ?? [];
$nombreUsuario = $nombreUsuario ?? 'Instructor';
$rolUsuario = $rolUsuario ?? 'Instructor';

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

    <div class="dashboard-container" style="background-color: #0d1117;">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <nav class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100" style="width: 80px; position: fixed; top: 0; left: 0; z-index: 1000;">
                <?php $esEscaner = (isset($vistaActiva) && $vistaActiva === 'escaner'); ?>
                <a href="index.php?action=portal_instructor" class="nav-link text-white <?= !$esEscaner ? 'active bg-dark' : ''; ?> rounded d-flex flex-column align-items-center justify-content-center py-2 mb-2" title="Panel Instructor">
                    <i class="bi bi-person-video3 fs-5 mb-1 text-success"></i>
                    <span style="font-size: 9px; line-height: 1;">Inicio</span>
                </a>
                <a href="index.php?action=portal_instructor#formLlamadoLista" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Llamado a Lista">
                    <i class="bi bi-check2-square fs-5 mb-1 text-info"></i>
                    <span style="font-size: 9px; line-height: 1;">Lista</span>
                </a>
                <a href="index.php?action=escaner_rfid" class="nav-link text-white <?= $esEscaner ? 'active bg-dark' : ''; ?> d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Lector RFID">
                    <i class="bi bi-upc-scan fs-5 mb-1 text-primary"></i>
                    <span style="font-size: 9px; line-height: 1;">RFID</span>
                </a>
                <a href="#panel-excusas" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Excusas por Revisar">
                    <i class="bi bi-inbox-fill fs-5 mb-1 text-warning"></i>
                    <span style="font-size: 9px; line-height: 1;">Excusas</span>
                </a>
                <a href="index.php?action=calendario" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Google Calendario">
                    <i class="bi bi-calendar3 fs-5 mb-1 text-primary"></i>
                    <span style="font-size: 9px; line-height: 1;">Calendario</span>
                </a>
            </nav>
        </aside>

        <main class="main-content p-0" style="background-color: transparent; min-width: 0;">
            <nav class="navbar navbar-expand-lg navbar-instructor py-3 px-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center text-white fw-bold gap-2" href="#">
                <span class="badge rounded-circle p-2 sena-badge-instructor">
                    <i class="bi bi-person-video3 text-white fs-5"></i>
                </span>
                <span>Panel Instructor <span class="badge bg-success ms-1 small">SENA</span></span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="index.php?action=escaner_rfid" class="btn btn-outline-info btn-sm rounded-pill px-3">
                    <i class="bi bi-upc-scan me-1"></i> Lector RFID
                </a>
                <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver información del instructor">
                    <div class="header-avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="text-start pe-2 d-none d-sm-block">
                        <h6 class="fw-bold mb-0 text-white" style="font-size: 0.9rem;"><?= htmlspecialchars((string)($nombreUsuario ?? 'Instructor')); ?></h6>
                        <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars((string)($rolUsuario ?? 'Instructor')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">

        <?php if (isset($vistaActiva) && $vistaActiva === 'escaner'): ?>

            <div class="row justify-content-center mt-3">
                <div class="col-md-8">
                    <div class="card shadow-lg text-center p-5" style="background-color: #161b22; border: 1px solid #30363d; border-radius: 20px;">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="bi bi-credit-card-2-front fs-1 text-info"></i>
                            </div>
                            <h3 class="card-title text-white mb-3">Lector de Asistencia RFID</h3>
                            <p class="text-muted mb-4">Acerque la tarjeta o llavero RFID del aprendiz. El sistema procesará el registro al instante.</p>
                            <form id="formEscaner">
                                <div class="mb-3">
                                    <input type="text" id="codigo_rfid" name="codigo_rfid" class="form-control form-control-lg text-center" style="background-color: #0d1117; color: #ffffff; border-color: #30363d;" placeholder="Esperando lectura de RFID..." autofocus autocomplete="off" required>
                                </div>
                            </form>
                            <div id="respuestaAsistencia" class="mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="public/js/escaner.js"></script>

        <?php else: ?>

        <?php if (isset($_GET['guardado'])): ?>
            <div class="alert alert-success alert-success-custom alert-dismissible fade show py-2 px-3 small mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> ¡Llamado a lista y novedades guardadas exitosamente en la base de datos!
                <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excusa_actualizada'): ?>
            <div class="alert alert-info alert-dismissible fade show py-2 px-3 small mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> Estado de la excusa médica actualizado correctamente.
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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

                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Exportar / Imprimir
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
                                        <th style="width: 30%;" class="text-white">APRENDIZ</th>
                                        <th style="width: 35%; text-align: center;" class="text-white">MARCADO RÁPIDO</th>
                                        <th style="width: 15%; text-align: center;" class="text-white">ALERTA SENA</th>
                                        <th style="width: 20%;" class="text-white">NOVEDAD / OBSERVACIÓN</th>
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

                                                <!-- Alerta Visual en Rojo del Reglamento SENA (>= 15% inasistencias) -->
                                                <td class="text-center">
                                                    <?php if ($ap['alerta_sena']): ?>
                                                        <span class="badge bg-danger text-white py-1 px-2 rounded-pill shadow-sm" title="Supera el 15% de inasistencias permitido por el reglamento">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $ap['porcentaje_faltas']; ?>% Faltas (Alerta Deserción)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-dark border border-secondary text-muted rounded-pill">
                                                            <?= $ap['total_faltas']; ?> faltas (<?= $ap['porcentaje_faltas']; ?>%)
                                                        </span>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="bi bi-inbox text-warning me-2"></i>Excusas por Revisar
                        </h6>
                        <span class="badge bg-warning text-dark rounded-pill"><?= count($excusasPendientes); ?> Pendientes</span>
                    </div>

                    <p class="text-muted small">Excusas médicas subidas por aprendices que esperan aprobación:</p>

                    <div class="d-flex flex-column gap-3">
                        <?php if (!empty($excusasPendientes)): ?>
                            <?php foreach ($excusasPendientes as $exc): ?>
                                <div class="p-3 excusa-box-instructor">
                                    <div class="fw-semibold text-white small mb-1">
                                        <?= htmlspecialchars($exc['nombre'] . ' ' . $exc['apellido']); ?>
                                    </div>
                                    <small class="text-muted d-block mb-1">Doc: <?= htmlspecialchars($exc['identificacion']); ?></small>
                                    <small class="text-muted d-block mb-2">Falta del: <?= htmlspecialchars($exc['fecha_asistencia']); ?></small>
                                    
                                    <p class="text-white small mb-2 fst-italic">
                                        "<?= htmlspecialchars($exc['observacion']); ?>"
                                    </p>

                                    <div class="mb-2">
                                        <a href="public/uploads/excusas/<?= htmlspecialchars($exc['archivo']); ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Ver Soporte Médico
                                        </a>
                                    </div>

                                    <form action="index.php?action=procesar_excusa_instructor" method="POST" class="d-flex gap-2 mt-2">
                                        <input type="hidden" name="id_excusa" value="<?= $exc['id_excusa']; ?>">
                                        <button type="submit" name="estado" value="Aprobada" class="btn btn-sm btn-sena-action flex-fill rounded-pill py-1">
                                            <i class="bi bi-check"></i> Aprobar
                                        </button>
                                        <button type="submit" name="estado" value="Rechazada" class="btn btn-sm btn-danger flex-fill rounded-pill py-1">
                                            <i class="bi bi-x"></i> Rechazar
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-shield-check fs-2 d-block mb-2 text-success"></i>
                                No hay excusas pendientes de revisión.
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
                        <i class="bi bi-person-badge-fill me-2" style="color: #22c55e;"></i>Información del Instructor
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
                        <p class="mb-2 text-white-50 small fw-semibold"><i class="bi bi-calendar-event text-success me-2"></i>Fecha de Clase: <span class="text-white"><?= htmlspecialchars($fechaSeleccionada); ?></span></p>
                        <p class="mb-0 text-white-50 small fw-semibold"><i class="bi bi-shield-check text-success me-2"></i>Estado de Sesión: <span class="text-white">Activa y Segura</span></p>
                    </div>
                </div>

                <div class="p-3 border-top border-secondary d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning w-50 fw-semibold rounded-3 py-2" data-bs-toggle="modal" data-bs-target="#modalEditarInstructor">
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
                            <input type="text" class="form-control form-control-dark text-white" value="<?= htmlspecialchars((string)($rolUsuario ?? 'Instructor')); ?>" readonly disabled style="background-color: #161b22; color: #cbd5e1; -webkit-text-fill-color: #cbd5e1;">
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
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal" style="border-color: #64748b;">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Guardar Cambios</button>
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
