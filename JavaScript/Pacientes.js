// 1. BUSCAR PACIENTE (SELECT)
document.getElementById('formBuscar').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita recargar la página

    let datos = new FormData(this);

    fetch('buscar_paciente.php', {
        method: 'POST',
        body: datos
    })
    .then(respuesta => respuesta.json())
    .then(datosPaciente => {
        if (datosPaciente.nombre) {
            alert("Paciente encontrado: " + datosPaciente.nombre);
        } else {
            alert("No se encontró el paciente.");
        }
    });
});

// 2. REGISTRAR PACIENTE (INSERT)
document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita recargar la página

    let datos = new FormData(this);

    fetch('guardar_paciente.php', {
        method: 'POST',
        body: datos
    })
    .then(respuesta => respuesta.text())
    .then(resultado => {
        alert("Respuesta del servidor: " + resultado);
    });
});