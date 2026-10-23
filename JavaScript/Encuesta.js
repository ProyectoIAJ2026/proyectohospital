document.addEventListener('DOMContentLoaded', () => {

    // FORMULARIO: ENCUESTA DE SATISFACCIÓN
    const formEncuesta = document.getElementById('formEncuesta');

    if (formEncuesta) {
        formEncuesta.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página recargue o abra el PHP

            const formData = new FormData(formEncuesta);

            try {
                const respuesta = await fetch('../php/Encuesta.php', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await respuesta.text();

                if (resultado.trim() === 'ok') {
                    alert('¡Encuesta enviada con éxito! Muchas gracias por su colaboración.');
                    formEncuesta.reset(); // Limpia los campos del formulario
                    window.location.href = '../index.html'; // Redirige al inicio
                } else {
                    alert(resultado); // Muestra el mensaje de error que devuelve el PHP
                }
            } catch (error) {
                console.error('Error al enviar la encuesta:', error);
                alert('Ocurrió un error al comunicarse con el servidor.');
            }
        });
    }

});