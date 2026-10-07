// Control interactivo para el panel de administración

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;

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
});
