<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/dashboard.css">
</head>
<body class="bg-light">

<div class="d-flex min-vh-100">
    <!-- Menú Lateral -->
    <div class="sidebar-nav bg-success d-flex flex-column align-items-center py-4 rounded-end-4 shadow" style="width: 70px;">
        <a href="#" class="mb-4 text-white fs-4 text-decoration-none" title="SENA Control">
            <i class="fa-solid fa-graduation-cap"></i>
        </a>
        <ul class="nav nav-pills flex-column mb-auto text-center w-100 px-2">
            <li class="nav-item mb-3">
                <a href="index.php?action=dashboard" class="nav-link active bg-white text-success rounded-3 fs-5" title="Inicio">
                    <i class="fa-solid fa-house"></i>
                </a>
            </li>
            <!-- Botón Modal Agregar Ficha -->
            <li class="nav-item mb-3">
                <button type="button" class="btn btn-link text-white-50 p-0 fs-5 w-100" data-bs-toggle="modal" data-bs-target="#modalFicha" title="Agregar Nueva Ficha">
                    <i class="fa-solid fa-folder-plus"></i>
                </button>
            </li>
            <!-- Botón Modal Agregar Instructor -->
            <li class="nav-item mb-3">
                <button type="button" class="btn btn-link text-white-50 p-0 fs-5 w-100" data-bs-toggle="modal" data-bs-target="#modalInstructor" title="Agregar Instructor">
                    <i class="fa-solid fa-user-tie"></i>
                </button>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white-50 rounded-3 fs-5" title="Aprendices">
                    <i class="fa-solid fa-users"></i>
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white-50 rounded-3 fs-5" title="Asistencias">
                    <i class="fa-solid fa-clipboard-check"></i>
                </a>
            </li>
        </ul>
        <a href="index.php?action=logout" class="text-white-50 mt-auto fs-5 text-decoration-none" title="Cerrar Sesión">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>

    <!-- Contenido Principal -->
    <div class="flex-grow-1 p-4">
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($tipoMensaje ?? 'info'); ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark">Panel de Control - Asistencia</h3>
                <p class="text-muted small mb-0">Arquitectura MVC - Datos en tiempo real</p>
            </div>
            <span class="badge bg-success px-3 py-2 rounded-pill fs-6">
                <i class="fa-solid fa-database me-1"></i> BD Conectada
            </span>
        </div>

        <!-- Tarjetas de Métricas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
                    <span class="text-secondary small fw-semibold">Aprendices Activos</span>
                    <h3 class="fw-bold my-1 text-primary"><?php echo htmlspecialchars($totalAprendices ?? 0); ?></h3>
                    <small class="text-muted">En el sistema</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
                    <span class="text-secondary small fw-semibold">Asistencias Hoy</span>
                    <h3 class="fw-bold my-1 text-success"><?php echo htmlspecialchars($asistenciasHoy ?? 0); ?></h3>
                    <small class="text-success">Registradas</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
                    <span class="text-secondary small fw-semibold">Novedades / Retardos</span>
                    <h3 class="fw-bold my-1 text-warning"><?php echo htmlspecialchars($retardosHoy ?? 0); ?></h3>
                    <small class="text-warning">Reportadas</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
                    <span class="text-secondary small fw-semibold">Excusas Pendientes</span>
                    <h3 class="fw-bold my-1 text-danger"><?php echo htmlspecialchars($excusasPendientes ?? 0); ?></h3>
                    <small class="text-danger">Por revisar</small>
                </div>
            </div>
        </div>

        <!-- Tabla con Datos Reales -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Últimos Marcajes Registrados
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Aprendiz</th>
                                    <th>Ficha</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ultimosMarcajes)): ?>
                                    <?php foreach ($ultimosMarcajes as $row): ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['numero_ficha'] ?? 'N/A'); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hora_ingreso']); ?></td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <?php echo ucfirst(htmlspecialchars($row['estado'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            No hay marcajes registrados aún en la base de datos.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Perfil del usuario -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                    <div class="mb-3">
                        <i class="fa-solid fa-circle-user fa-5x text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($nombreUsuario ?? 'Usuario Sistema'); ?></h5>
                    <span class="badge bg-success-subtle text-success mt-2 px-3 py-2 rounded-pill fw-bold">
                        <?php echo htmlspecialchars($rolUsuario ?? 'Administrador'); ?>
                    </span>
                    <hr class="my-4 text-secondary opacity-25">
                    <p class="small text-muted mb-0">Sistema de Control de Asistencia SENA</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR FICHA -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-labelledby="modalFichaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalFichaLabel"><i class="fa-solid fa-folder-plus me-2"></i>Agregar Nueva Ficha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crear_ficha" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="numero_ficha" class="form-label fw-semibold small">Número de Ficha</label>
                        <input type="text" class="form-control" id="numero_ficha" name="numero_ficha" placeholder="Ej: 2670123" required>
                    </div>
                    <div class="mb-3">
                        <label for="programa_formacion" class="form-label fw-semibold small">Programa de Formación</label>
                        <input type="text" class="form-control" id="programa_formacion" name="programa_formacion" placeholder="Ej: Análisis y Desarrollo de Software (ADSO)" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-3 fw-bold">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR INSTRUCTOR -->
<div class="modal fade" id="modalInstructor" tabindex="-1" aria-labelledby="modalInstructorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalInstructorLabel"><i class="fa-solid fa-user-tie me-2"></i>Agregar Nuevo Instructor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crear_instructor" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="documento" class="form-label fw-semibold small">Número de Documento</label>
                        <input type="text" class="form-control" id="documento" name="documento" placeholder="Ej: 1088123456" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="nombre" class="form-label fw-semibold small">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
                        </div>
                        <div class="col-6">
                            <label for="apellido" class="form-label fw-semibold small">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label fw-semibold small">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@sena.edu.co" required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label fw-semibold small">Contraseña</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-3 fw-bold">Guardar Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>