document.addEventListener('DOMContentLoaded', function() {
    const selectTipo = document.getElementById('tipoUsuario');
    const seccionFuncionario = document.getElementById('seccionFuncionario');
    const seccionPaciente = document.getElementById('seccionPaciente');
    const seccionChofer = document.getElementById('seccionChofer');
    const titulo = document.getElementById('tituloRegistro');
    const formRegistro = document.getElementById('formRegistro');

    // Muestra/oculta campos según rol y activa 'required' sólo en los visibles
    selectTipo.addEventListener('change', function() {
        seccionFuncionario.classList.add('campo-oculto');
        seccionPaciente.classList.add('campo-oculto');
        seccionChofer.classList.add('campo-oculto');

        document.querySelectorAll('.Registro-input').forEach(input => input.required = false);
        selectTipo.required = true;

        titulo.className = '';

        switch (this.value) {
            case 'funcionario':
                seccionFuncionario.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Funcionario';
                titulo.classList.add('titulo-funcionario');
                seccionFuncionario.querySelectorAll('input, select').forEach(i => i.required = true);
                break;

            case 'paciente':
                seccionPaciente.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Paciente';
                titulo.classList.add('titulo-paciente');
                seccionPaciente.querySelectorAll('input, select').forEach(i => i.required = true);
                break;

            case 'chofer':
                seccionChofer.classList.remove('campo-oculto');
                titulo.textContent = 'Registro de Chofer';
                titulo.classList.add('titulo-chofer');
                seccionChofer.querySelectorAll('input, select').forEach(i => i.required = true);
                break;

            default:
                titulo.textContent = 'Registro de Usuario';
                titulo.classList.add('titulo-usuario');
                break;
        }
    });

    // Envío asíncrono con Fetch
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();

        const rol = selectTipo.value;
        if (!rol) {
            alert('Por favor selecciona un tipo de usuario.');
            return;
        }

        const formData = new FormData();
        formData.append('tipoUsuario', rol);

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

        try {
            const respuesta = await fetch('../php/Registro.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.text();

            // Verificamos si comienza con "ok" o incluye el mensaje de pendiente
            if (resultado.trim().startsWith("ok")) {
                // Si es chofer o funcionario, le informamos del estado pendiente; si es paciente, éxito normal
                if (rol === 'chofer' || rol === 'funcionario') {
                    alert('¡Registro realizado con éxito! Su cuenta se encuentra pendiente de aprobación por la administración del hospital.');
                } else {
                    alert('¡Registro realizado con éxito!');
                }
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