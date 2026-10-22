<?php
// Variables disponibles: $excusas
$excusas = $excusas ?? [];
$activo = 'gestion_excusas';
$rol = $_SESSION['rol'] ?? 'Administrador';

function renderBadgeExcusa(string $estado): string
{
    switch ($estado) {
        case 'Aprobada':
            return '<span class="badge rounded-pill bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Aprobada</span>';
        case 'Rechazada':
            return '<span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Rechazada</span>';
        default:
            return '<span class="badge rounded-pill bg-warning-subtle text-warning px-2 py-1"><i class="bi bi-clock me-1"></i>Pendiente</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Excusas Médicas - Control de Asistencia</title>
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
                    <div class="p-2 rounded-circle bg-dark border border-secondary text-danger">
                        <i class="bi bi-envelope-paper fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Excusas</h4>
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
            
            <!-- Tabla de Excusas -->
            <div class="card card-dark-panel p-3 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-text-sm">
                        <thead>
                            <tr class="text-white-50 small text-uppercase border-bottom border-secondary">
                                <th>Aprendiz</th>
                                <th>Documento</th>
                                <th>Ficha</th>
                                <th>Fecha Falta</th>
                                <th>Motivo / Justificación</th>
                                <th>Soporte Médico</th>
                                <th>Estado</th>
                                <th class="text-center">Decisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($excusas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-white-50 py-4">No hay excusas radicadas en el sistema.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($excusas as $e): ?>
                                    <?php 
                                        $rutaArchivo = 'public/uploads/excusas/' . $e['archivo'];
                                        if (!file_exists(__DIR__ . '/../../' . $rutaArchivo)) {
                                            $rutaArchivo = 'uploads/' . $e['archivo'];
                                        }
                                    ?>
                                    <tr class="border-bottom border-secondary">
                                        <td class="text-white fw-semibold">
                                            <i class="bi bi-person text-info me-1"></i>
                                            <?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']); ?>
                                        </td>
                                        <td class="text-white-50"><?= htmlspecialchars($e['identificacion']); ?></td>
                                        <td>
                                            <?php if (!empty($e['numero_ficha'])): ?>
                                                <span class="badge bg-dark border border-secondary text-white">
                                                    <?= htmlspecialchars($e['numero_ficha']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-white-50"><?= htmlspecialchars($e['fecha_asistencia'] ?? $e['fecha_subida']); ?></td>
                                        <td class="text-white-50 small col-max-text">
                                            <?= htmlspecialchars($e['observacion']); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($e['archivo'])): ?>
                                                <a href="<?= htmlspecialchars($rutaArchivo); ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3" title="Ver documento adjunto">
                                                    <i class="bi bi-paperclip me-1"></i> Ver Soporte
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= renderBadgeExcusa($e['estado']); ?></td>
                                        <td class="text-center">
                                            <?php if ($e['estado'] === 'Pendiente'): ?>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?action=aprobar_excusa&id=<?= (int)$e['id_excusa']; ?>" 
                                                       class="btn btn-outline-success btn-sm rounded-pill me-1"
                                                       onclick="return confirm('¿Aprobar esta excusa médica? Se justificará la falta del aprendiz.');"
                                                       title="Aprobar Excusa">
                                                        <i class="bi bi-check-lg me-1"></i> Aprobar
                                                    </a>
                                                    <a href="index.php?action=rechazar_excusa&id=<?= (int)$e['id_excusa']; ?>" 
                                                       class="btn btn-outline-danger btn-sm rounded-pill"
                                                       onclick="return confirm('¿Rechazar esta excusa médica?');"
                                                       title="Rechazar Excusa">
                                                        <i class="bi bi-x-lg me-1"></i> Rechazar
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-white-50 small">
                                                    Revisada: <?= !empty($e['fecha_revision']) ? htmlspecialchars(substr($e['fecha_revision'], 0, 10)) : 'Sí'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once __DIR__ . '/../partials/modal_perfil.php'; ?>
</body>
</html>
