// Control interactivo para el portal del aprendiz

document.addEventListener('DOMContentLoaded', function () {
    // Inicialización dinámica de barras de progreso
    document.querySelectorAll('[data-progress]').forEach(function (el) {
        const val = el.getAttribute('data-progress');
        if (val !== null) {
            el.style.width = val + '%';
        }
    });

    // Filtro interactivo para el historial de excusas del aprendiz
    const contenedorFiltrosAp = document.getElementById('filtroExcusasAprendiz');
    if (contenedorFiltrosAp) {
        const botonesAp = contenedorFiltrosAp.querySelectorAll('button[data-filtro]');
        const itemsExcusasAp = document.querySelectorAll('#listaExcusasAprendiz .excusa-card-item');

        botonesAp.forEach(btn => {
            btn.addEventListener('click', function () {
                botonesAp.forEach(b => b.classList.remove('active', 'text-white'));
                this.classList.add('active', 'text-white');

                const filtro = (this.getAttribute('data-filtro') || 'todas').toLowerCase();

                itemsExcusasAp.forEach(item => {
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

    if (typeof Swal === 'undefined') return;

    // Confirmación al cerrar sesión
    document.querySelectorAll('a[href*="action=logout"]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const destUrl = this.getAttribute('href');
            Swal.fire({
                title: '¿Deseas cerrar sesión?',
                text: "Tu sesión actual de aprendiz finalizará de forma segura.",
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

    // Confirmación al marcar asistencia diaria manualmente
    document.querySelectorAll('a[href*="action=marcar_asistencia_aprendiz"]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const destUrl = this.getAttribute('href');
            Swal.fire({
                title: '¿Registrar Marcaje de Asistencia?',
                text: "Se registrará tu horario actual como asistencia a la jornada de formación.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Sí, marcar ahora',
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

    // Confirmación al radicar justificación médica
    const formExcusa = document.querySelector('form[action*="guardar_excusa"]');
    if (formExcusa) {
        formExcusa.addEventListener('submit', function (e) {
            if (this.dataset.confirmed === 'true') return;
            e.preventDefault();
            Swal.fire({
                title: '¿Radicar Justificación Médica?',
                text: "El soporte y los motivos serán enviados para revisión del instructor.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-send-check me-1"></i> Sí, enviar',
                cancelButtonText: 'Revisar datos',
                background: '#161b22',
                color: '#f0f6fc'
            }).then((result) => {
                if (result.isConfirmed) {
                    formExcusa.dataset.confirmed = 'true';
                    formExcusa.submit();
                }
            });
        });
    }
});
