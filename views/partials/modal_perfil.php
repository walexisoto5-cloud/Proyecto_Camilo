<?php
// Modal modular del perfil de usuario y administrador con botón de cerrar sesión
$perfilNombre = $nombreUsuario ?? ($_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador'));
$perfilRol = $rolUsuario ?? ($_SESSION['rol'] ?? 'Administrador');
?>
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg modal-content-dark card-dark-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-person-badge-fill me-2 text-success"></i>Información del Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="modal-avatar-container">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars((string)$perfilNombre); ?></h4>
                <p class="text-light small mb-3"><?= htmlspecialchars((string)$perfilRol); ?> del Sistema</p>
            </div>

            <a class="dropdown-item text-danger fw-bold text-center py-3 w-100 border-top border-secondary modal-logout-link"
                href="index.php?action=logout">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
