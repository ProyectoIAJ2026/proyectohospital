document.addEventListener('DOMContentLoaded', function() {
    // 1. OBTENER LOS ELEMENTOS PRINCIPALES DE LA PÁGINA
    const selectTipo = document.getElementById('tipoUsuario');
    const seccionFuncionario = document.getElementById('seccionFuncionario');
    const seccionPaciente = document.getElementById('seccionPaciente');
    const seccionChofer = document.getElementById('seccionChofer');
    const titulo = document.getElementById('tituloRegistro');
    const formRegistro = document.getElementById('formRegistro');

    // 2. CAMBIAR CAMPOS VISIBLES SEGÚN EL TIPO DE USUARIO SELECCIONADO
    selectTipo.addEventListener('change', function() {
        // Oculta todas las secciones
        seccionFuncionario.classList.add('campo-oculto');
        seccionPaciente.classList.add('campo-oculto');
        seccionChofer.classList.add('campo-oculto');

        // Desactiva el requisito 'obligatorio' de los campos ocultos
        document.querySelectorAll('.Registro-input').forEach(input => input.required = false);
        selectTipo.required = true;

        titulo.className = '';

        // Muestra la sección correspondiente y marca sus campos como obligatorios
        switch (this.value) {
            case 'funcionario':
                seccionFuncionario.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Funcionario';
                titulo.classList.add('titulo-funcionario');
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
                document.querySelectorAll('#seccionChofer input, #seccionChofer select').forEach(i => i.required = true);
                break;

            default:
                titulo.textContent = 'Registro de Usuario';
                titulo.classList.add('titulo-usuario');
                break;
        }
    });

    // 3. ENVIAR FORMULARIO AL PHP SIN RECARGAR LA PÁGINA
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault(); // Detiene el envío por defecto de la página

        const rol = selectTipo.value;
        if (!rol) {
            alert('Por favor selecciona un tipo de usuario.');
            return;
        }

        // Empaqueta los datos a enviar
        const formData = new FormData();
        formData.append('tipoUsuario', rol);

        // Guarda los datos según el tipo de usuario
        if (rol === 'funcionario') {
            formData.append('cedula', document.getElementById('cedula_func').value);
            formData.append('nombre', document.getElementById('nombre_func').value);
            formData.append('apellido', document.getElementById('apellido').value);
            formData.append('email', document.getElementById('email_func').value);
            formData.append('usuario', document.getElementById('usuario').value);
            formData.append('contrasenia', document.getElementById('contrasenia_func').value);
            formData.append('cargo', document.getElementById('cargo').value);

        } else if (rol === 'paciente') {
            formData.append('cedula', document.getElementById('cedula_pac').value);
            formData.append('nombre', document.getElementById('nombre_pac').value);
            formData.append('apellido', document.getElementById('apellido_pac').value);
            formData.append('telefono', document.getElementById('telefono').value);
            formData.append('email', document.getElementById('email_pac').value);
            formData.append('contrasenia', document.getElementById('contrasenia_pac').value);

        } else if (rol === 'chofer') {
            formData.append('cedula', document.getElementById('cedula_cho').value);
            formData.append('nombre', document.getElementById('nombre_cho').value);
            formData.append('apellido', document.getElementById('apellido_cho').value);
            formData.append('email', document.getElementById('email_cho').value);
            formData.append('contrasenia', document.getElementById('contrasenia_cho').value);
            formData.append('disponibilidad', document.getElementById('disponibilidad').value);
        }

        // Envía la información al archivo registro.php
        try {
            const respuesta = await fetch('../php/registro.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.text();

            // Muestra mensaje según la respuesta del backend
            if (resultado.trim() === "ok") {
                alert('¡Registro realizado con éxito!');
                formRegistro.reset();
                selectTipo.dispatchEvent(new Event('change'));
            } else {
                alert('Error en el registro: ' + resultado);
            }
        } catch (error) {
            console.error('Error de red/servidor:', error);
            alert('Ocurrió un error al conectar con el servidor.');
        }
    });
});