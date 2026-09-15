const formulario = document.querySelector('#registro');

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();

    const usuario = new FormData();
    usuario.append('cedula', formulario.cedula.value);
    usuario.append('nombre', formulario.nombre.value);
    usuario.append('apellido', formulario.apellido.value);
    usuario.append('usuario', formulario.usuario.value);
    usuario.append('contrasenia', formulario.contrasenia.value);
    usuario.append('rol', formulario.rol.value);

    try {

        const respuesta = await fetch('../php/registrarFuncionario.php', {
            method: 'POST',
            body: usuario
        })

        const resultado = await respuesta.json();

        if (resultado.exito) {
            alert('Dato guardado correctamente');
        } else {
            alert('Error: No se guardo');
        }

    } catch (error) {
        alert('Error al guardar');
    }

})