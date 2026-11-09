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
            // Seleccionamos los DIVs contenedores directos que tienen los IDs en el index.html
            const boxCerrar = document.getElementById('btnCerrarSesion');
            const boxIniciar = document.getElementById('btnIniciarSesion');
            const boxCrear = document.getElementById('btnCrearCuenta');
            const boxPerfil = document.getElementById('btnVerPerfil');

            // Selectores de los módulos de la página principal
            const linkPacientes = document.querySelector('a[href*="Pacientes"]');
            const linkExpedientes = document.querySelector('a[href*="Documentos"]'); 
            const linkInsumos = document.querySelector('a[href*="Insumos"]');
            const linkTraslados = document.querySelector('a[href*="TrasladosYAmbulancias"]');
            const linkEncuesta = document.querySelector('a[href*="Encuesta"]');

            if (data.activo) {
                // Hay sesión activa
                if (boxCerrar) boxCerrar.style.display = 'inline-block';
                if (boxIniciar) boxIniciar.style.display = 'none';
                if (boxPerfil) boxPerfil.style.display = 'inline-block';

                const cargoUsuario = data.cargo ? data.cargo.toLowerCase() : '';
                
                // Mostrar "Crear cuenta" únicamente a Administrativos o Gerencia
                if (
                    cargoUsuario.includes('administrativo') || 
                    cargoUsuario.includes('admin') || 
                    cargoUsuario.includes('gerencia') || 
                    cargoUsuario.includes('gerente')
                ) {
                    if (boxCrear) boxCrear.style.display = 'inline-block';
                } else {
                    if (boxCrear) boxCrear.style.display = 'none';
                }

                // --- RESTRICCIÓN PARA CHOFER ---
                if (data.rol === 'chofer') {
                    linkPacientes?.closest('.box')?.style.setProperty('display', 'none');
                    linkExpedientes?.closest('.box')?.style.setProperty('display', 'none');
                    linkInsumos?.closest('.box')?.style.setProperty('display', 'none');
                    linkEncuesta?.closest('.box')?.style.setProperty('display', 'none');
                    // Traslados y ambulancias se MANTIENE visible para el chofer
                }

                // --- RESTRICCIÓN PARA PACIENTE ---
                if (data.rol === 'paciente') {
                    linkPacientes?.closest('.box')?.style.setProperty('display', 'none');
                    linkExpedientes?.closest('.box')?.style.setProperty('display', 'none');
                    linkInsumos?.closest('.box')?.style.setProperty('display', 'none');
                    linkTraslados?.closest('.box')?.style.setProperty('display', 'none');
                    // La encuesta SE MANTIENE visible para el paciente
                    linkEncuesta?.closest('.box')?.style.setProperty('display', 'inline-block'); 
                }

                // --- RESTRICCIÓN PARA FUNCIONARIOS ---
                if (data.rol === 'funcionario') {
                    const esGerencia = cargoUsuario.includes('gerencia') || cargoUsuario.includes('gerente');
                    const esOperador = cargoUsuario.includes('operador') || cargoUsuario.includes('logístico') || cargoUsuario.includes('logistico');
                    const esClinico = cargoUsuario.includes('medicina general') || cargoUsuario.includes('enfermero') || cargoUsuario.includes('enfermera');
                    const esAdministrativo = cargoUsuario.includes('administrativo') || cargoUsuario.includes('admin');
                    
                    if (esClinico) {
                        // Personal Clínico: Solo Expedientes visible
                        linkPacientes?.closest('.box')?.style.setProperty('display', 'none');
                        linkExpedientes?.closest('.box')?.style.setProperty('display', 'inline-block');
                        linkInsumos?.closest('.box')?.style.setProperty('display', 'none');
                        linkTraslados?.closest('.box')?.style.setProperty('display', 'none');
                        linkEncuesta?.closest('.box')?.style.setProperty('display', 'none');
                    } 
                    else if (esAdministrativo) {
                        // Administrativo: Solo Pacientes visible (junto a Crear cuenta, Ver perfil y Cerrar sesión)
                        linkPacientes?.closest('.box')?.style.setProperty('display', 'inline-block');
                        linkExpedientes?.closest('.box')?.style.setProperty('display', 'none');
                        linkInsumos?.closest('.box')?.style.setProperty('display', 'none');
                        linkTraslados?.closest('.box')?.style.setProperty('display', 'none');
                        linkEncuesta?.closest('.box')?.style.setProperty('display', 'none');
                    } 
                    else if (esOperador) {
                        // Operador Logístico: Insumos y Traslados visibles (junto a Ver perfil y Cerrar sesión)
                        linkPacientes?.closest('.box')?.style.setProperty('display', 'none');
                        linkExpedientes?.closest('.box')?.style.setProperty('display', 'none');
                        linkInsumos?.closest('.box')?.style.setProperty('display', 'inline-block');
                        linkTraslados?.closest('.box')?.style.setProperty('display', 'inline-block');
                        linkEncuesta?.closest('.box')?.style.setProperty('display', 'none');
                    }
                    else if (!esGerencia) {
                        // Si es otro funcionario sin permisos específicos, ocultamos traslados por defecto
                        linkTraslados?.closest('.box')?.style.setProperty('display', 'none');
                    }
                }

            } else {
                // Sin sesión activa (Visitante / Anónimo) -> Ocultamos módulos internos por seguridad
                if (boxCerrar) boxCerrar.style.display = 'none';
                if (boxIniciar) boxIniciar.style.display = 'inline-block';
                if (boxCrear) boxCrear.style.display = 'inline-block';
                if (boxPerfil) boxPerfil.style.display = 'none';

                // Ocultar todos los módulos internos a los visitantes anónimos
                linkPacientes?.closest('.box')?.style.setProperty('display', 'none');
                linkExpedientes?.closest('.box')?.style.setProperty('display', 'none');
                linkInsumos?.closest('.box')?.style.setProperty('display', 'none');
                linkTraslados?.closest('.box')?.style.setProperty('display', 'none');
                linkEncuesta?.closest('.box')?.style.setProperty('display', 'none');
            }
        })
        .catch(error => console.error('Error al verificar la sesión:', error));
});