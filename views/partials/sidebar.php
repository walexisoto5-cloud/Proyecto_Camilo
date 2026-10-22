<?php
/**
 * Barra lateral (Sidebar) unificada y modular para el sistema de Asistencia SENA.
 * 
 * Variables opcionales:
 * - $activo: 'dashboard', 'escaner', 'calendario', 'lista', 'excusas', 'historial', 'mis_excusas', 'gestion', 'reportes'
 * - $rol: 'Administrador', 'Instructor', 'Aprendiz' (por defecto toma de $_SESSION['rol'])
 */

$activo = $activo ?? '';
$rol = $rol ?? ($_SESSION['rol'] ?? 'Administrador');
$esAdmin = ($rol === 'Administrador');
$esInstructor = ($rol === 'Instructor');
$esAprendiz = ($rol === 'Aprendiz');

if (!function_exists('renderItemNav')) {
    function renderItemNav(string $idActual, string $idItem, string $url, string $icono, string $texto, string $colorIcono = '', string $dataModal = ''): string
    {
        $esActivo = ($idActual === $idItem);
        $claseActivo = $esActivo ? 'active bg-dark rounded shadow-sm' : '';
        $attrModal = !empty($dataModal) ? ' data-bs-toggle="modal" data-bs-target="' . htmlspecialchars($dataModal) . '"' : '';

        return '
        <a href="' . htmlspecialchars($url) . '" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded ' . $claseActivo . '"' . $attrModal . ' title="' . htmlspecialchars($texto) . '">
            <i class="bi ' . htmlspecialchars($icono) . ' fs-5 mb-1 ' . htmlspecialchars($colorIcono) . '"></i>
            <span class="sidebar-item-text">' . htmlspecialchars($texto) . '</span>
        </a>';
    }
}
?>

<aside class="sidebar">
    <nav class="d-flex flex-column align-items-center flex-shrink-0 p-2 bg-dark text-white vh-100 sidebar-nav-container">
        
        <!-- Logo Institucional SENA -->
        <div class="my-2 text-center">
            <a href="<?= htmlspecialchars(function_exists('rutaPortalPorRol') ? rutaPortalPorRol($rol) : 'index.php?action=dashboard'); ?>" class="text-decoration-none">
                <i class="bi bi-shield-check text-success fs-3" title="Sistema Asistencia SENA"></i>
            </a>
        </div>
        <hr class="w-75 my-2 border-secondary opacity-50">

        <!-- Navegación según Rol -->
        <div class="w-100 d-flex flex-column align-items-center">

            <?php if ($esAdmin): ?>
                <!-- ITEMS ADMINISTRADOR -->
                <?= renderItemNav($activo, 'dashboard', 'index.php?action=dashboard', 'bi-grid-fill', 'Inicio', 'text-success'); ?>
                <?= renderItemNav($activo, 'gestion_aprendices', 'index.php?action=gestion_aprendices', 'bi-people-fill', 'Aprendices'); ?>
                <?= renderItemNav($activo, 'gestion_instructores', 'index.php?action=gestion_instructores', 'bi-person-video3', 'Instructores'); ?>
                <?= renderItemNav($activo, 'gestion_fichas', 'index.php?action=gestion_fichas', 'bi-journal-text', 'Fichas'); ?>
                <?= renderItemNav($activo, 'gestion_excusas', 'index.php?action=gestion_excusas', 'bi-envelope-paper', 'Excusas'); ?>
                <?= renderItemNav($activo, 'gestion_comite', 'index.php?action=gestion_comite', 'bi-gavel', 'Comité', 'text-warning'); ?>
                <?= renderItemNav($activo, 'escaner', 'index.php?action=escaner_rfid', 'bi-upc-scan', 'RFID', 'text-info'); ?>
                <?= renderItemNav($activo, 'calendario', 'index.php?action=calendario', 'bi-calendar3', 'Calendario', 'text-primary'); ?>

            <?php elseif ($esInstructor): ?>
                <!-- ITEMS INSTRUCTOR -->
                <?= renderItemNav($activo, 'dashboard', 'index.php?action=portal_instructor', 'bi-person-video3', 'Inicio', 'text-success'); ?>
                <?= renderItemNav($activo, 'lista', 'index.php?action=portal_instructor#formLlamadoLista', 'bi-check2-square', 'Lista', 'text-info'); ?>
                <?= renderItemNav($activo, 'escaner', 'index.php?action=escaner_rfid', 'bi-upc-scan', 'RFID', 'text-primary'); ?>
                <?= renderItemNav($activo, 'excusas', 'index.php?action=portal_instructor#panel-excusas', 'bi-folder2-open', 'Excusas', 'text-warning'); ?>
                <?= renderItemNav($activo, 'calendario', 'index.php?action=calendario', 'bi-calendar3', 'Calendario', 'text-primary'); ?>

            <?php else: ?>
                <!-- ITEMS APRENDIZ -->
                <?= renderItemNav($activo, 'dashboard', 'index.php?action=portal_aprendiz', 'bi-house-door-fill', 'Inicio', 'text-success'); ?>
                
                <?php if ($activo === 'dashboard' || $activo === 'portal_aprendiz'): ?>
                    <?= renderItemNav($activo, 'radicar_excusa', '#modalSubirExcusa', 'bi-file-earmark-medical-fill', 'Radicar', 'text-info', '#modalSubirExcusa'); ?>
                <?php else: ?>
                    <?= renderItemNav($activo, 'radicar_excusa', 'index.php?action=portal_aprendiz', 'bi-file-earmark-medical-fill', 'Radicar', 'text-info'); ?>
                <?php endif; ?>

                <?= renderItemNav($activo, 'historial', 'index.php?action=portal_aprendiz#seccion-historial', 'bi-clock-history', 'Historial', 'text-secondary'); ?>
                <?= renderItemNav($activo, 'mis_excusas', 'index.php?action=portal_aprendiz#seccion-excusas', 'bi-folder2-open', 'Excusas', 'text-warning'); ?>
                <?= renderItemNav($activo, 'calendario', 'index.php?action=calendario', 'bi-calendar3', 'Calendario', 'text-primary'); ?>

            <?php endif; ?>

        </div>

    </nav>
</aside>
