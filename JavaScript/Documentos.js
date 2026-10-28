document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formularioCarga");
    const mensajeDiv = document.getElementById("mensaje");
    const botonObtener = document.getElementById("boton");
    const listaDoc = document.getElementById("listaDoc");
    const preview = document.getElementById("previsualizacion");
    const contenedorQR = document.getElementById("contenedorQR");
    const imagenQR = document.getElementById("imagenQR");

    // Guardar Documento (POST)
    formulario.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(formulario);

        try {
            const respuesta = await fetch("../php/Documentos.php", {
                method: "POST",
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                alert("Archivo guardado correctamente.");
                formulario.reset();
            } else {
                alert("Error: " + resultado.mensaje);
            }
        } catch (error) {
            console.error("Error al procesar la solicitud:", error);
            alert("Ocurrió un error inesperado al subir el documento.");
        }
    });

    // Obtener Documentos (GET)
    botonObtener.addEventListener("click", async () => {
        try {
            const respuesta = await fetch("../php/Documentos.php");
            const documentos = await respuesta.json();

            listaDoc.innerHTML = '<option value="">Seleccione un documento...</option>';

            if (Array.isArray(documentos) && documentos.length > 0) {
                documentos.forEach((documento) => {
                    const option = document.createElement("option");
                    option.value = documento.archivo_url;
                    option.textContent = documento.titulo;
                    listaDoc.appendChild(option);
                });
            } else {
                alert("No se encontraron documentos registrados.");
            }
        } catch (error) {
            console.error("Error al cargar la lista de documentos:", error);
            alert("No se pudo obtener la lista de documentos.");
        }
    });

    // Manejador para previsualizar y generar el QR al cambiar la selección del select
    listaDoc.addEventListener("change", function () {
        if (this.value) {
            // 1. Mostrar la previsualización del documento en el iframe
            preview.src = "../" + this.value;
            preview.style.display = "block";

            // 2. Construir la URL completa usando la IP de la PC para que el celular pueda acceder
            const urlCompleta = "http://192.168.1.5/ProyectoFinal/proyectohospital/" + this.value;

            // 3. Generar la imagen del QR dinámicamente mediante la API pública
            imagenQR.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(urlCompleta)}`;
            
            // 4. Mostrar el bloque del código QR en pantalla
            contenedorQR.style.display = "block";
        } else {
            // Ocultar si se deselecciona
            preview.style.display = "none";
            contenedorQR.style.display = "none";
        }
    });
});