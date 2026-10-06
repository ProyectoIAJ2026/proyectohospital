const nombreDocumento = document.getElementById("nombreDocumento");
const archivo = document.getElementById("documento");
const subir = document.getElementById("subir");
const mensaje = document.getElementById("mensaje");
const boton = document.getElementById("boton");

subir.addEventListener("click",  async (e) => {
    e.preventDefault();

    let doc = new FormData();
    doc.append("nombreDocumento", nombreDocumento.value);
    doc.append("documento", archivo.files[0]);
    
    let respuesta = await fetch("../php/documento.php", {
        method: "POST",
        body: doc
    });

    let mensaje = await respuesta.json();
    
    if (mensaje.exito) {
        alert("Archivo Guardado Correctamente");
        document.GetElementByID("formularioCarga").reset();
    }else{
        alert(mensaje.mensaje);
    }
    })

    boton.addEventListener("click", async () => {
let dato = await fetch("../php/documento.php");
let documentos = await dato.json();

const listaDocumentos = document.getElementById("listaDocumentos");
listaDocumentos.name = "nombreDocumento";

listaDocumentos.innerHTML = "";

console.log("Documentos: ", documentos);
console.log("Primer Elemento: ", documentos[0]);
console.log("Tipo de nombre: ", typeof documentos[0].nombreDocumento);

documentos.forEach((documento) => {
    let option = document.createElement("option");
    option.textContent = documento.nombreDocumento;
    option.value = documento.nombreDocumento;

    listaDocumentos.appendChild(option);
    });

    const preview = document.getElementById("previsualizacion");

    ListaDocumentos.addEventListener("change", function ()  {
        let id = this.value;
        let documentoSeleccionado = documentos.find(documento => documento.nombreDocumento === id);

        if (documentoSeleccionado) {
            prev.src = documentoSeleccionado.ruta;

        }
    })
})

