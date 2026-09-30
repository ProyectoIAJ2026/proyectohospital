const formulario = document.querySelector('#formInicioSesion');

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();

    const datosI = new FormData();
    datosI.append('usuario', formulario.usuario.value.trim());
    datosI.append('contrasenia', formulario.contrasenia.value.trim());

    try {
        const respuesta = await fetch('../php/IniciarSesion.php', {
            method: 'POST',
            body: datosI
        });

        const textoRespuesta = await respuesta.text();

        let objetoJSON;
        try {
            objetoJSON = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('El servidor no devolvió JSON válido:', textoRespuesta);
            alert('Error en la ruta del archivo o en la respuesta del servidor.');
            return;
        }

        if (objetoJSON.error) {
            alert(objetoJSON.error);
        } else if (objetoJSON.exito) {
            window.location.href = '../index.html';
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
        alert('Ocurrió un error de conexión con el servidor.');
    }
});