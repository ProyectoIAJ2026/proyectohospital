<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

function responderNoAutorizado($mensaje) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["success" => false, "message" => $mensaje]);
    exit;
}

// 1. Verificar que haya sesión iniciada
if (!isset($_SESSION['cedula'])) {
    responderNoAutorizado("Sesión no iniciada. Volvé a iniciar sesión.");
}

// 2. Verificar que el funcionario exista y esté activo
$cedula = $_SESSION['cedula'];
$stmt = $con->prepare("SELECT estado, cargo FROM funcionario WHERE ci_funcionario = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();

$cargoActual = null;

if ($row = $res->fetch_assoc()) {
    // Validamos el estado de forma flexible ante mayúsculas/minúsculas o espacios
    if (strtolower(trim($row['estado'])) !== 'activo') {
        session_unset();
        session_destroy();
        responderNoAutorizado("Tu cuenta está pendiente de activación.");
    }
    $cargoActual = $row['cargo'];
} else {
    session_unset();
    session_destroy();
    responderNoAutorizado("Usuario no encontrado.");
}

// 3. Función que restringe el acceso recibiendo un array de cargos
function restringirAcceso(array $cargosPermitidos) {
    global $cargoActual;

    $permitidos = array_map('mb_strtolower', $cargosPermitidos);

    if (!in_array(mb_strtolower((string)$cargoActual), $permitidos, true)) {
        responderNoAutorizado("No tenés permisos para realizar esta acción.");
    }
}
?>