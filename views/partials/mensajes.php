<?php
// Mensajes flash para notificaciones dinámicas en el sistema (alertas de éxito, aviso, error)
?>
<?php if (isset($_GET['creado'])): ?>
    <div class="alert alert-dismissible fade show py-2 px-3 small border-0 shadow-sm flash-alert-success" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Registro creado correctamente.
        <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['actualizado'])): ?>
    <div class="alert alert-dismissible fade show py-2 px-3 small border-0 shadow-sm flash-alert-info" role="alert">
        <i class="bi bi-pencil-square me-2"></i> Registro actualizado correctamente.
        <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['eliminado'])): ?>
    <div class="alert alert-dismissible fade show py-2 px-3 small border-0 shadow-sm flash-alert-warning" role="alert">
        <i class="bi bi-trash me-2"></i> Registro eliminado correctamente.
        <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['guardado'])): ?>
    <div class="alert alert-dismissible fade show py-2 px-3 small border-0 shadow-sm flash-alert-success" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Información guardada con éxito.
        <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-dismissible fade show py-2 px-3 small border-0 shadow-sm flash-alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>
