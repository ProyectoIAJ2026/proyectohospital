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
const formAmbulancia = document.getElementById('formAmbulancia');

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

// Marcadores iniciales
const marcadorOrigen = L.marker([-32.3214, -58.0756], {
    draggable: true,
    icon: iconoOrigen
}).addTo(mapa).bindPopup("Origen del traslado");

const marcadorDestino = L.marker([-32.3150, -58.0800], {
    draggable: true,
    icon: iconoDestino
}).addTo(mapa).bindPopup("Destino del traslado");

// Variable global para almacenar la capa de la ruta actual y evitar que se encimen
let capaRuta = null;

// Clave de API de Geoapify
const apiKey = '4572a7318c164b3b873097a1f7b2c618';

// --- FUNCIÓN 1: TRAZAR RUTA ENTRE LOS DOS MARCADORES ---
async function calcularYMostrarRuta() {
    const latLngOrigen = marcadorOrigen.getLatLng();
    const latLngDestino = marcadorDestino.getLatLng();

    const urlRuta = `https://api.geoapify.com/v1/routing?waypoints=${latLngOrigen.lat},${latLngOrigen.lng}|${latLngDestino.lat},${latLngDestino.lng}&mode=drive&units=metric&lang=es&apiKey=${apiKey}`;

    try {
        const respuesta = await fetch(urlRuta);
        const routeData = await respuesta.json();

        if (routeData.features && routeData.features.length > 0) {
            const route = routeData.features[0];
            
            const distanciaKm = (route.properties.distance / 1000).toFixed(2);
            const tiempoMin = Math.round(route.properties.time / 60);
            console.log(`Distancia: ${distanciaKm} km | Tiempo estimado: ${tiempoMin} min`);

            if (capaRuta) {
                mapa.removeLayer(capaRuta);
            }

            const coordenadasDibujo = route.geometry.coordinates[0].map(([lon, lat]) => [lat, lon]);

            capaRuta = L.polyline(coordenadasDibujo, { color: 'blue', weight: 5 }).addTo(mapa);

            mapa.fitBounds(capaRuta.getBounds(), { padding: [50, 50] });
        }
    } catch (error) {
        console.error('Error al calcular la ruta:', error);
    }
}

// --- FUNCIÓN 2: GEOCODIFICACIÓN (BUSCAR DIRECCIÓN) ---
async function buscarUbicacion(direccionTexto, marcadorTarget) {
    if (!direccionTexto || !departamento.value) {
        alert('Por favor ingrese la dirección y seleccione un departamento.');
        return;
    }

    const consulta = `${direccionTexto}, ${departamento.value}, Uruguay`;
    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(consulta)}&filter=countrycode:uy&lang=es&limit=1&format=json&apiKey=${apiKey}`;

    try {
        const respuesta = await fetch(url);
        const dato = await respuesta.json();

        if (dato.results && dato.results.length > 0) {
            const resultado = dato.results[0];
            marcadorTarget.setLatLng([resultado.lat, resultado.lon]);

            await calcularYMostrarRuta();
        } else {
            alert('No se encontró la dirección especificada.');
        }
    } catch (error) {
        console.error('Error al geocodificar:', error);
        alert('Ocurrió un error al consultar el mapa.');
    }
}

// Eventos de los botones de búsqueda en el mapa
ubicarOrigenBtn.addEventListener('click', () => buscarUbicacion(dirOrigen.value, marcadorOrigen));
ubicarDestinoBtn.addEventListener('click', () => buscarUbicacion(dirDestino.value, marcadorDestino));

marcadorOrigen.on('dragend', calcularYMostrarRuta);
marcadorDestino.on('dragend', calcularYMostrarRuta);

// --- ENVÍO DEL FORMULARIO DE TRASLADOS ---
if (formTraslado) {
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
}

// --- ENVÍO DEL FORMULARIO DE AMBULANCIAS (MOVIDO A TRASLADOS) ---
if (formAmbulancia) {
    formAmbulancia.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formAmbulancia);

        try {
            const respuesta = await fetch('../php/GuardarAmbulancia.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.text();

            if (resultado.trim() === 'ok') {
                alert('¡Ambulancia registrada con éxito!');
                formAmbulancia.reset();
            } else {
                alert(resultado);
            }
        } catch (error) {
            console.error('Error al registrar ambulancia:', error);
            alert('Ocurrió un error al comunicarse con el servidor.');
        }
    });
}