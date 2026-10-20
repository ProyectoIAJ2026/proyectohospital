document.addEventListener('DOMContentLoaded', () => {

    // FORMULARIO: REGISTRO DE INSUMOS, OBJETOS Y EQUIPAMIENTO
    const formObjeto = document.getElementById('formObjeto');

    if (formObjeto) {
        formObjeto.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página recargue o salte al PHP

            const formData = new FormData(formObjeto);

            try {
                const respuesta = await fetch('../php/GuardarObjeto.php', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await respuesta.text();

                if (resultado.trim() === 'ok') {
                    alert('¡Recurso registrado con éxito!');
                    formObjeto.reset(); // Limpia los campos del formulario
                } else {
                    alert(resultado); // Muestra el mensaje de error que devuelve el PHP
                }
            } catch (error) {
                console.error('Error al registrar recurso:', error);
                alert('Ocurrió un error al comunicarse con el servidor.');
            }
        });
    }

});