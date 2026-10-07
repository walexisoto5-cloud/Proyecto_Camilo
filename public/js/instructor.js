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

        // Confirmación para aprobar o rechazar excusas
        document.querySelectorAll('form[action*="procesar_excusa_instructor"]').forEach(form => {
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const estado = this.value;
                    const esAprobar = (estado === 'Aprobada');
                    Swal.fire({
                        title: esAprobar ? '¿Aprobar justificación médica?' : '¿Rechazar justificación?',
                        text: esAprobar ? 'La inasistencia quedará justificada para el aprendiz.' : 'La inasistencia se mantendrá sin justificar.',
                        icon: esAprobar ? 'success' : 'warning',
                        showCancelButton: true,
                        confirmButtonColor: esAprobar ? '#16a34a' : '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: esAprobar ? 'Sí, Aprobar' : 'Sí, Rechazar',
                        cancelButtonText: 'Cancelar',
                        background: '#161b22',
                        color: '#f0f6fc'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const inputHidden = document.createElement('input');
                            inputHidden.type = 'hidden';
                            inputHidden.name = 'estado';
                            inputHidden.value = estado;
                            form.appendChild(inputHidden);
                            form.submit();
                        }
                    });
                });
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
