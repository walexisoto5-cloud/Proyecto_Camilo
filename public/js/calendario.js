// Control interactivo para el Calendario SENA y Horario de Formación

document.addEventListener('DOMContentLoaded', function () {
    const formNuevoEvento = document.getElementById('formNuevoEvento');
    const btnMesAnterior = document.getElementById('btnMesAnterior');
    const btnMesSiguiente = document.getElementById('btnMesSiguiente');
    const btnHoy = document.getElementById('btnHoy');
    const labelMesActual = document.getElementById('labelMesActual');

    // Botones de pestañas entre vista Mes, Horario Semanal y Lista
    const btnTabMes = document.getElementById('btnTabMes');
    const btnTabSemana = document.getElementById('btnTabSemana');
    const btnTabAgenda = document.getElementById('btnTabAgenda');

    const vistaMes = document.getElementById('vistaMesContainer');
    const vistaSemana = document.getElementById('vistaSemanaContainer');
    const vistaAgenda = document.getElementById('vistaAgendaContainer');

    function activarTab(tabActivo, vistaActiva) {
        [btnTabMes, btnTabSemana, btnTabAgenda].forEach(btn => {
            if (btn) {
                btn.classList.remove('btn-success', 'active');
                btn.classList.add('btn-outline-secondary', 'text-white');
            }
        });
        if (tabActivo) {
            tabActivo.classList.remove('btn-outline-secondary');
            tabActivo.classList.add('btn-success', 'active');
        }

        [vistaMes, vistaSemana, vistaAgenda].forEach(v => {
            if (v) v.style.display = 'none';
        });
        if (vistaActiva) {
            vistaActiva.style.display = 'block';
        }
    }

    if (btnTabMes) {
        btnTabMes.addEventListener('click', () => activarTab(btnTabMes, vistaMes));
    }
    if (btnTabSemana) {
        btnTabSemana.addEventListener('click', () => activarTab(btnTabSemana, vistaSemana));
    }
    if (btnTabAgenda) {
        btnTabAgenda.addEventListener('click', () => activarTab(btnTabAgenda, vistaAgenda));
    }

    // 1. Mensajes SweetAlert2 según parámetros de URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const bloques = urlParams.get('bloques');
    const instructores = urlParams.get('instructores');
    const detalleError = urlParams.get('err');

    if (msg && typeof Swal !== 'undefined') {
        if (msg === 'importado_ok') {
            activarTab(btnTabSemana, vistaSemana);
            Swal.fire({
                icon: 'success',
                title: '¡Horario Importado al Calendario!',
                html: `Se importaron exitosamente <b>${bloques || 0}</b> bloques de formación.<br>Se procesaron/crearon <b>${instructores || 0}</b> instructores en la base de datos.`,
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#22c55e',
                confirmButtonText: 'Aceptar'
            });
        } else if (msg === 'bloque_agregado') {
            activarTab(btnTabSemana, vistaSemana);
            Swal.fire({
                icon: 'success',
                title: '¡Bloque Guardado!',
                text: 'El bloque de formación se ha agregado correctamente al calendario.',
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#22c55e',
                confirmButtonText: 'Aceptar'
            });
        } else if (msg === 'bloque_eliminado') {
            activarTab(btnTabAgenda, vistaAgenda);
            Swal.fire({
                icon: 'info',
                title: 'Bloque Removido',
                text: 'El bloque seleccionado fue eliminado del calendario.',
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Entendido'
            });
        } else if (msg === 'error_import') {
            Swal.fire({
                icon: 'error',
                title: 'Error al Importar Horario',
                text: detalleError || 'Verifica que el archivo sea un Excel (.xlsx) o CSV válido.',
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Cerrar'
            });
        } else if (msg === 'error_archivo') {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo Inválido',
                text: 'Por favor selecciona un archivo con extensión .xlsx o .csv',
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Reintentar'
            });
        } else if (msg === 'acceso_denegado') {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Restringido',
                text: 'La modificación y carga de horarios está reservada para Instructores y Administradores.',
                background: '#161b22',
                color: '#ffffff',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Entendido'
            });
        }

        // Limpiar parámetros para no repetir la alerta
        const idFichaVal = urlParams.get('id_ficha') ? '&id_ficha=' + urlParams.get('id_ficha') : '';
        window.history.replaceState({}, document.title, window.location.pathname + '?action=calendario' + idFichaVal);
    }

    // 2. Confirmación SweetAlert2 para eliminar bloques
    document.querySelectorAll('.btn-eliminar-calendario').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const dia = button.getAttribute('data-dia') || 'este bloque';
            const hora = button.getAttribute('data-hora') || '';
            const targetUrl = button.getAttribute('href');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar bloque del calendario?',
                    text: `¿Estás seguro de eliminar el horario ${dia} (${hora})?`,
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#161b22',
                    color: '#ffffff',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#475569',
                    confirmButtonText: '<i class="bi bi-trash"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed && targetUrl) {
                        window.location.href = targetUrl;
                    }
                });
            } else if (confirm(`¿Eliminar ${dia} (${hora})?`)) {
                window.location.href = targetUrl;
            }
        });
    });

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

    // 5. Clic en los días del calendario para ver el horario completo y sus instructores
    document.querySelectorAll('.celda-dia-click').forEach(cell => {
        cell.addEventListener('click', function () {
            const rawData = this.getAttribute('data-dia-info');
            if (!rawData) return;

            let info;
            try {
                info = JSON.parse(rawData);
            } catch (err) {
                console.error("Error parseando data-dia-info:", err);
                return;
            }

            const fecha = info.fecha || 'Fecha';
            const diaSemana = info.dia_semana || '';
            const ficha = info.ficha || 'Ficha';
            const bloques = info.bloques || [];

            let contenidoHtml = `
                <div class="text-start mb-3">
                    <span class="badge bg-success rounded-pill px-3 py-1 mb-2">${diaSemana}</span>
                    <h6 class="text-white-50 small mb-0"><i class="bi bi-journal-bookmark me-1 text-warning"></i>${ficha}</h6>
                </div>
            `;

            if (bloques.length === 0) {
                contenidoHtml += `
                    <div class="p-3 rounded bg-dark border border-secondary text-center text-white-50">
                        <i class="bi bi-calendar-x fs-2 text-warning d-block mb-1"></i>
                        No hay sesiones de formación programadas para este día (${diaSemana}).
                    </div>
                `;
            } else {
                contenidoHtml += `<div class="d-flex flex-column gap-2">`;
                
                bloques.forEach((b, index) => {
                    const horaIni = (b.entrada || '').substring(0, 5);
                    const horaFin = (b.salida || '').substring(0, 5);
                    const esPrimerBloque = (horaIni < '09');
                    const badgeColor = esPrimerBloque ? '#16a34a' : '#2563eb';
                    const iconColor = esPrimerBloque ? 'text-success' : 'text-primary';

                    contenidoHtml += `
                        <div class="p-3 rounded bg-dark border border-secondary text-start" style="border-left: 4px solid ${badgeColor} !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge" style="background-color: ${badgeColor}; color: #ffffff;">
                                    <i class="bi bi-clock me-1"></i>${horaIni} - ${horaFin}
                                </span>
                                <small class="text-white-50">${b.ambiente || 'Ambiente 204'}</small>
                            </div>
                            <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;">
                                <i class="bi bi-person-badge-fill ${iconColor} me-1"></i>${b.instructor_nombre || 'Por Asignar'}
                            </h6>
                            <p class="text-white-50 small mb-0" style="font-size: 0.8rem;">
                                <i class="bi bi-book-fill text-secondary me-1"></i>${b.competencia || 'Formación Técnica ADSO'}
                            </p>
                        </div>
                    `;

                    // Mostrar el receso entre bloques
                    if (index === 0 && bloques.length > 1) {
                        contenidoHtml += `
                            <div class="py-1 px-3 rounded text-center small fw-semibold" style="background-color: rgba(234, 179, 8, 0.12); border: 1px dashed #eab308; color: #fde047;">
                                ☕ 08:45 AM - 09:15 AM: Receso / Descanso de Formación
                            </div>
                        `;
                    }
                });

                contenidoHtml += `</div>`;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `<span class="text-white fs-5 fw-bold"><i class="bi bi-calendar-event text-success me-2"></i>Horario: ${fecha}</span>`,
                    html: contenidoHtml,
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#16a34a',
                    background: '#161b22',
                    color: '#ffffff',
                    width: '560px'
                });
            }
        });
    });

    // Clic en los badges pequeños dentro de la celda
    document.querySelectorAll('.event-badge').forEach(badge => {
        badge.addEventListener('click', function (e) {
            e.stopPropagation(); // Permitir que la celda principal maneje el clic o muestre el popup completo
            const parentCell = this.closest('.celda-dia-click');
            if (parentCell) {
                parentCell.click();
            }
        });
    });

    // 6. Navegación de meses
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

    // 7. Filtros de categorías
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

    // 8. Spinner al procesar Excel
    const formImportar = document.getElementById('formImportarHorario');
    if (formImportar) {
        formImportar.addEventListener('submit', () => {
            const btnSubmit = formImportar.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando Excel...';
            }
        });
    }
});
