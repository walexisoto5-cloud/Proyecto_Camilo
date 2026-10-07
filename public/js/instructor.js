// Control interactivo para el panel de asistencia del instructor

document.addEventListener('DOMContentLoaded', function () {
    const btnMarcarTodos = document.getElementById('btnMarcarTodosPresente');

    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function () {
            marcarTodosPresente();
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
