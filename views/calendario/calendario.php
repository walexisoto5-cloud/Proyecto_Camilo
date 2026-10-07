<?php
$fichas = $fichas ?? [];
$nombreUsuario = $nombreUsuario ?? 'Usuario';
$rolUsuario = $rolUsuario ?? 'Administrador';

// Determinamos la ruta de retorno según el rol actual
$urlInicio = 'index.php?action=dashboard';
if ($rolUsuario === 'Instructor') {
    $urlInicio = 'index.php?action=portal_instructor';
} elseif ($rolUsuario === 'Aprendiz') {
    $urlInicio = 'index.php?action=portal_aprendiz';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Calendar - Cronograma SENA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/dashboard.css?v=<?= file_exists('public/css/dashboard.css') ? filemtime('public/css/dashboard.css') : time(); ?>">
    <link rel="stylesheet" href="public/css/calendario.css?v=<?= file_exists('public/css/calendario.css') ? filemtime('public/css/calendario.css') : time(); ?>">
</head>

<body style="background-color: #0d1117; color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <div class="dashboard-container" style="background-color: #0d1117; min-height: 100vh;">

        <!-- Sidebar unificado de navegación -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <nav class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100" style="width: 80px; position: fixed; top: 0; left: 0; z-index: 1000;">
                <a href="<?= htmlspecialchars($urlInicio); ?>" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Volver al Portal">
                    <i class="bi bi-house-door-fill fs-5 mb-1 text-success"></i>
                    <span style="font-size: 9px; line-height: 1;">Inicio</span>
                </a>

                <a href="index.php?action=escaner_rfid" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Lector RFID">
                    <i class="bi bi-upc-scan fs-5 mb-1 text-info"></i>
                    <span style="font-size: 9px; line-height: 1;">RFID</span>
                </a>

                <a href="index.php?action=calendario" class="nav-link text-white active bg-dark rounded d-flex flex-column align-items-center justify-content-center py-2 mb-2" title="Google Calendario">
                    <i class="bi bi-calendar3 fs-5 mb-1 text-primary"></i>
                    <span style="font-size: 9px; line-height: 1;">Calendario</span>
                </a>

                <?php if ($rolUsuario === 'Administrador'): ?>
                    <a href="index.php?action=portal_instructor" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Portal Instructor">
                        <i class="bi bi-person-video3 fs-5 mb-1 text-warning"></i>
                        <span style="font-size: 8px; line-height: 1;">Instructor</span>
                    </a>
                    <a href="index.php?action=portal_aprendiz" class="nav-link text-white d-flex flex-column align-items-center justify-content-center py-2 mb-2 rounded" title="Portal Aprendiz">
                        <i class="bi bi-person-badge fs-5 mb-1 text-info"></i>
                        <span style="font-size: 8px; line-height: 1;">Aprendiz</span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content" style="margin-left: 80px; padding: 25px; flex-grow: 1; background-color: #0d1117;">

            <!-- Barra Superior Institucional -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom border-secondary">
                <div class="d-flex align-items-center gap-3">
                    <div class="google-brand-icon">
                        <svg viewBox="0 0 24 24" width="22" height="22">
                            <path fill="#4285F4" d="M19.5 3h-15C3.67 3 3 3.67 3 4.5v15c0 .83.67 1.5 1.5 1.5h15c.83 0 1.5-.67 1.5-1.5v-15c0-.83-.67-1.5-1.5-1.5zM18 18H6V8h12v10z"/>
                            <path fill="#34A853" d="M9 10h6v2H9z"/>
                            <path fill="#FBBC05" d="M9 13h4v2H9z"/>
                            <path fill="#EA4335" d="M6 5h12v2H6z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0">Google Calendar - Cronograma Institucional</h4>
                        <small class="text-white-50">Sincronización de clases, asistencias y actividades académicas SENA</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= htmlspecialchars($urlInicio); ?>" class="btn btn-outline-secondary rounded-pill px-3 text-white" style="border-color: #475569;">
                        <i class="bi bi-arrow-left me-1"></i> Volver a mi Panel
                    </a>
                    <button type="button" class="btn btn-outline-info rounded-pill px-3" id="btnSyncGoogle">
                        <i class="bi bi-arrow-repeat me-1"></i> Sincronizar Google
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEvento">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Evento
                    </button>
                </div>
            </div>

            <!-- Controles del Calendario (Navegación de Mes y Filtros) -->
            <div class="row g-4">

                <!-- Columna Izquierda: Panel de Filtros y Calendarios -->
                <div class="col-xl-3 col-lg-4">
                    <div class="calendar-sidebar p-3 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-white mb-0">
                                <i class="bi bi-funnel-fill text-success me-2"></i>Mis Calendarios
                            </h6>
                            <span class="badge bg-dark border border-secondary text-white-50"><?= htmlspecialchars($rolUsuario); ?></span>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="clases" id="filtroClases" checked>
                            <label class="form-check-label text-white small" for="filtroClases">
                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #16a34a;"></span>
                                Clases y Sesiones ADSO
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="asistencias" id="filtroAsistencias" checked>
                            <label class="form-check-label text-white small" for="filtroAsistencias">
                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #3b82f6;"></span>
                                Control de Asistencia RFID
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="entregas" id="filtroEntregas" checked>
                            <label class="form-check-label text-white small" for="filtroEntregas">
                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #eab308;"></span>
                                Entregas y Evaluaciones
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="comites" id="filtroComites" checked>
                            <label class="form-check-label text-white small" for="filtroComites">
                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #8b5cf6;"></span>
                                Comités y Reuniones
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="festivos" id="filtroFestivos" checked>
                            <label class="form-check-label text-white small" for="filtroFestivos">
                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #ef4444;"></span>
                                Festivos Nacionales
                            </label>
                        </div>

                        <hr class="border-secondary my-3">

                        <!-- Tarjeta de Estado de Conexión Google API -->
                        <div class="p-3 rounded bg-dark border border-secondary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="d-inline-block rounded-circle bg-success" style="width: 8px; height: 8px;"></span>
                                <span class="fw-semibold text-white small">Google Calendar API</span>
                            </div>
                            <p class="text-white-50 small mb-2" style="font-size: 0.78rem;">
                                Conectado en modo interfaz visual. Listo para enlazar credenciales OAuth2 y sincronizar eventos automáticamente.
                            </p>
                            <span class="badge bg-secondary text-white py-1 px-2 rounded-pill" style="font-size: 0.7rem;">
                                Sincronización Lista
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Columna Derecha: Vista del Calendario Principal -->
                <div class="col-xl-9 col-lg-8">
                    <div class="calendar-main-card p-3 shadow-sm">

                        <!-- Barra de controles de mes -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3" id="btnMesAnterior" style="border-color: #475569;">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3" id="btnMesSiguiente" style="border-color: #475569;">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="btnHoy">
                                    Hoy
                                </button>
                                <h5 class="fw-bold text-white mb-0 ms-2" id="labelMesActual">Octubre 2026</h5>
                            </div>

                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success px-3 active">Mes</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white px-3" style="border-color: #475569;">Semana</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white px-3" style="border-color: #475569;">Día</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-white px-3" style="border-color: #475569;">Agenda</button>
                            </div>
                        </div>

                        <!-- Encabezado de Días de la Semana -->
                        <div class="calendar-grid-header">
                            <div>Lun</div>
                            <div>Mar</div>
                            <div>Mié</div>
                            <div>Jue</div>
                            <div>Vie</div>
                            <div>Sáb</div>
                            <div>Dom</div>
                        </div>

                        <!-- Grid de Días del Mes -->
                        <div class="calendar-grid-body">

                            <!-- Días del mes anterior (Sep 28, 29, 30) -->
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">28</span>
                            </div>
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">29</span>
                            </div>
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">30</span>
                            </div>

                            <!-- Días de Octubre -->
                            <div class="calendar-day-cell">
                                <span class="day-number">1</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">2</span>
                                <span class="event-badge event-sena" title="Sesión de Formación ADSO">08:00 AM ADSO Clase</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">3</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">4</span>
                            </div>

                            <!-- Semana 2 -->
                            <div class="calendar-day-cell">
                                <span class="day-number">5</span>
                                <span class="event-badge event-google" title="Toma de Asistencia con Carnet RFID">07:30 AM RFID Entrada</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">6</span>
                                <span class="event-badge event-sena" title="Algoritmos y Estructuras de Datos">08:00 AM Programación</span>
                            </div>
                            <!-- Día actual: 7 de Octubre de 2026 -->
                            <div class="calendar-day-cell today">
                                <span class="day-number">7 <span class="badge bg-success rounded-pill" style="font-size: 0.6rem;">HOY</span></span>
                                <span class="event-badge event-sena" title="Llamado a lista y asistencia activa">08:00 AM Sesión ADSO</span>
                                <span class="event-badge event-google" title="Sincronización Google Meet">10:30 AM Meet Proyecto</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">8</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">9</span>
                                <span class="event-badge event-warning-custom" title="Plazo entrega Guía de Aprendizaje">11:59 PM Evidencia 2</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">10</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">11</span>
                            </div>

                            <!-- Semana 3 -->
                            <div class="calendar-day-cell">
                                <span class="day-number">12</span>
                                <span class="event-badge event-danger-custom" title="Día Festivo Nacional">Festivo Nacional</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">13</span>
                                <span class="event-badge event-sena">08:00 AM ADSO Clase</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">14</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">15</span>
                                <span class="event-badge event-purple" title="Comité de Seguimiento Ficha 3234082">02:00 PM Comité ADSO</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">16</span>
                                <span class="event-badge event-sena">08:00 AM Taller Bases Datos</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">17</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">18</span>
                            </div>

                            <!-- Semana 4 -->
                            <div class="calendar-day-cell">
                                <span class="day-number">19</span>
                                <span class="event-badge event-google">07:30 AM RFID Entrada</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">20</span>
                                <span class="event-badge event-sena">08:00 AM ADSO Clase</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">21</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">22</span>
                                <span class="event-badge event-warning-custom">11:59 PM Parcial Práctico</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">23</span>
                                <span class="event-badge event-sena">08:00 AM Clase Web</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">24</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">25</span>
                            </div>

                            <!-- Semana 5 -->
                            <div class="calendar-day-cell">
                                <span class="day-number">26</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">27</span>
                                <span class="event-badge event-sena">08:00 AM ADSO Clase</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">28</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">29</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">30</span>
                                <span class="event-badge event-purple">04:00 PM Cierre de Mes</span>
                            </div>
                            <div class="calendar-day-cell">
                                <span class="day-number">31</span>
                            </div>

                            <!-- Noviembre -->
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">1</span>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- Modal para Agregar Nuevo Evento -->
    <div class="modal fade" id="modalNuevoEvento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-content-dark">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-calendar-plus text-success me-2"></i>Nuevo Evento / Clase en Calendario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevoEvento">
                    <div class="modal-body py-4">
                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Título del Evento / Sesión</label>
                            <input type="text" class="form-control form-control-dark text-white" id="eventoTitulo" placeholder="Ej. Sesión Programación Web ADSO" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Fecha</label>
                                <input type="date" class="form-control form-control-dark text-white" id="eventoFecha" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6 text-start">
                                <label class="form-label text-white small fw-semibold">Hora de Inicio</label>
                                <input type="time" class="form-control form-control-dark text-white" id="eventoHora" value="08:00" required>
                            </div>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Categoría / Tipo de Evento</label>
                            <select class="form-select form-select-dark text-white" id="eventoTipo" required>
                                <option value="sena">Clase / Formación SENA (Verde)</option>
                                <option value="google">Reunión Google Meet (Azul)</option>
                                <option value="warning">Entrega / Evaluación (Amarillo)</option>
                                <option value="purple">Comité / Administrativo (Púrpura)</option>
                            </select>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Ficha Asociada (Opcional)</label>
                            <select class="form-select form-select-dark text-white" id="eventoFicha">
                                <option value="">Todas las Fichas / General</option>
                                <?php if (!empty($fichas)): ?>
                                    <?php foreach ($fichas as $f): ?>
                                        <option value="<?= htmlspecialchars($f['id_ficha']); ?>">
                                            Ficha <?= htmlspecialchars($f['numero_ficha']); ?> - <?= htmlspecialchars($f['nombre_programa'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label text-white small fw-semibold">Descripción o Enlace Meet</label>
                            <textarea class="form-control form-control-dark text-white" id="eventoDescripcion" rows="2" placeholder="Detalles de la sesión, temas a tratar o enlace virtual..."></textarea>
                        </div>

                        <div class="p-3 rounded bg-dark border border-secondary text-light small text-start">
                            <i class="bi bi-info-circle text-info me-1"></i> Este evento se registrará en la vista del cronograma y quedará listo para sincronización con Google Calendar.
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal" style="border-color: #64748b;">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Guardar Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap & SweetAlert2 & JS Modular -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="public/js/calendario.js?v=<?= file_exists('public/js/calendario.js') ? filemtime('public/js/calendario.js') : time(); ?>"></script>
</body>

</html>
