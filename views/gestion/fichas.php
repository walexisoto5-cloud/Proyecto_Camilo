<?php
// Variables disponibles: $fichas, $instructores, $buscar
$fichas = $fichas ?? [];
$instructores = $instructores ?? [];
$buscar = $buscar ?? '';
$activo = 'gestion_fichas';
$rol = $_SESSION['rol'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Fichas - Control de Asistencia</title>
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
                    <div class="p-2 rounded-circle bg-dark border border-secondary text-info">
                        <i class="bi bi-journal-text fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Fichas</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <button class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaFicha">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Ficha
                    </button>

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

            <!-- Filtro de Búsqueda -->
            <div class="card card-dark-panel p-3 mb-4 shadow-sm">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="action" value="gestion_fichas">
                    <div class="col-md-9">
                        <label class="form-label text-white small fw-semibold">Buscar Ficha</label>
                        <input type="text" name="buscar" value="<?= htmlspecialchars($buscar); ?>" class="form-control form-control-dark text-white" placeholder="Número de ficha o nombre del programa">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success rounded-pill fw-semibold w-100">
                            <i class="bi bi-search me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Fichas -->
            <div class="card card-dark-panel p-3 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-text-sm">
                        <thead>
                            <tr class="text-white-50 small text-uppercase border-bottom border-secondary">
                                <th>N° Ficha</th>
                                <th>Programa de Formación</th>
                                <th>Jornada</th>
                                <th>Instructor a Cargo</th>
                                <th>Aprendices</th>
                                <th>Horario</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fichas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-white-50 py-4">No se encontraron fichas registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fichas as $f): ?>
                                    <tr class="border-bottom border-secondary">
                                        <td class="text-white fw-semibold">
                                            <span class="badge bg-dark border border-secondary text-info fs-6 px-2 py-1">
                                                <?= htmlspecialchars($f['numero_ficha']); ?>
                                            </span>
                                        </td>
                                        <td class="text-white"><?= htmlspecialchars($f['nombre_programa']); ?></td>
                                        <td class="text-white-50"><?= htmlspecialchars($f['jornada']); ?></td>
                                        <td>
                                            <?php if (!empty($f['instructor'])): ?>
                                                <span class="text-warning small fw-semibold">
                                                    <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($f['instructor']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                <?= (int)($f['total_aprendices'] ?? 0); ?> aprendices
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?action=calendario&id_ficha=<?= (int)$f['id_ficha']; ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Ver horario en calendario">
                                                <i class="bi bi-calendar3 me-1"></i> Calendario
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="index.php?action=reporte_asistencia_excel&tipo=mensual&ficha_id=<?= (int)$f['id_ficha']; ?>" class="btn btn-outline-success btn-sm rounded-pill me-1" title="Descargar Sábana Mensual en Excel (.xlsx)">
                                                <i class="bi bi-file-earmark-excel"></i>
                                            </a>
                                            <button class="btn btn-outline-info btn-sm rounded-pill me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarFicha"
                                                data-id="<?= (int)$f['id_ficha']; ?>"
                                                data-numero="<?= htmlspecialchars($f['numero_ficha']); ?>"
                                                data-programa="<?= htmlspecialchars($f['nombre_programa']); ?>"
                                                data-jornada="<?= htmlspecialchars($f['jornada']); ?>"
                                                data-instructor="<?= (int)($f['fk_usuario'] ?? 0); ?>"
                                                title="Editar Ficha">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="index.php?action=eliminar_ficha&id=<?= (int)$f['id_ficha']; ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-pill"
                                               onclick="return confirm('¿Seguro que deseas eliminar esta ficha? Se desvincularán los aprendices asociados.');"
                                               title="Eliminar Ficha">
                                                <i class="bi bi-trash"></i>
                                            </a>
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

    <!-- Modal Nueva Ficha -->
    <div class="modal fade" id="modalNuevaFicha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-plus-circle-fill text-success me-2"></i>Registrar Nueva Ficha
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=crear_ficha" method="POST">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Número de Ficha</label>
                            <input type="text" name="numero_ficha" class="form-control form-control-dark text-white" placeholder="Ej. 3064749" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Nombre del Programa</label>
                            <input type="text" name="nombre_programa" class="form-control form-control-dark text-white" placeholder="Ej. Análisis y Desarrollo de Software (ADSO)" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Jornada</label>
                            <select name="jornada" class="form-select form-select-dark text-white" required>
                                <option value="Diurna">Diurna (06:00 AM - 12:00 PM)</option>
                                <option value="Mixta">Mixta / Tarde</option>
                                <option value="Nocturna">Nocturna</option>
                            </select>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Instructor Líder / Asignado (Opcional)</label>
                            <select name="fk_usuario" class="form-select form-select-dark text-white">
                                <option value="0">-- Sin Asignar --</option>
                                <?php foreach ($instructores as $ins): ?>
                                    <option value="<?= (int)$ins['id_usuario']; ?>">
                                        <?= htmlspecialchars($ins['nombre_completo']); ?> (Doc: <?= htmlspecialchars($ins['identificacion']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Ficha -->
    <div class="modal fade" id="modalEditarFicha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square text-info me-2"></i>Editar Ficha de Formación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=actualizar_ficha" method="POST">
                    <input type="hidden" name="id_ficha" id="edit_ficha_id">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Número de Ficha</label>
                            <input type="text" name="numero_ficha" id="edit_ficha_numero" class="form-control form-control-dark text-white" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Programa de Formación</label>
                            <input type="text" name="nombre_programa" id="edit_ficha_programa" class="form-control form-control-dark text-white" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Jornada</label>
                            <select name="jornada" id="edit_ficha_jornada" class="form-select form-select-dark text-white" required>
                                <option value="Diurna">Diurna (06:00 AM - 12:00 PM)</option>
                                <option value="Mixta">Mixta / Tarde</option>
                                <option value="Nocturna">Nocturna</option>
                            </select>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Instructor Asignado</label>
                            <select name="fk_usuario" id="edit_ficha_instructor" class="form-select form-select-dark text-white">
                                <option value="0">-- Sin Asignar --</option>
                                <?php foreach ($instructores as $ins): ?>
                                    <option value="<?= (int)$ins['id_usuario']; ?>">
                                        <?= htmlspecialchars($ins['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="public/js/admin.js"></script>
    <?php require_once __DIR__ . '/../partials/modal_perfil.php'; ?>
</body>
</html>
