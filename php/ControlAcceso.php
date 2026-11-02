<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function restringirAcceso($cargosPermitidos) {
    // 1. Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['rol']) || !isset($_SESSION['cargo'])) {
        echo "<script>alert('Debe iniciar sesión para acceder a esta sección.'); window.location.href = '../html/IniciarSesion.html';</script>";
        exit();
    }

    // 2. Normalizar el cargo del usuario a minúsculas y sin espacios sobrantes
    $cargoActual = strtolower(trim($_SESSION['cargo']));

    // 3. Convertir los cargos permitidos en array y normalizarlos también
    $cargosPermitidos = (array) $cargosPermitidos;
    $cargosPermitidos = array_map(function($cargo) {
        return strtolower(trim($cargo));
    }, $cargosPermitidos);

    // 4. Verificar si el cargo normalizado está dentro de los permitidos
    if (!in_array($cargoActual, $cargosPermitidos)) {
        echo "<script>alert('Acceso denegado. No tiene los permisos necesarios para ver este módulo.'); window.location.href = '../index.html';</script>";
        exit();
    }
}
?>