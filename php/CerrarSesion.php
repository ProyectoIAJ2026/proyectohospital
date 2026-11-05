<?php
session_start(); // Inicializar la sesión existente

// Limpiar todas las variables de la sesión
$_SESSION = array();

// Si se utilizan cookies para las sesiones, eliminarlas también por seguridad
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión en el servidor
session_destroy();

// Redirigir de regreso al index.html principal
header("Location: ../index.html");
exit();
?>