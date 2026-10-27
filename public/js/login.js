// Control interactivo para la vista de Login SENA

document.addEventListener('DOMContentLoaded', function () {
    const formLogin = document.getElementById('formLogin');
    const inputUsuario = document.getElementById('nombre_usuario');
    const inputContrasena = document.getElementById('contrasena');
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const iconoOjo = document.getElementById('iconoOjo');
    const btnSubmit = document.getElementById('btnSubmitLogin');
    const checkRecordar = document.getElementById('checkRecordar');

    // 1. Cargar usuario recordado de localStorage si existe
    if (inputUsuario && checkRecordar) {
        const usuarioGuardado = localStorage.getItem('sena_usuario_recordado');
        if (usuarioGuardado) {
            inputUsuario.value = usuarioGuardado;
            checkRecordar.checked = true;
            if (inputContrasena) {
                inputContrasena.focus();
            }
        }
    }

    // 2. Alternar visibilidad de la contraseña
    if (btnTogglePassword && inputContrasena && iconoOjo) {
        btnTogglePassword.addEventListener('click', function () {
            const esPassword = (inputContrasena.getAttribute('type') === 'password');
            inputContrasena.setAttribute('type', esPassword ? 'text' : 'password');
            iconoOjo.className = esPassword ? 'bi bi-eye-slash text-success' : 'bi bi-eye';
            this.setAttribute('title', esPassword ? 'Ocultar contraseña' : 'Ver contraseña');
        });
    }

    // 3. Feedback visual al enviar formulario (Spinner)
    if (formLogin && btnSubmit) {
        formLogin.addEventListener('submit', function () {
            // Guardar usuario si el checkbox está activo
            if (checkRecordar && inputUsuario) {
                if (checkRecordar.checked) {
                    localStorage.setItem('sena_usuario_recordado', inputUsuario.value.trim());
                } else {
                    localStorage.removeItem('sena_usuario_recordado');
                }
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Verificando credenciales...';
        });
    }

    // 4. Inicializar tooltips de Bootstrap si están disponibles
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
    }
});
