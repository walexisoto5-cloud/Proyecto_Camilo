<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Asistencia</title>
    <!-- Bootstrap 5 CSS e Íconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Estilos Personalizados del Dashboard -->
    <link rel="stylesheet" href="public/css/dashboard.css">
</head>
<body>

    <div class="dashboard-container">

        <!-- Sidebar Flotante -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <nav class="nav-items">
                <a href="index.php?action=dashboard" class="nav-item-btn active" title="Dashboard">
                    <i class="bi bi-grid-fill fs-5"></i>
                </a>
                <a href="#" class="nav-item-btn" data-bs-toggle="modal" data-bs-target="#modalFicha" title="Nueva Ficha">
                    <i class="bi bi-journal-plus fs-5"></i>
                </a>
                <a href="#" class="nav-item-btn" data-bs-toggle="modal" data-bs-target="#modalInstructor" title="Nuevo Instructor">
                    <i class="bi bi-person-plus-fill fs-5"></i>
                </a>
                <a href="index.php?action=logout" class="nav-item-btn mt-auto" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </a>
            </nav>
        </aside>

        <!-- Contenido Central -->
        <main class="main-content">

            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h2 class="fw-bold mb-1" style="color: var(--text-dark);">¡Hola, <?= htmlspecialchars((string)($nombreUsuario ?? 'Usuario')); ?>!</h2>
                    <p class="text-muted small mb-0">Resumen del sistema de gestión de asistencia</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-action-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFicha">
                        <i class="bi bi-plus-lg me-1"></i> Ficha
                    </button>
                    <button class="btn btn-action-orange shadow-sm" data-bs-toggle="modal" data-bs-target="#modalInstructor">
                        <i class="bi bi-person-plus me-1"></i> Instructor
                    </button>
                </div>
            </div>

            <!-- Alertas -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= htmlspecialchars((string)($tipoMensaje ?? 'info')); ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?= htmlspecialchars((string)$mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- KPIs -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="dashboard-card stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="stat-icon"><i class="bi bi-people"></i></div>
                        </div>
                        <h3 class="fw-bold mb-1"><?= (int)($totalAprendices ?? 0); ?></h3>
                        <span class="text-muted small fw-semibold">Aprendices</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dashboard-card stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="stat-icon" style="color: #28a745; background: #e8f8ec;"><i class="bi bi-check-circle"></i></div>
                        </div>
                        <h3 class="fw-bold mb-1"><?= (int)($asistenciasHoy ?? 0); ?></h3>
                        <span class="text-muted small fw-semibold">Asistencias Hoy</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dashboard-card stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="stat-icon" style="color: #ffc107; background: #fffbf0;"><i class="bi bi-clock-history"></i></div>
                        </div>
                        <h3 class="fw-bold mb-1"><?= (int)($retardosHoy ?? 0); ?></h3>
                        <span class="text-muted small fw-semibold">Retardos Hoy</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dashboard-card stat-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="stat-icon" style="color: #dc3545; background: #fdeef0;"><i class="bi bi-file-earmark-text"></i></div>
                        </div>
                        <h3 class="fw-bold mb-1"><?= (int)($excusasPendientes ?? 0); ?></h3>
                        <span class="text-muted small fw-semibold">Excusas Pend.</span>
                    </div>
                </div>
            </div>

            <!-- Tabla Últimos Marcajes -->
            <div class="dashboard-card mt-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Últimos Marcajes</h5>
                    <span class="badge bg-light text-dark fw-normal px-3 py-2 border">Recientes</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
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
                                        $badgeStyle = ($estadoLower === 'a tiempo' || $estadoLower === 'asistió') 
                                            ? 'background: #e8f8ec; color: #1e7e34;' 
                                            : (($estadoLower === 'retardo') ? 'background: #fff8e6; color: #b78103;' : 'background: #f0f0f5; color: #6c757d;');
                                    ?>
                                    <tr>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars(trim($nombreReg . ' ' . $apellidoReg)); ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($fechaReg); ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($entradaReg); ?></td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2" style="<?= $badgeStyle; ?>">
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

        </main>

        <!-- Panel Derecho -->
        <aside class="right-panel">
            <div class="dashboard-card user-profile-card">
                <div class="user-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars((string)($nombreUsuario ?? 'Usuario')); ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars((string)($rolUsuario ?? 'Administrador')); ?></p>
                <div class="pt-3 border-top d-flex justify-content-around text-center">
                    <div>
                        <span class="d-block fw-bold text-dark"><?= (int)($totalAprendices ?? 0); ?></span>
                        <small class="text-muted" style="font-size: 0.75rem;">Registrados</small>
                    </div>
                    <div class="border-start ms-2 ps-2">
                        <span class="d-block fw-bold text-dark"><?= (int)($asistenciasHoy ?? 0); ?></span>
                        <small class="text-muted" style="font-size: 0.75rem;">Activos Hoy</small>
                    </div>
                </div>
            </div>

            <div class="dashboard-card p-3" style="background: linear-gradient(135deg, var(--sidebar-purple) 0%, var(--sidebar-dark) 100%); color: white;">
                <h6 class="fw-bold mb-2"><i class="bi bi-shield-check me-2"></i>Estado del Sistema</h6>
                <p class="small mb-0 text-white-50">Conexión a base de datos activa y sincronizada.</p>
            </div>
        </aside>

    </div>

    <!-- Modales -->
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
                        <button type="submit" class="btn btn-action-custom">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <button type="submit" class="btn btn-action-orange">Guardar Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>