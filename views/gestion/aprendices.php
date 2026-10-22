<?php
// Variables disponibles: $aprendices, $fichas, $buscar, $estado
$aprendices = $aprendices ?? [];
$fichas = $fichas ?? [];
$buscar = $buscar ?? '';
$estado = $estado ?? '';
$activo = 'gestion_aprendices';
$rol = $_SESSION['rol'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Aprendices - Control de Asistencia</title>
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
                    <div class="p-2 rounded-circle bg-dark border border-secondary text-success">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Aprendices</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <button class="btn btn-outline-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarAprendices">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Importar CSV
                    </button>
                    <button class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoAprendiz">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Aprendiz
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


            <!-- Filtros de Búsqueda -->
            <div class="card card-dark-panel p-3 mb-4 shadow-sm">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="action" value="gestion_aprendices">
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-semibold">Buscar Aprendiz</label>
                        <input type="text" name="buscar" value="<?= htmlspecialchars($buscar); ?>" class="form-control form-control-dark text-white" placeholder="Nombre, documento, usuario o RFID">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white small fw-semibold">Estado</label>
                        <select name="estado" class="form-select form-select-dark text-white">
                            <option value="">Todos los Estados</option>
                            <option value="Activo" <?= $estado === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="Inactivo" <?= $estado === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success rounded-pill fw-semibold w-100">
                            <i class="bi bi-search me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Aprendices -->
            <div class="card card-dark-panel p-3 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 table-text-sm">
                        <thead>
                            <tr class="text-white-50 small text-uppercase border-bottom border-secondary">
                                <th>Nombre Completo</th>
                                <th>Documento</th>
                                <th>Usuario</th>
                                <th>Ficha de Formación</th>
                                <th>Código RFID</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($aprendices)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-white-50 py-4">No se encontraron aprendices registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($aprendices as $a): ?>
                                    <tr class="border-bottom border-secondary">
                                        <td class="text-white fw-semibold">
                                            <i class="bi bi-person-fill text-success me-1"></i>
                                            <?= htmlspecialchars($a['nombre'] . ' ' . $a['apellido']); ?>
                                        </td>
                                        <td class="text-white-50"><?= htmlspecialchars($a['identificacion']); ?></td>
                                        <td class="text-white-50"><?= htmlspecialchars($a['nombre_usuario']); ?></td>
                                        <td>
                                            <?php if (!empty($a['numero_ficha'])): ?>
                                                <span class="badge bg-dark border border-secondary text-info">
                                                    Ficha <?= htmlspecialchars($a['numero_ficha']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Sin ficha</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-white-50 font-monospace small">
                                            <?= !empty($a['codigo_rfid']) ? htmlspecialchars($a['codigo_rfid']) : '<span class="text-muted">—</span>'; ?>
                                        </td>
                                        <td>
                                            <?php if ($a['estado'] === 'Activo'): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success px-2 py-1">Activo</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-info btn-sm rounded-pill me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarAprendiz"
                                                data-id="<?= (int)$a['id_aprendiz']; ?>"
                                                data-nombre="<?= htmlspecialchars($a['nombre']); ?>"
                                                data-apellido="<?= htmlspecialchars($a['apellido']); ?>"
                                                data-documento="<?= htmlspecialchars($a['identificacion']); ?>"
                                                data-telefono="<?= htmlspecialchars($a['telefono'] ?? ''); ?>"
                                                data-rfid="<?= htmlspecialchars($a['codigo_rfid'] ?? ''); ?>"
                                                data-ficha="<?= (int)$a['fk_ficha']; ?>"
                                                data-estado="<?= htmlspecialchars($a['estado']); ?>"
                                                title="Editar Aprendiz">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="index.php?action=eliminar_aprendiz&id=<?= (int)$a['id_aprendiz']; ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-pill"
                                               onclick="return confirm('¿Seguro que deseas eliminar este aprendiz?');"
                                               title="Eliminar Aprendiz">
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

    <!-- Modal Nuevo Aprendiz -->
    <div class="modal fade" id="modalNuevoAprendiz" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-person-plus-fill text-success me-2"></i>Registrar Nuevo Aprendiz
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=crear_aprendiz" method="POST">
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
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Código RFID (Opcional)</label>
                                <input name="codigo_rfid" class="form-control form-control-dark text-white" placeholder="Escanee o digite tarjeta RFID">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Ficha de Formación</label>
                                <select name="fk_ficha" class="form-select form-select-dark text-white" required>
                                    <option value="" disabled selected>-- Seleccione Ficha --</option>
                                    <?php foreach ($fichas as $f): ?>
                                        <option value="<?= (int)$f['id_ficha']; ?>">
                                            Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Guardar Aprendiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Importar Aprendices Masivamente -->
    <div class="modal fade" id="modalImportarAprendices" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>Importar Aprendices (CSV)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=importar_aprendices" method="POST" enctype="multipart/form-data">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Ficha de Formación Destino</label>
                            <select name="fk_ficha" class="form-select form-control-dark text-white input-radius-sm" required>
                                <option value="" disabled selected>Selecciona la ficha...</option>
                                <?php foreach ($fichas as $f): ?>
                                    <option value="<?= (int)$f['id_ficha']; ?>">
                                        Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa'] ?? 'ADSO'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Archivo CSV (.csv)</label>
                            <input type="file" name="archivo_csv" accept=".csv, text/csv, text/plain" class="form-control form-control-dark text-white input-radius-sm" required>
                            <small class="text-muted d-block mt-1 text-size-xs">
                                Columnas esperadas separadas por coma (,) o punto y coma (;):<br>
                                <code>identificacion, nombre, apellido, usuario/correo, telefono, codigo_rfid</code>
                            </small>
                        </div>
                        <div class="p-3 rounded bg-dark border border-secondary text-info small text-start">
                            <i class="bi bi-info-circle me-1"></i> La contraseña de acceso inicial será automáticamente el número de documento del aprendiz.
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white btn-cancel-modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">Subir e Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Aprendiz -->
    <div class="modal fade" id="modalEditarAprendiz" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark border-0 shadow-lg card-dark-modal">
                <div class="modal-header border-secondary pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square text-info me-2"></i>Editar Aprendiz
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=actualizar_aprendiz" method="POST">
                    <input type="hidden" name="id_aprendiz" id="edit_id">
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Nombres</label>
                                <input name="nombre" id="edit_nombre" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Apellidos</label>
                                <input name="apellido" id="edit_apellido" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Documento de Identidad</label>
                                <input name="identificacion" id="edit_documento" class="form-control form-control-dark text-white" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label text-white small fw-semibold">Teléfono</label>
                                <input name="telefono" id="edit_telefono" class="form-control form-control-dark text-white">
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label text-white small fw-semibold">Código RFID</label>
                                <input name="codigo_rfid" id="edit_rfid" class="form-control form-control-dark text-white">
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label text-white small fw-semibold">Ficha</label>
                                <select name="fk_ficha" id="edit_ficha" class="form-select form-select-dark text-white">
                                    <option value="0">-- Sin Ficha --</option>
                                    <?php foreach ($fichas as $f): ?>
                                        <option value="<?= (int)$f['id_ficha']; ?>">
                                            Ficha <?= htmlspecialchars($f['numero_ficha']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label text-white small fw-semibold">Estado</label>
                                <select name="estado" id="edit_estado" class="form-select form-select-dark text-white">
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
