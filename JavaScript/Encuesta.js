document.addEventListener('DOMContentLoaded', () => {

    // QR de la encuesta: la URL con la IP real la calcula el servidor
    const qrEncuesta = document.getElementById('qrEncuesta');
    if (qrEncuesta) {
        fetch('../php/ObtenerBaseUrl.php')
            .then(response => response.json())
            .then(data => {
                qrEncuesta.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" +
                    encodeURIComponent(data.url_encuesta);
            })
            .catch(() => { /* si falla, queda la imagen de respaldo */ });
    }

    // FORMULARIO: ENCUESTA DE SATISFACCIÓN
    const formEncuesta = document.getElementById('formEncuesta');

    if (formEncuesta) {
        formEncuesta.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(formEncuesta);

            try {
                const respuesta = await fetch('../php/Encuesta.php', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await respuesta.text();

                if (resultado.trim() === 'ok') {
                    alert('¡Encuesta enviada con éxito! Muchas gracias por su colaboración.');
                    formEncuesta.reset();
                    window.location.href = '../index.html';
                } else {
                    alert(resultado);
                }
            } catch (error) {
                console.error('Error al enviar la encuesta:', error);
                alert('Ocurrió un error al comunicarse con el servidor.');
            }
        });
    }

});