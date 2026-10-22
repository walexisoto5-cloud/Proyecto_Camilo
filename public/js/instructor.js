// Control interactivo para el panel de asistencia del instructor

document.addEventListener('DOMContentLoaded', function () {
    const btnMarcarTodos = document.getElementById('btnMarcarTodosPresente');

    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function () {
            marcarTodosPresente();
        });
    }

    // Configuración base de SweetAlert2 si está disponible
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            background: '#161b22',
            color: '#f0f6fc',
            customClass: {
                popup: 'border border-secondary shadow-lg'
            }
        });

        // Lectura de parámetros de URL para notificaciones
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('guardado')) {
            Toast.fire({
                icon: 'success',
                title: '¡Asistencia Guardada!',
                text: 'El llamado a lista y las novedades se registraron correctamente.',
                timer: 3500,
                showConfirmButton: false
            });
        }

        if (urlParams.get('msg') === 'excusa_actualizada') {
            Toast.fire({
                icon: 'info',
                title: 'Novedad Actualizada',
                text: 'El estado de la excusa médica se ha procesado con éxito.',
                timer: 3500,
                showConfirmButton: false
            });
        } else if (urlParams.has('msg')) {
            Toast.fire({
                icon: 'success',
                title: 'Notificación',
                text: urlParams.get('msg'),
                timer: 4500,
                showConfirmButton: false
            });
        }

        if (urlParams.has('error')) {
            Toast.fire({
                icon: 'error',
                title: 'Atención',
                text: urlParams.get('error'),
                timer: 4500,
                showConfirmButton: false
            });
        }

        // Confirmación de cierre de sesión
        document.querySelectorAll('a[href*="action=logout"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const destUrl = this.getAttribute('href');
                Swal.fire({
                    title: '¿Deseas cerrar sesión?',
                    text: "Tu sesión actual de instructor finalizará de forma segura.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar',
                    background: '#161b22',
                    color: '#f0f6fc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = destUrl;
                    }
                });
            });
        });

        // Confirmación antes de guardar el llamado a lista
        const formLista = document.getElementById('formLlamadoLista');
        if (formLista) {
            formLista.addEventListener('submit', function (e) {
                if (this.dataset.confirmed === 'true') return;
                e.preventDefault();
                Swal.fire({
                    title: '¿Guardar Asistencia de la Sesión?',
                    text: "Se registrarán las asistencias, retardos y ausencias de todos los aprendices.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-save me-1"></i> Sí, guardar',
                    cancelButtonText: 'Revisar de nuevo',
                    background: '#161b22',
                    color: '#f0f6fc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formLista.dataset.confirmed = 'true';
                        formLista.submit();
                    }
                });
            });
        }

        // Confirmación para aprobar o rechazar excusas con motivo reglamentario
        document.querySelectorAll('form[action*="procesar_excusa_instructor"]').forEach(form => {
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const estado = this.value;
                    const esAprobar = (estado === 'Aprobada');

                    if (esAprobar) {
                        Swal.fire({
                            title: '¿Aprobar justificación médica?',
                            text: 'La inasistencia quedará marcada como justificada para el aprendiz.',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#16a34a',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Sí, Aprobar',
                            cancelButtonText: 'Cancelar',
                            background: '#161b22',
                            color: '#f0f6fc'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const inputHidden = document.createElement('input');
                                inputHidden.type = 'hidden';
                                inputHidden.name = 'estado';
                                inputHidden.value = 'Aprobada';
                                form.appendChild(inputHidden);
                                form.submit();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: '¿Rechazar justificación?',
                            text: 'Indica el motivo del rechazo según el reglamento SENA:',
                            input: 'text',
                            inputPlaceholder: 'Ej. Radicada fuera de los 3 días hábiles / Soporte no válido',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Sí, Rechazar',
                            cancelButtonText: 'Cancelar',
                            background: '#161b22',
                            color: '#f0f6fc',
                            inputValidator: (value) => {
                                if (!value || !value.trim()) {
                                    return 'Debes ingresar un motivo breve para rechazar la excusa.';
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const inputHidden = document.createElement('input');
                                inputHidden.type = 'hidden';
                                inputHidden.name = 'estado';
                                inputHidden.value = 'Rechazada';
                                form.appendChild(inputHidden);

                                const inputMotivo = document.createElement('input');
                                inputMotivo.type = 'hidden';
                                inputMotivo.name = 'motivo_rechazo';
                                inputMotivo.value = result.value.trim();
                                form.appendChild(inputMotivo);

                                form.submit();
                            }
                        });
                    }
                });
            });
        });
    }

    // Filtro interactivo para el historial de excusas del instructor
    const contenedorFiltros = document.getElementById('filtroExcusasInstructor');
    if (contenedorFiltros) {
        const botones = contenedorFiltros.querySelectorAll('button[data-filtro]');
        const itemsExcusas = document.querySelectorAll('#listaExcusasInstructor .excusa-item-card');

        botones.forEach(btn => {
            btn.addEventListener('click', function () {
                botones.forEach(b => b.classList.remove('active', 'text-white'));
                this.classList.add('active', 'text-white');

                const filtro = (this.getAttribute('data-filtro') || 'todas').toLowerCase();

                itemsExcusas.forEach(item => {
                    const estado = (item.getAttribute('data-estado') || '').toLowerCase();
                    if (filtro === 'todas' || estado === filtro) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                    }
                });
            });
        });
    }

    // Visor Integrado de Soportes Médicos (PDF / Imagen)
    const modalVisorEl = document.getElementById('modalVisorSoporte');
    if (modalVisorEl && typeof bootstrap !== 'undefined') {
        const bsModalVisor = new bootstrap.Modal(modalVisorEl);
        const tituloAprendiz = document.getElementById('visorTituloAprendiz');
        const subtitulo = document.getElementById('visorSubtitulo');
        const obsMotivo = document.getElementById('visorObservacionMotivo');
        const enlaceExterno = document.getElementById('visorEnlaceExterno');
        const contenedorPdf = document.getElementById('visorContenedorPdf');
        const iframePdf = document.getElementById('visorIframePdf');
        const contenedorImg = document.getElementById('visorContenedorImg');
        const elementoImg = document.getElementById('visorElementoImg');
        const cargando = document.getElementById('visorCargando');

        document.querySelectorAll('.btn-ver-soporte').forEach(btn => {
            btn.addEventListener('click', function () {
                const archivo = this.getAttribute('data-archivo') || '';
                const aprendiz = this.getAttribute('data-aprendiz') || 'Aprendiz';
                const fecha = this.getAttribute('data-fecha') || '';
                const motivo = this.getAttribute('data-motivo') || '';

                if (tituloAprendiz) tituloAprendiz.innerHTML = `<i class="bi bi-file-earmark-medical text-info me-2"></i>Evidencia: ${aprendiz}`;
                if (subtitulo) subtitulo.textContent = `Fecha de falta: ${fecha}`;
                if (obsMotivo) obsMotivo.textContent = motivo ? `"${motivo}"` : '';
                if (enlaceExterno) enlaceExterno.href = archivo;

                const ext = archivo.split('.').pop().toLowerCase();
                if (cargando) cargando.classList.remove('d-none');
                if (contenedorPdf) contenedorPdf.classList.add('d-none');
                if (contenedorImg) contenedorImg.classList.add('d-none');

                setTimeout(() => {
                    if (cargando) cargando.classList.add('d-none');
                    if (ext === 'pdf') {
                        if (iframePdf) iframePdf.src = archivo;
                        if (contenedorPdf) contenedorPdf.classList.remove('d-none');
                    } else {
                        if (elementoImg) elementoImg.src = archivo;
                        if (contenedorImg) contenedorImg.classList.remove('d-none');
                    }
                }, 200);

                bsModalVisor.show();
            });
        });

        modalVisorEl.addEventListener('hidden.bs.modal', function () {
            if (iframePdf) iframePdf.src = '';
            if (elementoImg) elementoImg.src = '';
        });
    }

    // Modal Remisión a Comité de Evaluación y Seguimiento
    const modalRemitirEl = document.getElementById('modalRemitirComite');
    if (modalRemitirEl && typeof bootstrap !== 'undefined') {
        const bsModalRemitir = new bootstrap.Modal(modalRemitirEl);
        const inputId = document.getElementById('comiteIdAprendiz');
        const inputFaltas = document.getElementById('comiteTotalFaltas');
        const inputPorcentaje = document.getElementById('comitePorcentajeFaltas');
        const nombreEl = document.getElementById('comiteNombreAprendiz');
        const docEl = document.getElementById('comiteDocAprendiz');
        const badgeFaltas = document.getElementById('comiteBadgeFaltas');
        const motivoEl = document.getElementById('comiteMotivo');

        document.querySelectorAll('.btn-remitir-comite').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const doc = this.getAttribute('data-doc');
                const faltas = this.getAttribute('data-faltas');
                const porcentaje = this.getAttribute('data-porcentaje');

                if (inputId) inputId.value = id;
                if (inputFaltas) inputFaltas.value = faltas;
                if (inputPorcentaje) inputPorcentaje.value = porcentaje;
                if (nombreEl) nombreEl.textContent = nombre;
                if (docEl) docEl.textContent = doc;
                if (badgeFaltas) badgeFaltas.textContent = `${faltas} faltas (${porcentaje}%)`;
                if (motivoEl) {
                    motivoEl.value = `El aprendiz acumula ${faltas} inasistencias injustificadas (${porcentaje}% de inasistencia), superando los límites establecidos en el Reglamento del Aprendiz SENA para citación a Comité de Evaluación y Seguimiento.`;
                }

                bsModalRemitir.show();
            });
        });
    }

});

/**
 * Marca como "Presente" a todos los aprendices del listado con un solo clic.
 */
function marcarTodosPresente() {
    const radiosPresente = document.querySelectorAll('.radio-presente');
    radiosPresente.forEach(radio => {
        radio.checked = true;
    });
}
