// Inicializar el mapa centrado en Paysandú
const mapa = L.map('visualizar', { attributionControl: false }).setView([-32.3214, -58.0756], 15);

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
let rutaCoordenadas = [];     // Almacenará los puntos [lat, lng] de la ruta calculada
let marcadorAnimacion = null; // Instancia del marcador animado de la ambulancia
let animacionTimer = null;    // Control del temporizador de movimiento

// Iconos (Cada uno con su imagen correcta)
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

// Marcadores iniciales (Origen = Ambulancia, Destino = Pin rojo)
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

            // Guardamos las coordenadas globalmente para la simulación
            rutaCoordenadas = coordenadasDibujo;

            capaRuta = L.polyline(coordenadasDibujo, { color: 'blue', weight: 5 }).addTo(mapa);

            mapa.fitBounds(capaRuta.getBounds(), { padding: [50, 50] });

            // Habilitar el botón de simulación una vez que la ruta está lista
            if (btnSimular) {
                btnSimular.disabled = false;
                btnSimular.style.backgroundColor = '#28a745'; // Color verde de éxito
                btnSimular.style.cursor = 'pointer';
            }
        }
    } catch (error) {
        console.error('Error al calcular la ruta:', error);
    }
}

// --- FUNCIÓN DE ANIMACIÓN DE LA AMBULANCIA ---
function iniciarSimulacionAmbulancia() {
    if (!rutaCoordenadas || rutaCoordenadas.length === 0) return;

    // Si hay una simulación previa corriendo, la detenemos
    if (animacionTimer) {
        clearInterval(animacionTimer);
        animacionTimer = null;
    }

    // 1. Ocultamos temporalmente el marcador estático de origen para que la unidad "salga"
    if (mapa.hasLayer(marcadorOrigen)) {
        mapa.removeLayer(marcadorOrigen);
    }

    // Nota: Dejamos intacto el `marcadorDestino` (pin rojo) para que permanezca visible en el mapa

    // Si el marcador animado no existe, lo creamos en el punto inicial
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

    // Ejecutamos el movimiento paso a paso
    animacionTimer = setInterval(() => {
        if (pasoActual < totalPasos) {
            marcadorAnimacion.setLatLng(rutaCoordenadas[pasoActual]);
            pasoActual++;
        } else {
            // Fin del recorrido: la ambulancia llegó a destino
            clearInterval(animacionTimer);
            animacionTimer = null;
        }
    }, 50); // Velocidad del movimiento (menor número = más rápido)
}

// --- FUNCIÓN 2: GEOCODIFICACIÓN (LIMPIA TEXTO Y BUSCA EN EL MAPA) ---
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
            
            // Si el origen estaba oculto por la simulación, nos aseguramos de mostrarlo
            if (marcadorTarget === marcadorOrigen && !mapa.hasLayer(marcadorOrigen)) {
                marcadorOrigen.addTo(mapa);
            }
            // Nos aseguramos de que el destino también esté visible
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

// --- FUNCIÓN 3: OBTENER UBICACIÓN GPS NATIVA CON RESPALDO DE EMERGENCIA ---
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
            mapa.setView([lat, lon], 16);

            dirOrigen.value = `Ubicación GPS (${lat.toFixed(4)}, ${lon.toFixed(4)})`;

            await calcularYMostrarRuta();
            
            console.log(`GPS obtenido con éxito -> Lat: ${lat}, Lon: ${lon}`);
        },
        (error) => {
            console.warn('Aviso: No se pudo obtener la ubicación automática del navegador, aplicando ubicación por defecto de Paysandú:', error);
            
            const latDefault = -32.3214;
            const lonDefault = -58.0756;

            if (!mapa.hasLayer(marcadorOrigen)) {
                marcadorOrigen.addTo(mapa);
            }

            marcadorOrigen.setLatLng([latDefault, lonDefault]);
            mapa.setView([latDefault, lonDefault], 16);
            dirOrigen.value = `Ubicación por defecto (${latDefault}, ${lonDefault})`;
            
            calcularYMostrarRuta();
        },
        { 
            enableHighAccuracy: false, 
            timeout: 20000,            
            maximumAge: 60000          
        }
    );
}

// Eventos de los botones de búsqueda (Origen y Destino simétricos)
ubicarOrigenBtn.addEventListener('click', () => buscarUbicacion(dirOrigen.value, marcadorOrigen, departamentoOrigen));
ubicarDestinoBtn.addEventListener('click', () => buscarUbicacion(dirDestino.value, marcadorDestino, departamentoDestino));

// Evento para el botón de GPS
if (ubicarGpsBtn) {
    ubicarGpsBtn.addEventListener('click', obtenerUbicacionGps);
}

// Evento para el botón de simulación
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