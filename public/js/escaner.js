document.addEventListener('DOMContentLoaded', function() {
    const inputRfId = document.getElementById('codigo_rfid');
    const formEscaner = document.getElementById('formEscaner');
    const respuestaDiv = document.getElementById('respuestaAsistencia');

    if (!inputRfId || !formEscaner) return;

    // Mantener siempre el foco en el input
    window.addEventListener('focus', () => inputRfId.focus());
    document.addEventListener('click', () => inputRfId.focus());
    
    setInterval(() => {
        if(document.activeElement !== inputRfId) {
            inputRfId.focus();
        }
    }, 1000);

    // Interceptar el envío del formulario mediante AJAX
    formEscaner.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = inputRfId.value.trim();
        if (!codigo) return;

        fetch('index.php?action=procesar_rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'codigo_rfid=' + encodeURIComponent(codigo)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                respuestaDiv.innerHTML = `<div class="alert alert-success fs-4">${data.mensaje}</div>`;
            } else {
                respuestaDiv.innerHTML = `<div class="alert alert-danger fs-4">${data.mensaje}</div>`;
            }

            // Limpiar y restaurar foco
            inputRfId.value = '';
            setTimeout(() => { respuestaDiv.innerHTML = ''; }, 4000);
        })
        .catch(error => {
            console.error('Error:', error);
            respuestaDiv.innerHTML = `<div class="alert alert-warning">Error al procesar el código RFID.</div>`;
            inputRfId.value = '';
        });
    });

    formEscaner.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = inputRfId.value.trim();
        console.log("Código RFID leído:", codigo); // <-- ¡Mira si aparece esto en la consola al presionar Enter!

        if (!codigo) return;

        fetch('index.php?action=procesar_rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'codigo_rfid=' + encodeURIComponent(codigo)
        })
        .then(response => {
            console.log("Respuesta HTTP recibida:", response); // <-- ¡Mira si llega la respuesta del servidor!
            return response.json();
        })
        .then(data => {
            console.log("Datos del servidor:", data); // <-- ¡Mira qué devuelve el PHP!
            if (data.status === 'success') {
                respuestaDiv.innerHTML = `<div class="alert alert-success fs-4">${data.mensaje}</div>`;
            } else {
                respuestaDiv.innerHTML = `<div class="alert alert-danger fs-4">${data.mensaje}</div>`;
            }

            inputRfId.value = '';
            setTimeout(() => { respuestaDiv.innerHTML = ''; }, 4000);
        })
        .catch(error => {
            console.error('Error en el Fetch:', error); // <-- Si hay un error de sintaxis en PHP, saltará aquí
            respuestaDiv.innerHTML = `<div class="alert alert-warning">Error al procesar el código RFID. Revisa la consola (F12).</div>`;
            inputRfId.value = '';
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const inputRfId = document.getElementById('codigo_rfid');
    const formEscaner = document.getElementById('formEscaner');
    const respuestaDiv = document.getElementById('respuestaAsistencia');

    if (!inputRfId || !formEscaner) return;

    // Mantener siempre el foco en el input del lector
    window.addEventListener('focus', () => inputRfId.focus());
    document.addEventListener('click', () => inputRfId.focus());
    
    setInterval(() => {
        if(document.activeElement !== inputRfId) {
            inputRfId.focus();
        }
    }, 1000);

    // Interceptar el envío del formulario mediante AJAX
    formEscaner.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = inputRfId.value.trim();
        if (!codigo) return;

        fetch('index.php?action=procesar_rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'codigo_rfid=' + encodeURIComponent(codigo)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                respuestaDiv.innerHTML = `<div class="alert alert-success bg-success text-white border-0 fs-5 py-3">${data.mensaje}</div>`;
            } else {
                respuestaDiv.innerHTML = `<div class="alert alert-danger bg-danger text-white border-0 fs-5 py-3">${data.mensaje}</div>`;
            }

            // Limpiar y restaurar foco
            inputRfId.value = '';
            setTimeout(() => { respuestaDiv.innerHTML = ''; }, 4000);
        })
        .catch(error => {
            console.error('Error:', error);
            respuestaDiv.innerHTML = `<div class="alert alert-warning bg-warning text-dark border-0 fs-5">Error al procesar el código RFID.</div>`;
            inputRfId.value = '';
        });
    });
});