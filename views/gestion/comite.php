<?php
// Variables disponibles: $casosComite, $totalCasos, $pendientesCount, $citadosCount, $finalizadosCount, $filtroEstado
$casosComite = $casosComite ?? [];
$activo = 'gestion_comite';
$rol = $_SESSION['rol'] ?? 'Administrador';
$filtroEstado = $filtroEstado ?? '';

$totalCasos = count($casosComite);
$pendientesCount = 0;
$citadosCount = 0;
$finalizadosCount = 0;

foreach ($casosComite as $c) {
    $st = strtolower($c['estado'] ?? '');
    if ($st === 'pendiente') $pendientesCount++;
    else if ($st === 'citado' || strpos($st, 'proceso') !== false) $citadosCount++;
    else if ($st === 'finalizado' || $st === 'resuelto') $finalizadosCount++;
}

function renderBadgeComite(string $estado): string
{
    switch ($estado) {
        case 'Pendiente':
            return '<span class="badge rounded-pill badge-comite-warning px-3 py-2"><i class="bi bi-clock me-1"></i>Pendiente Citación</span>';
        case 'Citado':
            return '<span class="badge rounded-pill bg-info-subtle text-info px-3 py-2"><i class="bi bi-calendar-event me-1"></i>Citado a Comité</span>';
        case 'Finalizado':
            return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2"><i class="bi bi-check-circle me-1"></i>Finalizado / Resuelto</span>';
        case 'Archivado':
            return '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2"><i class="bi bi-archive me-1"></i>Archivado</span>';
        default:
            return '<span class="badge rounded-pill bg-dark border border-secondary text-white px-3 py-2">' . htmlspecialchars($estado) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comité de Evaluación y Seguimiento - Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css?v=<?= file_exists('public/css/dashboard.css') ? filemtime('public/css/dashboard.css') : time(); ?>">
</head>

<body class="bg-dark-app">

    <div class="dashboard-container dashboard-container-dark">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content main-content-dark">
            <?php require_once __DIR__ . '/../partials/mensajes.php'; ?>

            <!-- Barra Superior Institucional -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom border-secondary">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle bg-dark border border-secondary text-warning">
                        <i class="bi bi-gavel fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Comité de Evaluación y Seguimiento</h4>
                        <small class="text-muted">Control y seguimiento de aprendices remitidos por inasistencias y deserción</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver perfil del administrador">
                        <div class="header-avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="fw-bold mb-0 text-white profile-title"><?= htmlspecialchars((string)($nombreUsuario ?? ($_SESSION['nombre_completo'] ?? 'Administrador'))); ?></h6>
                            <span class="text-muted profile-subtitle"><?= htmlspecialchars((string)($rolUsuario ?? ($_SESSION['rol'] ?? 'Administrador'))); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicadores Rápidos -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-dark-panel p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold">Total Remisiones</span>
                                <h3 class="fw-bold text-white mb-0 mt-1"><?= $totalCasos; ?></h3>
                            </div>
                            <div class="p-3 rounded-3 avatar-icon-blue">
                                <i class="bi bi-folder-symlink fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-dark-panel p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold">Pendientes por Citar</span>
                                <h3 class="fw-bold text-warning mb-0 mt-1"><?= $pendientesCount; ?></h3>
                            </div>
                            <div class="p-3 rounded-3 avatar-icon-orange">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-dark-panel p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold">Citados a Comité</span>
                                <h3 class="fw-bold text-info mb-0 mt-1"><?= $citadosCount; ?></h3>
                            </div>
                            <div class="p-3 rounded-3 avatar-icon-indigo">
                                <i class="bi bi-calendar2-week fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-dark-panel p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold">Casos Resueltos</span>
                                <h3 class="fw-bold text-success mb-0 mt-1"><?= $finalizadosCount; ?></h3>
                            </div>
                            <div class="p-3 rounded-3 avatar-icon-green">
                                <i class="bi bi-check2-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros de Estado -->
            <div class="d-flex gap-2 flex-wrap mb-3">
                <a href="index.php?action=gestion_comite" class="btn btn-sm <?= empty($filtroEstado) ? 'btn-success' : 'btn-outline-secondary text-white'; ?> rounded-pill px-3">
                    Todos (<?= $totalCasos; ?>)
                </a>
                <a href="index.php?action=gestion_comite&estado=Pendiente" class="btn btn-sm <?= ($filtroEstado === 'Pendiente') ? 'btn-warning text-dark' : 'btn-outline-secondary text-white'; ?> rounded-pill px-3">
                    Pendientes (<?= $pendientesCount; ?>)
                </a>
                <a href="index.php?action=gestion_comite&estado=Citado" class="btn btn-sm <?= ($filtroEstado === 'Citado') ? 'btn-info text-dark' : 'btn-outline-secondary text-white'; ?> rounded-pill px-3">
                    Citados (<?= $citadosCount; ?>)
                </a>
                <a href="index.php?action=gestion_comite&estado=Finalizado" class="btn btn-sm <?= ($filtroEstado === 'Finalizado') ? 'btn-success' : 'btn-outline-secondary text-white'; ?> rounded-pill px-3">
                    Finalizados (<?= $finalizadosCount; ?>)
                </a>
            </div>

            <!-- Tabla de Casos Remitidos a Comité -->
            <div class="card card-dark-panel p-3 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-text-sm">
                        <thead>
                            <tr class="text-white-50 small text-uppercase border-bottom border-secondary">
                                <th>Aprendiz</th>
                                <th>Ficha / Programa</th>
                                <th class="text-center">Faltas Registradas</th>
                                <th>Remitido Por</th>
                                <th>Fecha Remisión</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($casosComite)): ?>
                                <?php foreach ($casosComite as $caso): ?>
                                    <tr class="border-bottom border-secondary">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle-sm avatar-icon-orange">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-white">
                                                        <?= htmlspecialchars(($caso['aprendiz_nombre'] ?? '') . ' ' . ($caso['aprendiz_apellido'] ?? '')); ?>
                                                    </div>
                                                    <small class="text-muted">Doc: <?= htmlspecialchars($caso['aprendiz_doc'] ?? '---'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-white">
                                                Ficha <?= htmlspecialchars($caso['numero_ficha'] ?? 'N/A'); ?>
                                            </span>
                                            <div class="small text-muted mt-1 text-truncate col-max-text-sm" title="<?= htmlspecialchars($caso['nombre_programa'] ?? ''); ?>">
                                                <?= htmlspecialchars($caso['nombre_programa'] ?? 'ADSO'); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill px-2 py-1">
                                                <?= (int)$caso['total_faltas']; ?> inasistencias
                                            </span>
                                            <small class="text-muted d-block mt-1">
                                                <?= number_format((float)$caso['porcentaje_fallas'], 1); ?>% de fallas
                                            </small>
                                        </td>
                                        <td>
                                            <span class="text-white">
                                                <?= htmlspecialchars(($caso['instructor_nombre'] ?? '') . ' ' . ($caso['instructor_apellido'] ?? '')); ?>
                                            </span>
                                            <small class="text-muted d-block">Instructor</small>
                                        </td>
                                        <td>
                                            <span class="text-white">
                                                <?= date('d/m/Y', strtotime($caso['fecha_remision'])); ?>
                                            </span>
                                            <small class="text-muted d-block">
                                                <?= date('H:i A', strtotime($caso['fecha_remision'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= renderBadgeComite($caso['estado'] ?? 'Pendiente'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning rounded-pill px-3 btn-gestionar-caso"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalGestionarComite"
                                                    data-id="<?= $caso['id_comite']; ?>"
                                                    data-aprendiz="<?= htmlspecialchars(($caso['aprendiz_nombre'] ?? '') . ' ' . ($caso['aprendiz_apellido'] ?? '')); ?>"
                                                    data-doc="<?= htmlspecialchars($caso['aprendiz_doc'] ?? ''); ?>"
                                                    data-ficha="<?= htmlspecialchars($caso['numero_ficha'] ?? ''); ?>"
                                                    data-faltas="<?= $caso['total_faltas']; ?>"
                                                    data-motivo="<?= htmlspecialchars($caso['motivo'] ?? ''); ?>"
                                                    data-estado="<?= htmlspecialchars($caso['estado'] ?? 'Pendiente'); ?>"
                                                    data-obs="<?= htmlspecialchars($caso['observacion_admin'] ?? ''); ?>"
                                                    title="Gestionar estado del caso">
                                                <i class="bi bi-pencil-square me-1"></i> Gestionar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        No se registran casos de remisión a comité bajo el criterio seleccionado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Gestionar Caso de Comité -->
    <div class="modal fade" id="modalGestionarComite" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg card-dark-modal">
                <form action="index.php?action=actualizar_estado_comite" method="POST">
                    <input type="hidden" name="id_comite" id="gestionarIdComite">

                    <div class="modal-header border-bottom border-secondary pb-3">
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">
                                <i class="bi bi-gavel text-warning me-2"></i>Gestión de Caso de Comité
                            </h5>
                            <small class="text-muted">Resolución y seguimiento administrativo</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-4 text-start">
                        <div class="p-3 mb-3 rounded bg-dark border border-secondary">
                            <h6 class="fw-bold text-white mb-1" id="gestionarNombreAprendiz">Aprendiz</h6>
                            <small class="text-muted d-block mb-2">Doc: <span id="gestionarDocAprendiz" class="text-white">---</span> | Ficha: <span id="gestionarFichaAprendiz" class="text-white">---</span></small>
                            <div class="small text-white-50">
                                <strong>Motivo remitido por Instructor:</strong>
                                <p class="mb-0 mt-1 fst-italic text-warning-subtle" id="gestionarMotivoAprendiz">---</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-semibold">
                                <i class="bi bi-arrow-repeat me-1 text-success"></i> Nuevo Estado del Caso:
                            </label>
                            <select name="nuevo_estado" id="gestionarEstado" class="form-select form-select-dark" required>
                                <option value="Citado">Citado a Comité (Notificación enviada)</option>
                                <option value="Finalizado">Finalizado / Resuelto (Acta de Comité suscrita)</option>
                                <option value="Archivado">Archivado / Desestimado</option>
                                <option value="Pendiente">Pendiente (Esperando agendamiento)</option>
                            </select>
                            <div class="form-text text-white-50 small mt-1">
                                <i class="bi bi-info-circle text-info me-1"></i>
                                Al actualizar a <strong>Citado</strong> o <strong>Finalizado</strong>, el caso se considerará gestionado y la alerta del panel de administración se retirará automáticamente.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-semibold">
                                <i class="bi bi-journal-check me-1 text-info"></i> Observación Administrativa / Acta de Citación:
                            </label>
                            <textarea name="observacion_admin" id="gestionarObservacion" class="form-control form-control-dark text-white" rows="3" placeholder="Ej: Citado para el día 25/10/2026 10:00 AM en Sala de Coordinación. Notificación enviada al correo del aprendiz."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">
                            <i class="bi bi-save me-1"></i> Actualizar Caso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/admin.js"></script>
</body>

</html>
