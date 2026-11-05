document.addEventListener("DOMContentLoaded", function () {
    fetch('./php/VerificarSesion.php')
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new TypeError("El backend no devolvió JSON. Revisa errores en VerificarSesion.php.");
            }
            return response.json();
        })
        .then(data => {
            const btnCerrar = document.getElementById('btnCerrarSesion');
            const btnIniciar = document.getElementById('btnIniciarSesion');
            const btnCrear = document.getElementById('btnCrearCuenta');

            // Selectores para todos los módulos internos
            const linkPacientes = document.querySelector('a[href*="Pacientes"]');
            const linkExpedientes = document.querySelector('a[href*="Documentos"]'); // Apunta a Documentos.php
            const linkInsumos = document.querySelector('a[href*="Insumos"]');
            const linkTraslados = document.querySelector('a[href*="Traslados"]');
            const linkEncuesta = document.querySelector('a[href*="Encuesta"]'); // Selector de encuesta

            if (data.activo) {
                // Hay sesión activa
                if (btnCerrar) btnCerrar.style.display = 'inline-block';
                if (btnIniciar) btnIniciar.style.display = 'none';

                const cargoUsuario = data.cargo ? data.cargo.toLowerCase() : '';
                
                // Mostrar "Crear cuenta" únicamente a Gerencia o Administrativo con rango de gestión
                if (
                    cargoUsuario.includes('administrativo') || 
                    cargoUsuario.includes('admin') || 
                    cargoUsuario.includes('gerencia') || 
                    cargoUsuario.includes('gerente')
                ) {
                    if (btnCrear) btnCrear.style.display = 'inline-block';
                } else {
                    if (btnCrear) btnCrear.style.display = 'none';
                }

                // --- RESTRICCIÓN PARA PACIENTE ---
                // Solo ve Encuesta (ocultamos los demás)
                if (data.rol === 'paciente') {
                    if (linkPacientes) linkPacientes.closest('.box').style.display = 'none';
                    if (linkExpedientes) linkExpedientes.closest('.box').style.display = 'none';
                    if (linkInsumos) linkInsumos.closest('.box').style.display = 'none';
                    if (linkTraslados) linkTraslados.closest('.box').style.display = 'none';
                }

                // --- RESTRICCIÓN PARA CHOFER ---
                // Solo ve Traslados y ambulancias
                if (data.rol === 'chofer') {
                    if (linkPacientes) linkPacientes.closest('.box').style.display = 'none';
                    if (linkExpedientes) linkExpedientes.closest('.box').style.display = 'none';
                    if (linkInsumos) linkInsumos.closest('.box').style.display = 'none';
                    if (linkEncuesta) linkEncuesta.closest('.box').style.display = 'none';
                }

                // --- RESTRICCIÓN PARA FUNCIONARIO SEGÚN SU CARGO ---
                if (data.rol === 'funcionario') {
                    const esMedicoEnfermero = cargoUsuario.includes('medicina') || cargoUsuario.includes('enfermer');
                    const esAdministrativo = cargoUsuario.includes('administrativo') || cargoUsuario.includes('admin');
                    const esOperador = cargoUsuario.includes('operador') || cargoUsuario.includes('logístico') || cargoUsuario.includes('logistico');
                    const esGerencia = cargoUsuario.includes('gerencia') || cargoUsuario.includes('gerente');

                    if (esGerencia) {
                        // Gerencia tiene acceso global a todo (no ocultamos ningún módulo)
                    } else if (esMedicoEnfermero) {
                        // Medicina / Enfermería: Solo ven Expedientes médicos
                        if (linkPacientes) linkPacientes.closest('.box').style.display = 'none';
                        if (linkInsumos) linkInsumos.closest('.box').style.display = 'none';
                        if (linkTraslados) linkTraslados.closest('.box').style.display = 'none';
                        if (linkEncuesta) linkEncuesta.closest('.box').style.display = 'none';
                    } else if (esAdministrativo) {
                        // Administrativo: Solo ven Pacientes (admisión y registro)
                        if (linkExpedientes) linkExpedientes.closest('.box').style.display = 'none';
                        if (linkInsumos) linkInsumos.closest('.box').style.display = 'none';
                        if (linkTraslados) linkTraslados.closest('.box').style.display = 'none';
                        if (linkEncuesta) linkEncuesta.closest('.box').style.display = 'none';
                    } else if (esOperador) {
                        // Operador Logístico: Ven Traslados, ambulancias e Insumos
                        if (linkPacientes) linkPacientes.closest('.box').style.display = 'none';
                        if (linkExpedientes) linkExpedientes.closest('.box').style.display = 'none';
                        if (linkEncuesta) linkEncuesta.closest('.box').style.display = 'none';
                    }
                }

            } else {
                // Sin sesión activa (visitante / registrado sin iniciar sesión)
                if (btnCerrar) btnCerrar.style.display = 'none';
                if (btnIniciar) btnIniciar.style.display = 'inline-block';
                if (btnCrear) btnCrear.style.display = 'inline-block';

                // Ocultar todas las cajas de los módulos internos para los visitantes
                if (linkPacientes) linkPacientes.closest('.box').style.display = 'none';
                if (linkExpedientes) linkExpedientes.closest('.box').style.display = 'none';
                if (linkInsumos) linkInsumos.closest('.box').style.display = 'none';
                if (linkTraslados) linkTraslados.closest('.box').style.display = 'none';
                if (linkEncuesta) linkEncuesta.closest('.box').style.display = 'none';
            }
        })
        .catch(error => console.error('Error al verificar la sesión:', error));
});