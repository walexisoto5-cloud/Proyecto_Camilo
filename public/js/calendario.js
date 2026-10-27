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

    // 3. Guardar nuevo evento desde el modal
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

    // 4. Modal con detalles del día
    function mostrarModalDia(rawData) {
        let info;
        try {
            info = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
        } catch (err) {
            console.error("Error parseando data-dia-info:", err);
            return;
        }

        const fecha = info.fecha || 'Fecha';
        const diaSemana = info.dia_semana || '';
        const ficha = info.ficha || 'Ficha';
        const bloques = info.bloques || [];
        const festivo = info.festivo || '';

        let contenidoHtml = `
            <div class="text-start mb-3">
                <span class="badge bg-success rounded-pill px-3 py-1 mb-2">${diaSemana}</span>
                <h6 class="text-white-50 small mb-0"><i class="bi bi-journal-bookmark me-1 text-warning"></i>${ficha}</h6>
            </div>
        `;

        if (festivo) {
            contenidoHtml += `
                <div class="p-3 rounded bg-dark border border-danger text-center text-white mb-2">
                    <i class="bi bi-flag-fill fs-2 text-danger d-block mb-1"></i>
                    <h6 class="fw-bold text-danger mb-1">${festivo}</h6>
                    <small class="text-white-50">Día festivo oficial en Colombia. No hay formación presencial programada.</small>
                </div>
            `;
        } else if (bloques.length === 0) {
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
                const cardClass = esPrimerBloque ? 'modal-dia-bloque-card' : 'modal-dia-bloque-card bloque-segundo';
                const badgeClass = esPrimerBloque ? 'modal-dia-time-badge' : 'modal-dia-time-badge badge-segundo';
                const iconColor = esPrimerBloque ? 'text-success' : 'text-primary';

                contenidoHtml += `
                    <div class="p-3 rounded bg-dark border border-secondary text-start ${cardClass}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge ${badgeClass}">
                                <i class="bi bi-clock me-1"></i>${horaIni} - ${horaFin}
                            </span>
                            <small class="text-white-50">${b.ambiente || 'Ambiente 204'}</small>
                        </div>
                        <h6 class="text-white fw-bold mb-1 modal-dia-titulo">
                            <i class="bi bi-person-badge-fill ${iconColor} me-1"></i>${b.instructor_nombre || 'Por Asignar'}
                        </h6>
                        <p class="text-white-50 small mb-0 modal-dia-desc">
                            <i class="bi bi-book-fill text-secondary me-1"></i>${b.competencia || 'Formación Técnica ADSO'}
                        </p>
                    </div>
                `;

                // Mostrar el receso intermedio de 08:45 a 09:15
                if (index === 0 && bloques.length > 1) {
                    contenidoHtml += `
                        <div class="py-1 px-3 rounded text-center small fw-semibold modal-dia-receso">
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
    }

    // 5. Enlace de clics en las celdas y badges
    function enlazarEventosCeldas() {
        document.querySelectorAll('.celda-dia-click').forEach(cell => {
            cell.onclick = function () {
                const rawData = this.getAttribute('data-dia-info');
                if (!rawData) return;
                mostrarModalDia(rawData);
            };
        });

        document.querySelectorAll('.event-badge').forEach(badge => {
            badge.onclick = function (e) {
                e.stopPropagation();
                const parentCell = this.closest('.celda-dia-click');
                if (parentCell) {
                    parentCell.click();
                }
            };
        });

        aplicarFiltrosCategorias();
    }

    // 6. Configuración y renderizado dinámico de mes / año
    const config = window.CALENDARIO_CONFIG || {};
    let mesActual = parseInt(config.mes, 10) || (new Date().getMonth() + 1);
    let anioActual = parseInt(config.anio, 10) || new Date().getFullYear();
    const fichaId = config.fichaId || 0;
    const fichaTexto = config.fichaTexto || 'General';
    const horariosPorDia = config.horariosPorDia || {};
    const festivos = config.festivos || {};

    const mesesNombres = [
        '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    const diasNombreMap = {
        1: 'Lunes', 2: 'Martes', 3: 'Miércoles',
        4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 0: 'Domingo'
    };

    function renderCalendarioMes(mes, anio) {
        mesActual = mes;
        anioActual = anio;

        if (labelMesActual) {
            labelMesActual.innerText = `${mesesNombres[mesActual]} ${anioActual}`;
        }

        // Actualizar URL sin recargar
        const nuevaUrl = `index.php?action=calendario&id_ficha=${fichaId}&mes=${mesActual}&anio=${anioActual}`;
        window.history.replaceState({}, document.title, nuevaUrl);

        const gridBody = document.getElementById('gridCalendarioBody');
        if (!gridBody) return;

        gridBody.innerHTML = '';

        // Días del mes actual
        const totalDiasMes = new Date(anioActual, mesActual, 0).getDate();
        // Primer día de la semana (Lunes = 1 .. Domingo = 7)
        const diaSemanaInicioJs = new Date(anioActual, mesActual - 1, 1).getDay();
        const diaSemanaInicio = (diaSemanaInicioJs === 0) ? 7 : diaSemanaInicioJs;

        // Días del mes anterior
        const totalDiasMesAnterior = new Date(anioActual, mesActual - 1, 0).getDate();

        // 1. Celdas del mes anterior
        const celdasPrevias = diaSemanaInicio - 1;
        for (let k = celdasPrevias; k >= 1; k--) {
            const diaPrevNum = totalDiasMesAnterior - k + 1;
            const cell = document.createElement('div');
            cell.className = 'calendar-day-cell other-month';
            cell.innerHTML = `<span class="day-number">${diaPrevNum}</span>`;
            gridBody.appendChild(cell);
        }

        // 2. Días del mes actual
        const hoy = new Date();
        const diaHoyNum = hoy.getDate();
        const mesHoyNum = hoy.getMonth() + 1;
        const anioHoyNum = hoy.getFullYear();

        for (let dia = 1; dia <= totalDiasMes; dia++) {
            const mm = String(mesActual).padStart(2, '0');
            const dd = String(dia).padStart(2, '0');
            const fechaIso = `${anioActual}-${mm}-${dd}`;
            const dateObj = new Date(anioActual, mesActual - 1, dia);
            const numDiaSemanaJs = dateObj.getDay();
            const nombreDiaSemana = diasNombreMap[numDiaSemanaJs] || '';
            const bloquesDelDia = horariosPorDia[nombreDiaSemana] || [];
            const esHoy = (dia === diaHoyNum && mesActual === mesHoyNum && anioActual === anioHoyNum);
            const esFestivo = !!festivos[fechaIso];
            const nombreFestivo = esFestivo ? festivos[fechaIso] : '';

            const infoDia = {
                fecha: `${dd} de ${mesesNombres[mesActual]} de ${anioActual}`,
                dia_semana: nombreDiaSemana,
                bloques: bloquesDelDia,
                festivo: nombreFestivo,
                ficha: fichaTexto
            };

            const cell = document.createElement('div');
            cell.className = `calendar-day-cell ${esHoy ? 'today' : ''} celda-dia-click`;
            cell.setAttribute('data-dia-info', JSON.stringify(infoDia));
            cell.setAttribute('title', 'Haz clic para ver instructores y horario de este día');

            let innerHtml = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="day-number mb-0">${dia}</span>
                    ${esHoy ? '<span class="badge bg-success rounded-pill badge-today">HOY</span>' : ''}
                </div>
            `;

            if (esFestivo) {
                innerHtml += `
                    <div class="event-badge event-danger-custom mb-1" title="${nombreFestivo}">
                        <i class="bi bi-flag-fill me-1"></i> ${nombreFestivo}
                    </div>
                `;
            } else if (bloquesDelDia.length > 0) {
                bloquesDelDia.forEach(blk => {
                    const horaIni = (blk.entrada || '').substring(0, 5);
                    const claseEstilo = (horaIni < '09') ? 'event-sena' : 'event-blue event-google';
                    const primerNombre = blk.instructor_nombre ? blk.instructor_nombre.trim().split(' ')[0] : 'Clase';
                    const competencia = blk.competencia || 'Formación';
                    innerHtml += `
                        <div class="event-badge ${claseEstilo} mb-1" title="${blk.instructor_nombre || 'Instructor'} - ${competencia}">
                            <span class="fw-bold">${horaIni}</span> ${primerNombre}
                        </div>
                    `;
                });
            } else if (nombreDiaSemana === 'Domingo') {
                innerHtml += `<small class="text-white-50 text-descanso-sm">Descanso</small>`;
            }

            cell.innerHTML = innerHtml;
            gridBody.appendChild(cell);
        }

        // 3. Celdas del mes siguiente
        const totalCeldasRenderizadas = celdasPrevias + totalDiasMes;
        const celdasSiguientes = (7 - (totalCeldasRenderizadas % 7)) % 7;
        for (let s = 1; s <= celdasSiguientes; s++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day-cell other-month';
            cell.innerHTML = `<span class="day-number">${s}</span>`;
            gridBody.appendChild(cell);
        }

        enlazarEventosCeldas();
    }

    // Botones de navegación de mes
    if (btnMesAnterior) {
        btnMesAnterior.addEventListener('click', function () {
            let nuevoMes = mesActual - 1;
            let nuevoAnio = anioActual;
            if (nuevoMes < 1) {
                nuevoMes = 12;
                nuevoAnio--;
            }
            renderCalendarioMes(nuevoMes, nuevoAnio);
        });
    }

    if (btnMesSiguiente) {
        btnMesSiguiente.addEventListener('click', function () {
            let nuevoMes = mesActual + 1;
            let nuevoAnio = anioActual;
            if (nuevoMes > 12) {
                nuevoMes = 1;
                nuevoAnio++;
            }
            renderCalendarioMes(nuevoMes, nuevoAnio);
        });
    }

    if (btnHoy) {
        btnHoy.addEventListener('click', function () {
            const hoy = new Date();
            renderCalendarioMes(hoy.getMonth() + 1, hoy.getFullYear());
        });
    }

    // 7. Filtros de categorías
    function aplicarFiltrosCategorias() {
        const checkboxes = document.querySelectorAll('.calendar-sidebar input[type="checkbox"]');
        checkboxes.forEach(chk => {
            const val = chk.value;
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
    }

    const checkboxes = document.querySelectorAll('.calendar-sidebar input[type="checkbox"]');
    checkboxes.forEach(chk => {
        chk.addEventListener('change', aplicarFiltrosCategorias);
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

    // Inicializar listeners en la vista precargada
    enlazarEventosCeldas();
});
