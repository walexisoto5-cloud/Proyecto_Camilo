<?php
// Variables disponibles: $instructores, $buscar
$instructores = $instructores ?? [];
$buscar = $buscar ?? '';
$activo = 'gestion_instructores';
$rol = $_SESSION['rol'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Instructores - Control de Asistencia</title>
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
                        <i class="bi bi-person-video3 fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Instructores</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <button class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoInstructor">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Instructor
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
                    <input type="hidden" name="action" value="gestion_instructores">
                    <div class="col-md-9">
                        <label class="form-label text-white small fw-semibold">Buscar Instructor</label>
                        <input type="text" name="buscar" value="<?= htmlspecialchars($buscar); ?>" class="form-control form-control-dark text-white" placeholder="Nombre, apellido, documento o usuario">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success rounded-pill fw-semibold w-100">
                            <i class="bi bi-search me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Instructores -->
            <div class="card card-dark-panel p-3 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-text-sm">
                        <thead>
                            <tr class="text-white-50 small text-uppercase border-bottom border-secondary">
                                <th>Nombre Completo</th>
                                <th>Documento</th>
                                <th>Usuario</th>
                                <th>Teléfono</th>
                                <th>Fichas a Cargo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($instructores)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-white-50 py-4">No se encontraron instructores registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($instructores as $i): ?>
                                    <tr class="border-bottom border-secondary">
                                        <td class="text-white fw-semibold">
                                            <i class="bi bi-person-badge text-warning me-1"></i>
                                            <?= htmlspecialchars($i['nombre'] . ' ' . $i['apellido']); ?>
                                        </td>
                                        <td class="text-white-50"><?= htmlspecialchars($i['identificacion']); ?></td>
                                        <td class="text-white-50"><?= htmlspecialchars($i['nombre_usuario']); ?></td>
                                        <td class="text-white-50"><?= htmlspecialchars($i['telefono'] ?: '—'); ?></td>
                                        <td>
                                            <?php if (!empty($i['fichas_asignadas'])): ?>
                                                <span class="badge bg-dark border border-secondary text-info">
                                                    <?= htmlspecialchars($i['fichas_asignadas']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">Sin fichas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($i['estado'] === 'Activo'): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success px-2 py-1">Activo</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-info btn-sm rounded-pill me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarInstructor"
                                                data-id="<?= (int)$i['id_usuario']; ?>"
                                                data-nombre="<?= htmlspecialchars($i['nombre']); ?>"
                                                data-apellido="<?= htmlspecialchars($i['apellido']); ?>"
                                                data-documento="<?= htmlspecialchars($i['identificacion']); ?>"
                                                data-telefono="<?= htmlspecialchars($i['telefono'] ?? ''); ?>"
                                                data-estado="<?= htmlspecialchars($i['estado']); ?>"
                                                title="Editar Instructor">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="index.php?action=eliminar_instructor&id=<?= (int)$i['id_usuario']; ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-pill"
                                               onclick="return confirm('¿Seguro que deseas eliminar este instructor?');"
                                               title="Eliminar Instructor">
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

    <!-- Modal Nuevo Instructor -->
    <div class="modal fade" id="modalNuevoInstructor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-person-plus-fill text-warning me-2"></i>Registrar Nuevo Instructor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=crear_instructor" method="POST">
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Nombres</label>
                                <input name="nombre" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Apellidos</label>
                                <input name="apellido" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Documento de Identidad</label>
                                <input name="identificacion" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Teléfono</label>
                                <input name="telefono" class="form-control form-control-dark text-white">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Nombre de Usuario</label>
                                <input name="nombre_usuario" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Contraseña</label>
                                <input name="contrasena" type="password" class="form-control form-control-dark text-white" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Guardar Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Instructor -->
    <div class="modal fade" id="modalEditarInstructor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square text-info me-2"></i>Editar Datos del Instructor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=actualizar_instructor" method="POST">
                    <input type="hidden" name="id_usuario" id="edit_instructor_id">
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Nombres</label>
                                <input name="nombre" id="edit_instructor_nombre" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Apellidos</label>
                                <input name="apellido" id="edit_instructor_apellido" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Documento de Identidad</label>
                                <input name="identificacion" id="edit_instructor_documento" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Teléfono</label>
                                <input name="telefono" id="edit_instructor_telefono" class="form-control form-control-dark text-white">
                            </div>
                            <div class="col-md-12 text-start">
                                <label class="form-label text-white small fw-semibold">Estado de la Cuenta</label>
                                <select name="estado" id="edit_instructor_estado" class="form-select form-select-dark text-white">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
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
