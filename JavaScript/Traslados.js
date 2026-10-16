// Inicializar el mapa centrado en Paysandú
const mapa = L.map('visualizar', { attributionControl: false }).setView([-32.3214, -58.0756], 15);

L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 18
}).addTo(mapa);

// DOM
const departamento = document.getElementById('departamento');
const dirOrigen = document.getElementById('origen');
const dirDestino = document.getElementById('destino');

const ubicarOrigenBtn = document.getElementById('ubicarOrigen');
const ubicarDestinoBtn = document.getElementById('ubicarDestino');
const formTraslado = document.getElementById('formTraslado');

// Iconos
const iconoOrigen = L.icon({
    iconUrl: '../img/Ambulancia.png',
    iconSize: [38, 45],
    iconAnchor: [19, 45]
});

const iconoDestino = L.icon({
    iconUrl: '../img/Marcador.png',
    iconSize: [35, 45],
    iconAnchor: [17, 45]
});

// Marcadores
const marcadorOrigen = L.marker([-32.3214, -58.0756], {
    draggable: true,
    icon: iconoOrigen
}).addTo(mapa).bindPopup("Origen del traslado");

const marcadorDestino = L.marker([-32.3150, -58.0800], {
    draggable: true,
    icon: iconoDestino
}).addTo(mapa).bindPopup("Destino del traslado");

// Función de geocodificación
async function buscarUbicacion(direccionTexto, marcadorTarget) {
    if (!direccionTexto || !departamento.value) {
        alert('Por favor ingrese la dirección y seleccione un departamento.');
        return;
    }

    const consulta = `${direccionTexto}, ${departamento.value}, Uruguay`;
    const apiKey = '4572a7318c164b3b873097a1f7b2c618';
    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(consulta)}&filter=countrycode:uy&lang=es&limit=1&format=json&apiKey=${apiKey}`;

    try {
        const respuesta = await fetch(url);
        const dato = await respuesta.json();

        if (dato.results && dato.results.length > 0) {
            const resultado = dato.results[0];
            marcadorTarget.setLatLng([resultado.lat, resultado.lon]);

            const grupo = L.featureGroup([marcadorOrigen, marcadorDestino]);
            mapa.fitBounds(grupo.getBounds().pad(0.2));
        } else {
            alert('No se encontró la dirección especificada.');
        }
    } catch (error) {
        console.error('Error al geocodificar:', error);
        alert('Ocurrió un error al consultar el mapa.');
    }
}

// Eventos de los botones de búsqueda
ubicarOrigenBtn.addEventListener('click', () => buscarUbicacion(dirOrigen.value, marcadorOrigen));
ubicarDestinoBtn.addEventListener('click', () => buscarUbicacion(dirDestino.value, marcadorDestino));

// Envío del formulario sin recargar la página (captura la respuesta de GuardarTraslado.php)
formTraslado.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(formTraslado);

    try {
        const respuesta = await fetch('../php/GuardarTraslado.php', {
            method: 'POST',
            body: formData
        });

        const resultado = await respuesta.text();

        if (resultado.trim() === 'ok') {
            alert('¡Traslado registrado con éxito!');
            formTraslado.reset();
        } else {
            alert(resultado);
        }
    } catch (error) {
        console.error('Error al guardar el traslado:', error);
        alert('Ocurrió un error al comunicarse con el servidor.');
    }
});