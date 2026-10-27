// Control interactivo para el panel de administración

document.addEventListener('DOMContentLoaded', function () {
    // 1. Notificaciones y alertas con SweetAlert2
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            background: '#161b22',
            color: '#f0f6fc',
            customClass: {
                popup: 'border border-secondary shadow-lg'
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            Toast.fire({
                icon: 'error',
                title: 'Error de Validación',
                text: urlParams.get('error'),
                timer: 5000,
                showConfirmButton: false
            });
        } else if (urlParams.has('creado')) {
            Toast.fire({
                icon: 'success',
                title: '¡Operación Exitosa!',
                text: 'El registro se creó correctamente.',
                timer: 3500,
                showConfirmButton: false
            });
        } else if (urlParams.has('actualizado')) {
            Toast.fire({
                icon: 'info',
                title: 'Actualizado',
                text: 'Los cambios se guardaron correctamente.',
                timer: 3500,
                showConfirmButton: false
            });
        } else if (urlParams.has('eliminado')) {
            Toast.fire({
                icon: 'warning',
                title: 'Eliminado',
                text: 'El registro fue eliminado correctamente.',
                timer: 3500,
                showConfirmButton: false
            });
        }

        // Confirmación al cerrar sesión
        document.querySelectorAll('a[href*="action=logout"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const destUrl = this.getAttribute('href');
                Swal.fire({
                    title: '¿Deseas cerrar sesión?',
                    text: "Tu sesión administrativa se cerrará de forma segura.",
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

        // Confirmación al guardar fichas
        const formFicha = document.querySelector('form[action*="crear_ficha"]');
        if (formFicha) {
            formFicha.addEventListener('submit', function (e) {
                if (this.dataset.confirmed === 'true') return;
                e.preventDefault();
                Swal.fire({
                    title: '¿Registrar Nueva Ficha?',
                    text: "Se dará de alta la ficha en el sistema de formación.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Guardar Ficha',
                    cancelButtonText: 'Cancelar',
                    background: '#161b22',
                    color: '#f0f6fc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formFicha.dataset.confirmed = 'true';
                        formFicha.submit();
                    }
                });
            });
        }

        // Confirmación al guardar instructores
        const formInstructor = document.querySelector('form[action*="crear_instructor"]');
        if (formInstructor) {
            formInstructor.addEventListener('submit', function (e) {
                if (this.dataset.confirmed === 'true') return;
                e.preventDefault();
                Swal.fire({
                    title: '¿Registrar Instructor?',
                    text: "Se creará el usuario de instructor para gestión académica.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Guardar Instructor',
                    cancelButtonText: 'Cancelar',
                    background: '#161b22',
                    color: '#f0f6fc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formInstructor.dataset.confirmed = 'true';
                        formInstructor.submit();
                    }
                });
            });
        }

        // Confirmación al guardar aprendices
        const formAprendiz = document.querySelector('form[action*="crearAprendiz"]');
        if (formAprendiz) {
            formAprendiz.addEventListener('submit', function (e) {
                if (this.dataset.confirmed === 'true') return;
                e.preventDefault();
                Swal.fire({
                    title: '¿Registrar Aprendiz?',
                    text: "Se asociará el aprendiz a la ficha de formación seleccionada.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Guardar Aprendiz',
                    cancelButtonText: 'Cancelar',
                    background: '#161b22',
                    color: '#f0f6fc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formAprendiz.dataset.confirmed = 'true';
                        formAprendiz.submit();
                    }
                });
            });
        }
    }

    // 2. Desplazamiento suave y resalte hacia la sección de Marcajes en Dashboard
    const btnToggleMarcajes = document.getElementById('btnToggleMarcajesTop');
    if (btnToggleMarcajes) {
        btnToggleMarcajes.addEventListener('click', function() {
            const seccionEl = document.getElementById('seccionMarcajes');
            if (seccionEl) {
                seccionEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                const cardInner = seccionEl.querySelector('.dashboard-card');
                if (cardInner) {
                    cardInner.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
                    cardInner.style.borderColor = '#eab308';
                    cardInner.style.boxShadow = '0 0 18px rgba(234, 179, 8, 0.4)';
                    setTimeout(function() {
                        cardInner.style.borderColor = '#30363d';
                        cardInner.style.boxShadow = 'none';
                    }, 1800);
                }
            }
        });
    }

    // 3. Población de datos en modal de edición de Aprendices
    const modalEditarAp = document.getElementById('modalEditarAprendiz');
    if (modalEditarAp) {
        modalEditarAp.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const setVal = (id, attr) => {
                const el = document.getElementById(id);
                if (el) el.value = btn.getAttribute(attr) || '';
            };
            setVal('edit_id', 'data-id');
            setVal('edit_nombre', 'data-nombre');
            setVal('edit_apellido', 'data-apellido');
            setVal('edit_documento', 'data-documento');
            setVal('edit_telefono', 'data-telefono');
            setVal('edit_rfid', 'data-rfid');
            setVal('edit_ficha', 'data-ficha');
            setVal('edit_estado', 'data-estado');
        });
    }

    // 4. Población de datos en modal de edición de Fichas
    const modalEditFch = document.getElementById('modalEditarFicha');
    if (modalEditFch) {
        modalEditFch.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const setVal = (id, attr) => {
                const el = document.getElementById(id);
                if (el) el.value = btn.getAttribute(attr) || '';
            };
            setVal('edit_ficha_id', 'data-id');
            setVal('edit_ficha_numero', 'data-numero');
            setVal('edit_ficha_programa', 'data-programa');
            setVal('edit_ficha_jornada', 'data-jornada');
            setVal('edit_ficha_instructor', 'data-instructor');
        });
    }

    // 5. Población de datos en modal de edición de Instructores
    const modalEditIns = document.getElementById('modalEditarInstructor');
    if (modalEditIns) {
        modalEditIns.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const setVal = (id, attr) => {
                const el = document.getElementById(id);
                if (el) el.value = btn.getAttribute(attr) || '';
            };
            setVal('edit_instructor_id', 'data-id');
            setVal('edit_instructor_nombre', 'data-nombre');
            setVal('edit_instructor_apellido', 'data-apellido');
            setVal('edit_instructor_documento', 'data-documento');
            setVal('edit_instructor_telefono', 'data-telefono');
            setVal('edit_instructor_estado', 'data-estado');
        });
    }

    // 6. Población de datos en modal de gestión de Casos de Comité
    const modalGestionComite = document.getElementById('modalGestionarComite');
    if (modalGestionComite) {
        const poblarModalComite = (btn) => {
            if (!btn) return;
            const inputId = document.getElementById('gestionarIdComite');
            const nombreEl = document.getElementById('gestionarNombreAprendiz');
            const docEl = document.getElementById('gestionarDocAprendiz');
            const fichaEl = document.getElementById('gestionarFichaAprendiz');
            const motivoEl = document.getElementById('gestionarMotivoAprendiz');
            const estadoSelect = document.getElementById('gestionarEstado');
            const obsEl = document.getElementById('gestionarObservacion');

            if (inputId) inputId.value = btn.getAttribute('data-id') || '';
            if (nombreEl) nombreEl.textContent = btn.getAttribute('data-aprendiz') || '';
            if (docEl) docEl.textContent = btn.getAttribute('data-doc') || '---';
            if (fichaEl) fichaEl.textContent = btn.getAttribute('data-ficha') || '---';
            if (motivoEl) motivoEl.textContent = btn.getAttribute('data-motivo') || '---';
            if (estadoSelect) {
                const estadoActual = btn.getAttribute('data-estado') || 'Pendiente';
                // Si el caso está en Pendiente, preseleccionar Citado para que al guardar pase de estado automáticamente
                estadoSelect.value = (estadoActual === 'Pendiente') ? 'Citado' : estadoActual;
            }
            if (obsEl) obsEl.value = btn.getAttribute('data-obs') || '';
        };

        modalGestionComite.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget || document.querySelector('.btn-gestionar-caso:focus');
            poblarModalComite(btn);
        });

        document.querySelectorAll('.btn-gestionar-caso').forEach(btn => {
            btn.addEventListener('click', function () {
                poblarModalComite(this);
            });
        });
    }

    // 7. Descarte y control de persistencia de la Alerta de Comité en el Dashboard
    const alertaComite = document.getElementById('alertaComiteAdmin');
    if (alertaComite) {
        if (sessionStorage.getItem('sena_alerta_comite_descartada') === '1') {
            alertaComite.style.display = 'none';
        }

        const descartarAlerta = () => {
            sessionStorage.setItem('sena_alerta_comite_descartada', '1');
            fetch('index.php?action=descartar_alerta_comite', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(() => {});
        };

        const btnCerrar = document.getElementById('btnCerrarAlertaComite');
        if (btnCerrar) {
            btnCerrar.addEventListener('click', descartarAlerta);
        }

        const btnIr = document.getElementById('btnIrGestionComite');
        if (btnIr) {
            btnIr.addEventListener('click', descartarAlerta);
        }
    }
});
