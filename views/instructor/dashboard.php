<?php

$fichas = $fichas ?? [];
$idFichaSeleccionada = $idFichaSeleccionada ?? 0;
$fechaSeleccionada = $fechaSeleccionada ?? date('Y-m-d');
$competenciaActiva = $competenciaActiva ?? 'ADSO - Desarrollo y Programación';
$aprendicesLista = $aprendicesLista ?? [];
$excusasPendientes = $excusasPendientes ?? [];
$nombreUsuario = $nombreUsuario ?? 'Instructor';
$rolUsuario = $rolUsuario ?? 'Instructor';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Instructor - Toma de Asistencia SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css">
    <link rel="stylesheet" href="public/css/instructor.css">
</head>

<body class="portal-instructor-body">

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
                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold text-white"><?= htmlspecialchars($nombreUsuario); ?></div>
                    <small class="text-muted"><?= htmlspecialchars($rolUsuario); ?></small>
                </div>
                <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">

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
                                <option value="<?= $f['id_ficha']; ?>" <?= ($f['id_ficha'] == $idFichaSeleccionada) ? 'selected' : ''; ?>>
                                    Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa']); ?> (<?= htmlspecialchars($f['jornada']); ?>)
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
                                    <tr class="text-muted small border-bottom border-secondary">
                                        <th style="width: 30%;">APRENDIZ</th>
                                        <th style="width: 35%; text-align: center;">MARCADO RÁPIDO</th>
                                        <th style="width: 15%; text-align: center;">ALERTA SENA</th>
                                        <th style="width: 20%;">OBSERVACIÓN</th>
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
                                                    <input type="text" name="observacion[<?= $id; ?>]" class="form-control form-control-sm form-control-dark" placeholder="Novedad opcional...">
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
                <div class="card instructor-card p-3 shadow-sm h-100">
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

    </div>

    <script src="public/js/instructor.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
