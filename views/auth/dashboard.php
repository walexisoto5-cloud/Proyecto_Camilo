<?php
// Las fichas y variables son proporcionadas directamente por DashboardController
$fichas = $fichas ?? [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <nav class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100" style="width: 80px; position: fixed; top: 0; left: 0; z-index: 1000;">
                <a href="index.php?action=dashboard" class="nav-link text-white active d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Inicio">
                    <i class="bi bi-grid-fill fs-5 mb-1"></i>
                    <span style="font-size: 9px; line-height: 1;">Inicio</span>
                </a>
                <a href="#" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#modalFicha" title="Nueva Ficha">
                    <i class="bi bi-journal-plus fs-5 mb-1"></i>
                    <span style="font-size: 9px; line-height: 1;">Ficha</span>
                </a>
                <a href="#" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#modalInstructor" title="Nuevo Instructor">
                    <i class="bi bi-person-plus-fill fs-5 mb-1"></i>
                    <span style="font-size: 9px; line-height: 1;">Instructor</span>
                </a>
                <a href="#" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#modalAprendiz" title="Nuevo Aprendiz">
                    <i class="bi bi-people-fill fs-5 mb-1"></i>
                    <span style="font-size: 9px; line-height: 1;">Aprendiz</span>
                </a>
                <a href="#" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#modalExcusas" title="Subir Excusa">
                    <i class="bi bi-file-earmark-arrow-up-fill fs-5 mb-1"></i>
                    <span style="font-size: 9px; line-height: 1;">Excusa</span>
                </a>
                <a href="index.php?action=escaner_rfid" class="nav-link text-center text-white p-2 <?php echo (isset($_GET['action']) && $_GET['action'] === 'escaner_rfid') ? 'active bg-dark rounded' : ''; ?>">
                    <div class="mb-1"><i class="bi bi-upc-scan fs-4"></i></div>
                    <div style="font-size: 0.85rem; line-height: 1.1;">Lector<br>RFID</div>
                </a>
                <a href="index.php?action=portal_instructor" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Panel Instructor">
                    <i class="bi bi-person-video3 fs-5 mb-1 text-warning"></i>
                    <span style="font-size: 8px; line-height: 1;">P. Instructor</span>
                </a>
                <a href="index.php?action=portal_aprendiz" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Portal Aprendiz">
                    <i class="bi bi-person-badge fs-5 mb-1 text-info"></i>
                    <span style="font-size: 8px; line-height: 1;">P. Aprendiz</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-white">¡Hola, <?= htmlspecialchars((string)($nombreUsuario ?? 'Usuario')); ?>!</h2>
                    <p class="text-muted small mb-0">Resumen del sistema de gestión de asistencia</p>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex gap-2">
                        <button class="btn text-white shadow-sm fw-semibold" style="background-color:#16a34a; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalFicha">
                            <i class="bi bi-plus-lg me-1"></i> Ficha
                        </button>
                        <button class="btn text-white shadow-sm fw-semibold" style="background-color:#16a34a; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalInstructor">
                            <i class="bi bi-person-plus me-1"></i> Instructor
                        </button>
                    </div>

                    <div class="header-profile-card d-flex align-items-center gap-3" data-bs-toggle="modal" data-bs-target="#modalPerfil" title="Ver datos del administrador">
                        <div class="header-avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="text-start pe-2">
                            <h6 class="fw-bold mb-0 text-white" style="font-size: 0.9rem;"><?= htmlspecialchars((string)($nombreUsuario ?? 'Administrador')); ?></h6>
                            <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars((string)($rolUsuario ?? 'Administrador')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($vistaActiva) && $vistaActiva === 'escaner'): ?>

                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="card shadow-lg text-center p-5" style="background-color: #161b22; border: 1px solid #30363d;">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="bi bi-credit-card-2-front fs-1 text-info"></i>
                                </div>
                                <h3 class="card-title text-white mb-3">Acerque su Tarjeta o Llavero RFID</h3>
                                <p class="text-muted mb-4">El lector procesará el código automáticamente y registrará la entrada o salida.</p>
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

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="dashboard-card stat-card shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="color: #3b82f6; background: rgba(59, 130, 246, 0.15);">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?php echo $totalAprendices ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Aprendices</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="dashboard-card stat-card shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="color: #22c55e; background: rgba(34, 197, 94, 0.15);">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?php echo $asistenciasHoy ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Asistencias Hoy</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="dashboard-card stat-card shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="color: #22c55e; background: rgba(34, 197, 94, 0.15);">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?php echo $totalFichas ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Número de Fichas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="dashboard-card stat-card shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="color: #eab308; background: rgba(234, 179, 8, 0.15);">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?php echo $retardosHoy ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Retardos Hoy</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="dashboard-card stat-card shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="color: #ef4444; background: rgba(239, 68, 68, 0.15);">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white"><?php echo $excusasPendientes ?? 0; ?></h3>
                                    <span class="text-muted small fw-semibold">Excusas Pend.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mt-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white">Últimos Marcajes</h5>
                        <span class="badge bg-dark text-muted fw-normal px-3 py-2 border border-secondary rounded-pill" style="font-size: 0.75rem;">Recientes</span>
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

                                        // Estilos de los badges según el estado
                                        $badgeStyle = ($estadoLower === 'a tiempo' || $estadoLower === 'asistió')
                                            ? 'background: rgba(34, 197, 94, 0.15); color: #22c55e;'
                                            : (($estadoLower === 'retardo') ? 'background: rgba(234, 179, 8, 0.15); color: #eab308;' : 'background: rgba(108, 117, 125, 0.15); color: #94a3b8;');
                                        ?>
                                        <tr>
                                            <td class="fw-semibold text-white">
                                                <?= htmlspecialchars(trim($nombreReg . ' ' . $apellidoReg)); ?>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars($fechaReg); ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($entradaReg); ?></td>
                                            <td>
                                                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="<?= $badgeStyle; ?>">
                                                    <?= htmlspecialchars($estadoReg); ?>
                                                </span>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars($salidaReg); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay registros de asistencias recientes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
    </div>

    <div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2" style="color: var(--sidebar-purple);"></i>Nueva Ficha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?action=crear_ficha" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Número de Ficha</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="numero_ficha" placeholder="Ej. 3234082" required style="border-radius: 12px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Programa de Formación</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="programa_formacion" placeholder="Ej. ADSO" required style="border-radius: 12px;">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Cancelar</button>
                        <button type="submit" class="btn btn-action-custom" style="background-color: #16a34a; color: #fff;">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="modalInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2" style="color: var(--accent-orange);"></i>Nuevo Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crear_instructor" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Documento / Identificación</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="documento" required style="border-radius: 12px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nombre</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="nombre" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Apellido</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="apellido" required style="border-radius: 12px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nombre de Usuario / Correo</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="correo" required style="border-radius: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Contraseña</label>
                        <input type="password" class="form-control form-control-lg fs-6" name="contrasena" required style="border-radius: 12px;">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Cancelar</button>
                    <button type="submit" class="btn btn-action-orange" style="background-color: #16a34a; color: #fff;">Guardar Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAprendiz" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-people-fill me-2" style="color: var(--accent-green, #22c55e);"></i>Registrar Nuevo Aprendiz
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crearAprendiz" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Documento de Identidad</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="documento" placeholder="Número de documento" required style="border-radius: 12px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nombres</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="nombre" placeholder="Nombres" required style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Apellidos</label>
                            <input type="text" class="form-control form-control-lg fs-6" name="apellido" placeholder="Apellidos" required style="border-radius: 12px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nombre de Usuario (para Iniciar Sesión)</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="nombre_usuario" placeholder="Ej. usuario.aprendiz o correo" required style="border-radius: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Contraseña</label>
                        <input type="password" class="form-control form-control-lg fs-6" name="contrasena" placeholder="Contraseña de acceso" required style="border-radius: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Ficha de Formación</label>
                        <select class="form-select form-select-lg fs-6" name="id_ficha" required style="border-radius: 12px;">
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Cancelar</button>
                    <button type="submit" class="btn btn-action-custom" style="background-color: #16a34a; color: #fff; border-radius: 12px;">Guardar Aprendiz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcusas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-arrow-up me-2" style="color: var(--sidebar-purple);"></i>Registrar Excusa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=guardar_excusa" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Documento del Aprendiz</label>
                        <input type="text" class="form-control form-control-lg fs-6" name="documento_aprendiz" placeholder="Número de documento" required style="border-radius: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Fecha de la Falta / Incapacidad</label>
                        <input type="date" class="form-control form-control-lg fs-6" name="fecha_falta" required style="border-radius: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Motivo / Observación</label>
                        <textarea class="form-control fs-6" name="motivo" rows="3" placeholder="Describe brevemente el motivo..." required style="border-radius: 12px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Adjuntar Evidencia (PDF o Imagen)</label>
                        <input type="file" class="form-control form-control-lg fs-6" name="archivo_excusa" accept=".pdf, .jpg, .jpeg, .png" required style="border-radius: 12px;">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Cancelar</button>
                    <button type="submit" class="btn btn-action-custom" style="background-color: #16a34a; color: #fff;">Subir Excusa</button>
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
                    <i class="bi bi-person-badge-fill me-2" style="color: #22c55e;"></i>Información del Administrador
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
                <p class="text-muted small mb-3"><?= htmlspecialchars((string)($rolUsuario ?? 'Administrador del Sistema')); ?></p>

                <div class="row text-center py-3 my-3 g-0 modal-stats-box">
                    <div class="col-6 border-end border-secondary">
                        <span class="d-block fw-bold fs-5 text-success"><?= (int)($totalAprendices ?? 0); ?></span>
                        <span class="text-muted small">Registrados</span>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-bold fs-5 text-success"><?= (int)($asistenciasHoy ?? 0); ?></span>
                        <span class="text-muted small">Activos Hoy</span>
                    </div>
                </div>

                <div class="text-start px-2 mt-3">
                    <p class="mb-2 text-muted small fw-semibold"><i class="bi bi-shield-check text-success me-2"></i>Estado de Sesión: <span class="text-white">Activa y Segura</span></p>
                    <p class="mb-0 text-muted small fw-semibold"><i class="bi bi-database-check text-success me-2"></i>Base de Datos: <span class="text-white">Sincronizada</span></p>
                </div>
            </div>

            <a class="dropdown-item text-danger fw-bold text-center py-2 w-100"
                style="background-color: rgba(220, 53, 69, 0.1); border-radius: 4px; transition: background-color 0.2s;"
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