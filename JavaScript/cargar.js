document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formularioCarga");
    const mensajeDiv = document.getElementById("mensaje");
    const botonObtener = document.getElementById("boton");
    const listaDoc = document.getElementById("listaDoc");
    const preview = document.getElementById("previsualizacion");

    // Guardar Documento (POST)
    formulario.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(formulario);

        try {
            const respuesta = await fetch("../php/documento.php", {
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
            const respuesta = await fetch("../php/documento.php");
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

            // Manejador para previsualizar al cambiar la selección
            listaDoc.onchange = function () {
                if (this.value) {
                    preview.src = "../" + this.value;
                    preview.style.display = "block";
                } else {
                    preview.style.display = "none";
                }
            };

        } catch (error) {
            console.error("Error al cargar la lista de documentos:", error);
            alert("No se pudo obtener la lista de documentos.");
        }
    });
});