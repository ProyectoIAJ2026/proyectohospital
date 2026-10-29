document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formularioCarga');
    const imagenQR = document.getElementById('imagenQR');
    const iframePrevia = document.getElementById('previsualizacion');
    
    const btnObtener = document.getElementById('boton'); // Botón "Obtener documentos"
    const listaDoc = document.getElementById('listaDoc'); // Selector <select> de documentos

    // 1. Lógica para cargar un documento nuevo mediante formulario
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(formulario);

            fetch('../php/CargarDocumento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    
                    if (data.texto_qr) {
                        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data.texto_qr)}`;
                        imagenQR.src = qrUrl;
                    }

                    if (data.archivo_url) {
                        iframePrevia.src = '../' + data.archivo_url;
                    }

                    formulario.reset();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                alert("Error de conexión con el servidor.");
            });
        });
    }

    // 2. Lógica para el botón "Obtener documentos" (llenar el <select> con "ID:")
    if (btnObtener && listaDoc) {
        btnObtener.addEventListener('click', function(e) {
            e.preventDefault();

            fetch('../php/ObtenerDocumentos.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    listaDoc.innerHTML = '<option value="">Seleccione un documento</option>';

                    data.documentos.forEach(doc => {
                        const option = document.createElement('option');
                        option.value = doc.archivo_url; 
                        option.dataset.qr = doc.codigo_qr; 
                        // AQUÍ SE AGREGA LA ETIQUETA "ID:"
                        option.textContent = `ID: ${doc.id_documento} - ${doc.titulo}`;
                        listaDoc.appendChild(option);
                    });

                    alert("¡Lista de documentos actualizada!");
                } else {
                    alert("Error al cargar los documentos.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error de conexión al obtener los documentos.");
            });
        });

        // 3. Lógica al cambiar de opción en el selector para mostrar QR y vista previa
        listaDoc.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const archivoUrl = this.value;
            const textoQr = selectedOption.dataset.qr;

            if (archivoUrl) {
                iframePrevia.src = '../' + archivoUrl;
            } else {
                iframePrevia.src = '';
            }

            if (textoQr) {
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(textoQr)}`;
                imagenQR.src = qrUrl;
            } else {
                imagenQR.src = ''; 
            }
        });
    }
});