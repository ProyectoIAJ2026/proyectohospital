document.addEventListener('DOMContentLoaded', () => {

    // 1. FORMULARIO 1: REGISTRO DE AMBULANCIAS
    const formAmbulancia = document.getElementById('formAmbulancia');

    if (formAmbulancia) {
        formAmbulancia.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página se recargue

            const formData = new FormData(formAmbulancia);

            try {
                const respuesta = await fetch('../php/GuardarAmbulancia.php', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await respuesta.text();

                if (resultado.trim() === 'ok') {
                    alert('¡Ambulancia registrada con éxito!');
                    formAmbulancia.reset(); // Limpia los campos del formulario
                } else {
                    alert(resultado); // Muestra el mensaje de error que devuelve el PHP
                }
            } catch (error) {
                console.error('Error al registrar ambulancia:', error);
                alert('Ocurrió un error al comunicarse con el servidor.');
            }
        });
    }

    // 2. FORMULARIO 2: REGISTRO DE INSUMOS Y OBJETOS
    const formObjeto = document.getElementById('formObjeto');

    if (formObjeto) {
        formObjeto.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página se recargue

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