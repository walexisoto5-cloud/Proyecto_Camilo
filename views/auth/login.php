<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Control de Asistencia SENA</title>
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Estilos específicos complementarios -->
    <link rel="stylesheet" href="public/css/login.css?v=<?= file_exists('public/css/login.css') ? filemtime('public/css/login.css') : time(); ?>">
</head>

<body class="bg-dark bg-gradient d-flex align-items-center justify-content-center min-vh-100 py-4">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">

                <!-- Tarjeta Principal con Clases Nativas de Bootstrap 5 -->
                <div class="card border border-secondary border-opacity-25 shadow-lg rounded-4 overflow-hidden bg-body-tertiary">
                    
                    <!-- Franja de Acento Institucional SENA -->
                    <div class="bg-success py-1 w-100"></div>

                    <div class="card-body p-4 p-sm-5">

                        <!-- Encabezado con Icono y Marca Institucional -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3 p-3 border border-success border-opacity-25 shadow-sm">
                                <i class="bi bi-shield-lock-fill fs-2"></i>
                            </div>
                            <h4 class="fw-bold mb-1 text-body">Control de Asistencia</h4>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                                    SENA ADSO
                                </span>
                                <span class="text-secondary small">&bull;</span>
                                <small class="text-secondary">Acceso al Sistema</small>
                            </div>
                        </div>

                        <!-- Alerta Bootstrap para Errores de Autenticación -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 small mb-4 rounded-3 border-danger border-opacity-50" role="alert">
                                <i class="bi bi-exclamation-octagon-fill flex-shrink-0 text-danger"></i>
                                <div class="flex-grow-1"><?= htmlspecialchars($error); ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de Inicio de Sesión -->
                        <form action="index.php?action=login" method="POST" id="formLogin">

                            <!-- Campo Usuario / Documento con Form-Floating de Bootstrap -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control rounded-3" id="nombre_usuario" name="nombre_usuario" placeholder="Usuario o Cédula" required autofocus>
                                <label for="nombre_usuario" class="text-secondary">
                                    <i class="bi bi-person me-1 text-success"></i> Usuario o Cédula
                                </label>
                            </div>

                            <!-- Campo Contraseña con Input-Group y Form-Floating -->
                            <div class="input-group mb-3">
                                <div class="form-floating flex-grow-1">
                                    <input type="password" class="form-control rounded-start-3" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                                    <label for="contrasena" class="text-secondary">
                                        <i class="bi bi-key me-1 text-success"></i> Contraseña
                                    </label>
                                </div>
                                <button class="btn btn-outline-secondary px-3 rounded-end-3" type="button" id="btnTogglePassword" title="Mostrar/ocultar contraseña">
                                    <i class="bi bi-eye" id="iconoOjo"></i>
                                </button>
                            </div>

                            <!-- Opciones: Recordar Usuario y Enlace de Ayuda -->
                            <div class="d-flex justify-content-between align-items-center mb-4 small">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkRecordar">
                                    <label class="form-check-label text-secondary" for="checkRecordar">
                                        Recordar usuario
                                    </label>
                                </div>
                                <span class="text-secondary" role="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Si olvidaste tu clave, acércate a la Coordinación">
                                    <i class="bi bi-question-circle me-1"></i>Ayuda
                                </span>
                            </div>

                            <!-- Botón de Envío -->
                            <button type="submit" id="btnSubmitLogin" class="btn btn-success w-100 py-2 rounded-pill fw-semibold shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                            </button>

                        </form>

                    </div>

                    <!-- Pie de la Tarjeta -->
                    <div class="card-footer bg-transparent border-0 text-center pb-4 pt-0">
                        <small class="text-secondary small d-block">
                            Servicio Nacional de Aprendizaje SENA &bull; Regional Valle
                        </small>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Scripts Bootstrap 5.3 y Lógica de Login -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/login.js?v=<?= file_exists('public/js/login.js') ? filemtime('public/js/login.js') : time(); ?>"></script>
</body>

</html>