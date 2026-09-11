let esAdministrador = false;
let rutaLayer = null;
let map;

const hospitales = [
  { nombre: "Hospital Maciel", lat: -34.9080, lng: -56.2081 },
  { nombre: "Hospital Escuela Litoral", lat: -32.3168, lng: -58.0772 },
  { nombre: "COMEPA", lat: -32.3183, lng: -58.0835 }
];

let traslados = [
  { id: 1, unidad: "Ambulancia 03#", origen: "Hospital Maciel", destino: "COMEPA", acompanante: "Juana", horaSalida: "06:00", horaLlegada: "10:00", estado: "En camino" }
];

// Inicialización del DOM y el mapa
document.addEventListener('DOMContentLoaded', () => {
  map = L.map('map').setView([-32.522779, -55.765835], 7);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

  const crearIcono = () => L.divIcon({ className: 'hospital-icon', html: 'H', iconSize: [24, 24] });

  hospitales.forEach(h => {
    L.marker([h.lat, h.lng], { icon: crearIcono() }).addTo(map).bindPopup(h.nombre);
    ['origen', 'destino'].forEach(id => {
      const select = document.getElementById(id);
      if (select) select.add(new Option(h.nombre, h.nombre));
    });
  });

  renderizarTraslados();
});

// Autenticación
function iniciarSesion(e) {
  e.preventDefault();
  const email = document.getElementById('login-email').value.trim().toLowerCase();
  esAdministrador = (email === "administrador01@gmail.com" && document.getElementById('login-password').value === "12345678");

  document.getElementById('login-modal').style.display = "none";
  document.getElementById('user-display').innerText = `${email} (${esAdministrador ? 'Admin' : 'Usuario'})`;
  document.getElementById('btn-logout').style.display = "inline-block";
  document.getElementById('admin-form-container').style.display = esAdministrador ? "block" : "none";
  
  setTimeout(() => map.invalidateSize(), 100);
  renderizarTraslados();
}

function cerrarSesion() {
  esAdministrador = false;
  document.getElementById('login-modal').style.display = "flex";
  document.getElementById('admin-form-container').style.display = "none";
  document.getElementById('btn-logout').style.display = "none";
  document.getElementById('user-display').innerText = "Invitado";
  if (rutaLayer) map.removeLayer(rutaLayer);
  renderizarTraslados();
}

// Renderizado y Rutas
function renderizarTraslados() {
  const contenedor = document.getElementById('lista-traslados');
  if (!contenedor) return;

  contenedor.innerHTML = traslados.map(t => `
    <div class="card" onclick="trazarRuta('${t.origen}', '${t.destino}')">
      <h3>${t.unidad}</h3>
      <p><strong>Origen:</strong> ${t.origen}</p>
      <p><strong>Destino:</strong> ${t.destino}</p>
      <p><strong>Acompañante:</strong> ${t.acompanante}</p>
      <p><strong>Horas:</strong> ${t.horaSalida} - ${t.horaLlegada}</p>
      <p><strong>Estado:</strong> ${t.estado}</p>
      ${esAdministrador ? `
        <div class="card-actions">
          <button class="btn-edit" onclick="event.stopPropagation(); cargarEdicion(${t.id})">Editar</button>
          <button class="btn-delete" onclick="event.stopPropagation(); eliminarTraslado(${t.id})">Eliminar</button>
        </div>` : ''}
    </div>
  `).join('');
}

function trazarRuta(orig, dest) {
  const h1 = hospitales.find(h => h.nombre === orig);
  const h2 = hospitales.find(h => h.nombre === dest);
  if (!h1 || !h2) return;

  if (rutaLayer) map.removeLayer(rutaLayer);
  rutaLayer = L.polyline([[h1.lat, h1.lng], [h2.lat, h2.lng]], { color: '#007bc7', weight: 4, dashArray: '6, 6' }).addTo(map);
  map.fitBounds(rutaLayer.getBounds(), { padding: [40, 40] });
}

// CRUD Formulario
function guardarTraslado(e) {
  e.preventDefault();
  if (!esAdministrador) return;

  const id = document.getElementById('traslado-id').value;
  const datos = {
    id: id ? Number(id) : Date.now(),
    unidad: document.getElementById('unidad').value,
    origen: document.getElementById('origen').value,
    destino: document.getElementById('destino').value,
    acompanante: document.getElementById('acompanante').value,
    horaSalida: document.getElementById('hora-salida').value,
    horaLlegada: document.getElementById('hora-llegada').value,
    estado: document.getElementById('estado').value
  };

  if (id) {
    const idx = traslados.findIndex(t => t.id == id);
    if (idx !== -1) traslados[idx] = datos;
  } else {
    traslados.push(datos);
  }

  limpiarFormulario();
  renderizarTraslados();
}

function cargarEdicion(id) {
  const t = traslados.find(item => item.id === id);
  if (!t) return;

  document.getElementById('traslado-id').value = t.id;
  document.getElementById('unidad').value = t.unidad;
  document.getElementById('origen').value = t.origen;
  document.getElementById('destino').value = t.destino;
  document.getElementById('acompanante').value = t.acompanante;
  document.getElementById('hora-salida').value = t.horaSalida;
  document.getElementById('hora-llegada').value = t.horaLlegada;
  document.getElementById('estado').value = t.estado;

  document.getElementById('form-title').innerText = "Modificar Traslado";
  document.getElementById('btn-submit').innerText = "Actualizar";
  document.getElementById('btn-cancel').style.display = "block";
}

function eliminarTraslado(id) {
  traslados = traslados.filter(t => t.id !== id);
  if (rutaLayer) map.removeLayer(rutaLayer);
  renderizarTraslados();
}

function limpiarFormulario() {
  document.getElementById('traslado-form').reset();
  document.getElementById('traslado-id').value = '';
  document.getElementById('form-title').innerText = "Nuevo Traslado";
  document.getElementById('btn-submit').innerText = "Guardar";
  document.getElementById('btn-cancel').style.display = "none";
}