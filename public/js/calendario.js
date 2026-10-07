// Control interactivo para la vista de Google Calendar

document.addEventListener('DOMContentLoaded', function () {
    const btnSyncGoogle = document.getElementById('btnSyncGoogle');
    const formNuevoEvento = document.getElementById('formNuevoEvento');
    const btnMesAnterior = document.getElementById('btnMesAnterior');
    const btnMesSiguiente = document.getElementById('btnMesSiguiente');
    const btnHoy = document.getElementById('btnHoy');
    const labelMesActual = document.getElementById('labelMesActual');

    // Botón de sincronización con Google Calendar
    if (btnSyncGoogle) {
        btnSyncGoogle.addEventListener('click', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Google Calendar API',
                    html: `
                        <p class="text-start mb-2">La vista y los componentes visuales están 100% listos y adaptados a la paleta oscura.</p>
                        <p class="text-start mb-0 text-white-50 small">En el siguiente paso podemos enlazar tus credenciales OAuth2 / Client ID de Google Cloud para sincronizar las clases y asistencias en vivo.</p>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#16a34a',
                    background: '#161b22',
                    color: '#f0f6fc'
                });
            } else {
                alert('La vista de Google Calendar está lista para ser conectada con la API.');
            }
        });
    }

    // Guardar nuevo evento desde el modal
    if (formNuevoEvento) {
        formNuevoEvento.addEventListener('submit', function (e) {
            e.preventDefault();
            const modalEl = document.getElementById('modalNuevoEvento');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            const titulo = document.getElementById('eventoTitulo').value;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Evento Programado!',
                    text: `El evento "${titulo}" ha sido registrado en la vista del cronograma.`,
                    icon: 'success',
                    confirmButtonColor: '#16a34a',
                    background: '#161b22',
                    color: '#f0f6fc'
                });
            }

            formNuevoEvento.reset();
        });
    }

    // Clic en los eventos para ver detalles
    document.querySelectorAll('.event-badge').forEach(badge => {
        badge.addEventListener('click', function () {
            const texto = this.innerText;
            const tituloCompleto = this.getAttribute('title') || texto;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Detalle del Evento',
                    html: `
                        <div class="text-start p-2 rounded bg-dark border border-secondary mb-2">
                            <strong class="text-white d-block">${tituloCompleto}</strong>
                            <small class="text-white-50">Horario: ${texto}</small>
                        </div>
                        <p class="text-start text-white-50 small mb-0">Asociado al calendario institucional de formación SENA.</p>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#16a34a',
                    background: '#161b22',
                    color: '#f0f6fc'
                });
            }
        });
    });

    // Navegación de meses
    let mesActualIndex = 9; // Octubre (base 0)
    const meses = [
        'Enero 2026', 'Febrero 2026', 'Marzo 2026', 'Abril 2026',
        'Mayo 2026', 'Junio 2026', 'Julio 2026', 'Agosto 2026',
        'Septiembre 2026', 'Octubre 2026', 'Noviembre 2026', 'Diciembre 2026'
    ];

    if (btnMesAnterior && labelMesActual) {
        btnMesAnterior.addEventListener('click', function () {
            if (mesActualIndex > 0) {
                mesActualIndex--;
                labelMesActual.innerText = meses[mesActualIndex];
            }
        });
    }

    if (btnMesSiguiente && labelMesActual) {
        btnMesSiguiente.addEventListener('click', function () {
            if (mesActualIndex < meses.length - 1) {
                mesActualIndex++;
                labelMesActual.innerText = meses[mesActualIndex];
            }
        });
    }

    if (btnHoy && labelMesActual) {
        btnHoy.addEventListener('click', function () {
            mesActualIndex = 9; // Octubre 2026
            labelMesActual.innerText = meses[mesActualIndex];
        });
    }

    // Filtros de categorías
    const checkboxes = document.querySelectorAll('.calendar-sidebar input[type="checkbox"]');
    checkboxes.forEach(chk => {
        chk.addEventListener('change', function () {
            const val = this.value;
            let selector = '';
            if (val === 'clases') selector = '.event-sena';
            if (val === 'asistencias') selector = '.event-google';
            if (val === 'entregas') selector = '.event-warning-custom';
            if (val === 'comites') selector = '.event-purple';
            if (val === 'festivos') selector = '.event-danger-custom';

            if (selector) {
                document.querySelectorAll(selector).forEach(el => {
                    el.style.display = chk.checked ? 'block' : 'none';
                });
            }
        });
    });
});
