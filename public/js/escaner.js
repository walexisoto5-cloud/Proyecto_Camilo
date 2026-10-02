document.addEventListener('DOMContentLoaded', function () {
    const inputRfId = document.getElementById('codigo_rfid');
    const formEscaner = document.getElementById('formEscaner');
    const respuestaDiv = document.getElementById('respuestaAsistencia');

    if (!inputRfId || !formEscaner) return;

    let isSubmitting = false;

    // Función auxiliar para saber si el usuario está interactuando con un modal u otro campo
    function isModalOrOtherInputActive() {
        const modalAbierto = document.querySelector('.modal.show');
        if (modalAbierto) return true;

        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (['input', 'textarea', 'select', 'button'].includes(activeTag) && document.activeElement !== inputRfId) {
            return true;
        }
        return false;
    }

    // Mantener el foco en el input RFID solo si no hay modales abiertos
    function enfocarLector() {
        if (!isModalOrOtherInputActive() && document.activeElement !== inputRfId) {
            inputRfId.focus();
        }
    }

    window.addEventListener('focus', enfocarLector);
    document.addEventListener('click', (e) => {
        // Si el clic no fue dentro de un modal ni en un botón interactivo, re-enfocar
        if (!e.target.closest('.modal') && !e.target.closest('button, a, input, select, textarea')) {
            enfocarLector();
        }
    });

    setInterval(enfocarLector, 1200);

    // Procesar lectura RFID
    formEscaner.addEventListener('submit', function (e) {
        e.preventDefault();

        if (isSubmitting) return;

        const codigo = inputRfId.value.trim();
        if (!codigo) return;

        isSubmitting = true;
        inputRfId.disabled = true;

        fetch('index.php?action=procesar_rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'codigo_rfid=' + encodeURIComponent(codigo)
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                let alertClass = 'alert-danger bg-danger text-white';
                if (data.status === 'success') {
                    alertClass = 'alert-success bg-success text-white';
                } else if (data.status === 'warning') {
                    alertClass = 'alert-warning bg-warning text-dark';
                }

                respuestaDiv.innerHTML = `<div class="alert ${alertClass} border-0 fs-5 py-3 shadow-sm">${data.mensaje}</div>`;

                inputRfId.value = '';
                setTimeout(() => {
                    respuestaDiv.innerHTML = '';
                }, 4500);
            })
            .catch(error => {
                console.error('Error al procesar RFID:', error);
                respuestaDiv.innerHTML = `<div class="alert alert-warning bg-warning text-dark border-0 fs-5">Error de comunicación al procesar el código RFID.</div>`;
                inputRfId.value = '';
            })
            .finally(() => {
                isSubmitting = false;
                inputRfId.disabled = false;
                enfocarLector();
            });
    });
});