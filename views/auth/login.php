<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Asistencia - SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
        }

        .card-login {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-sena {
            background-color: #39a900;
            border-color: #39a900;
            color: #fff;
            font-weight: 600;
        }

        .btn-sena:hover {
            background-color: #2e8800;
            border-color: #2e8800;
            color: #fff;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center p-3">

    <div class="container" style="max-width: 420px;">
        <div class="card card-login bg-white p-4">
            <div class="card-body">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-badge text-success fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Control de Asistencia</h4>
                </div>

                <!-- Alertas de error -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="/proyecto_Camilo_Asistencia/index.php?action=login" method="POST">

                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label text-secondary small fw-bold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-control border-start-0 bg-light" placeholder="Ej. admin" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="contrasena" class="form-label text-secondary small fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" name="contrasena" id="contrasena" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sena w-100 py-2 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </button>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>