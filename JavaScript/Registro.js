document.addEventListener('DOMContentLoaded', function() {
    // Referencias al DOM
    const selectTipo = document.getElementById('tipoUsuario');
    const seccionFuncionario = document.getElementById('seccionFuncionario');
    const seccionPaciente = document.getElementById('seccionPaciente');
    const seccionChofer = document.getElementById('seccionChofer');
    const titulo = document.getElementById('tituloRegistro');
    const formRegistro = document.getElementById('formRegistro');

    // 1. Mostrar/Ocultar secciones según el tipo de usuario
    selectTipo.addEventListener('change', function() {
        seccionFuncionario.classList.add('campo-oculto');
        seccionPaciente.classList.add('campo-oculto');
        seccionChofer.classList.add('campo-oculto');

        // Desactivar atributo 'required' en campos ocultos para evitar errores al enviar
        document.querySelectorAll('.Registro-input').forEach(input => input.required = false);
        selectTipo.required = true;

        titulo.className = '';

        switch (this.value) {
            case 'funcionario':
                seccionFuncionario.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Funcionario';
                titulo.classList.add('titulo-funcionario');
                
                // Activar 'required' en la sección activa
                document.querySelectorAll('#seccionFuncionario input, #seccionFuncionario select').forEach(i => i.required = true);
                break;

            case 'paciente':
                seccionPaciente.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Paciente';
                titulo.classList.add('titulo-paciente');
                
                document.querySelectorAll('#seccionPaciente input').forEach(i => i.required = true);
                break;

            case 'chofer':
                seccionChofer.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Chofer';
                titulo.classList.add('titulo-chofer');
                
                document.querySelectorAll('#seccionChofer input').forEach(i => i.required = true);
                break;

            default:
                titulo.textContent = 'Registro de Usuario';
                titulo.classList.add('titulo-usuario');
                break;
        }
    });

    // 2. Envío del formulario mediante Fetch
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();

        const rol = selectTipo.value;
        if (!rol) {
            alert('Por favor selecciona un tipo de usuario.');
            return;
        }

        const formData = new FormData();
        formData.append('tipoUsuario', rol);

        // Capturar los datos dependiendo del rol visible
        if (rol === 'funcionario') {
            formData.append('cedula', document.getElementById('cedula_func').value);
            formData.append('nombre', document.getElementById('nombre_func').value);
            formData.append('apellido', document.getElementById('apellido').value);
            formData.append('usuario', document.getElementById('usuario').value);
            formData.append('contrasenia', document.getElementById('contrasenia_func').value);
            formData.append('cargo', document.getElementById('cargo').value);

        } else if (rol === 'paciente') {
            formData.append('cedula', document.getElementById('cedula_pac').value);
            formData.append('nombre', document.getElementById('nombre_pac').value);
            formData.append('telefono', document.getElementById('telefono').value);
            formData.append('email', document.getElementById('email').value);

        } else if (rol === 'chofer') {
            formData.append('cedula', document.getElementById('cedula_cho').value);
            formData.append('nombre', document.getElementById('nombre_cho').value);
            formData.append('contrasenia', document.getElementById('contrasenia_cho').value);
            formData.append('disponibilidad', document.getElementById('disponibilidad').value);
        }

        try {
            const respuesta = await fetch('../php/registro.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.text();

            if (resultado.trim() === "ok") {
                alert('¡Registro realizado con éxito!');
                formRegistro.reset();
                selectTipo.dispatchEvent(new Event('change')); // Resetea la vista
            } else {
                alert('Error en el registro: ' + resultado);
            }
        } catch (error) {
            console.error('Error de red/servidor:', error);
            alert('Ocurrió un error al conectar con el servidor.');
        }
    });
});