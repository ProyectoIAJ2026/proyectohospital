// Inicializar el mapa centrado en el Hospital de Clínicas (Montevideo)
const mapa = L.map('visualizar', { attributionControl: false }).setView([-34.8911, -56.1557], 15);

L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 18
}).addTo(mapa);

// DOM
const departamentoOrigen = document.getElementById('departamento_origen');
const departamentoDestino = document.getElementById('departamento_destino');
const dirOrigen = document.getElementById('origen');
const dirDestino = document.getElementById('destino');

const ubicarOrigenBtn = document.getElementById('ubicarOrigen');
const ubicarDestinoBtn = document.getElementById('ubicarDestino');
const ubicarGpsBtn = document.getElementById('btnGpsOrigen');
const formTraslado = document.getElementById('formTraslado');
const formAmbulancia = document.getElementById('formAmbulancia');
const btnSimular = document.getElementById('btnSimularRecorrido');

// --- VARIABLES GLOBALES PARA LA ANIMACIÓN ---
let rutaCoordenadas = [];     
let marcadorAnimacion = null; 
let animacionTimer = null;    

// Iconos personalizados (Ambulancia y tu Marcador rojo guardado)
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

// Marcadores iniciales en Montevideo:
// 1. Origen: Ambulancia en el Hospital de Clínicas (Por defecto)
const marcadorOrigen = L.marker([-34.8911, -56.1557], { 
    draggable: true,
    icon: iconoOrigen
}).addTo(mapa).bindPopup("Origen del traslado (Hospital de Clínicas)");

// 2. Destino: Pin rojo personalizado ubicado en Cornelio Cantera 2749
const marcadorDestino = L.marker([-34.8868, -56.1575], { 
    draggable: true,
    icon: iconoDestino
}).addTo(mapa).bindPopup("Destino del traslado (Cornelio Cantera 2749)");

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

            rutaCoordenadas = coordenadasDibujo;

            capaRuta = L.polyline(coordenadasDibujo, { color: 'blue', weight: 5 }).addTo(mapa);

            mapa.fitBounds(capaRuta.getBounds(), { padding: [50, 50] });

            if (btnSimular) {
                btnSimular.disabled = false;
                btnSimular.style.backgroundColor = '#28a745'; 
                btnSimular.style.cursor = 'pointer';
            }
        }
    } catch (error) {
        console.error('Error al calcular la ruta:', error);
    }
}

// Ejecutamos el cálculo de la ruta automáticamente al cargar la página
calcularYMostrarRuta();

// --- FUNCIÓN DE ANIMACIÓN DE LA AMBULANCIA ---
function iniciarSimulacionAmbulancia() {
    if (!rutaCoordenadas || rutaCoordenadas.length === 0) return;

    if (animacionTimer) {
        clearInterval(animacionTimer);
        animacionTimer = null;
    }

    if (mapa.hasLayer(marcadorOrigen)) {
        mapa.removeLayer(marcadorOrigen);
    }

    if (!marcadorAnimacion) {
        marcadorAnimacion = L.marker(rutaCoordenadas[0], { icon: iconoOrigen }).addTo(mapa);
    } else {
        marcadorAnimacion.setLatLng(rutaCoordenadas[0]);
        if (!mapa.hasLayer(marcadorAnimacion)) {
            marcadorAnimacion.addTo(mapa);
        }
    }

    let pasoActual = 0;
    const totalPasos = rutaCoordenadas.length;

    animacionTimer = setInterval(() => {
        if (pasoActual < totalPasos) {
            marcadorAnimacion.setLatLng(rutaCoordenadas[pasoActual]);
            pasoActual++;
        } else {
            clearInterval(animacionTimer);
            animacionTimer = null;
        }
    }, 50); 
}

// --- FUNCIÓN 2: GEOCODIFICACIÓN ---
async function buscarUbicacion(direccionTexto, marcadorTarget, selectDepto) {
    if (!direccionTexto || !selectDepto.value) {
        alert('Por favor ingrese la dirección y seleccione un departamento.');
        return;
    }

    let textoLimpio = direccionTexto
        .replace(/Ubicación por defecto|Ubicación GPS|\(|\)/g, '')
        .trim();

    const consulta = `${textoLimpio}, ${selectDepto.value}, Uruguay`;
    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(consulta)}&filter=countrycode:uy&lang=es&limit=1&format=json&apiKey=${apiKey}`;

    try {
        const respuesta = await fetch(url);
        const dato = await respuesta.json();

        if (dato.results && dato.results.length > 0) {
            const resultado = dato.results[0];
            
            if (marcadorTarget === marcadorOrigen && !mapa.hasLayer(marcadorOrigen)) {
                marcadorOrigen.addTo(mapa);
            }
            if (marcadorTarget === marcadorDestino && !mapa.hasLayer(marcadorDestino)) {
                marcadorDestino.addTo(mapa);
            }

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

// --- FUNCIÓN 3: OBTENER UBICACIÓN GPS NATIVA CON RESPALDO ---
function obtenerUbicacionGps() {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización.');
        return;
    }

    dirOrigen.value = "Obteniendo ubicación GPS...";

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            if (!mapa.hasLayer(marcadorOrigen)) {
                marcadorOrigen.addTo(mapa);
            }

            marcadorOrigen.setLatLng([lat, lon]);
            mapa.setView([lat, lon], 15);

            dirOrigen.value = `Ubicación GPS (${lat.toFixed(4)}, ${lon.toFixed(4)})`;

            await calcularYMostrarRuta();
        },
        (error) => {
            console.warn('Aviso: No se pudo obtener la ubicación automática, aplicando Hospital de Clínicas por defecto:', error);
            
            const latDefault = -34.8911;
            const lonDefault = -56.1557;

            if (!mapa.hasLayer(marcadorOrigen)) {
                marcadorOrigen.addTo(mapa);
            }

            marcadorOrigen.setLatLng([latDefault, lonDefault]);
            mapa.setView([latDefault, lonDefault], 15);
            dirOrigen.value = `Hospital de Clínicas (${latDefault}, ${lonDefault})`;
            
            calcularYMostrarRuta();
        },
        { 
            enableHighAccuracy: false, 
            timeout: 20000,            
            maximumAge: 60000          
        }
    );
}

// Eventos
ubicarOrigenBtn.addEventListener('click', () => buscarUbicacion(dirOrigen.value, marcadorOrigen, departamentoOrigen));
ubicarDestinoBtn.addEventListener('click', () => buscarUbicacion(dirDestino.value, marcadorDestino, departamentoDestino));

if (ubicarGpsBtn) {
    ubicarGpsBtn.addEventListener('click', obtenerUbicacionGps);
}

if (btnSimular) {
    btnSimular.addEventListener('click', () => {
        iniciarSimulacionAmbulancia();
    });
}

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

// --- ENVÍO DEL FORMULARIO DE AMBULANCIAS ---
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