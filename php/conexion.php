<?php
$host = "localhost";
$user = "root";      // Usuario por defecto de XAMPP
$pass = "";          // Contraseña por defecto de XAMPP (vacía)
$db   = "hospital";  // Nombre de base de datos en phpMyAdmin

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_error) {
    die("Error en la conexión a la base de datos: " . $con->connect_error);
}

$con->set_charset("utf8");

// URL base del proyecto, usada para armar los enlaces de los QR
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host_url  = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Si se entra por localhost, el celular no podría abrir el QR:
// se reemplaza por la IP de la PC en la red local
if (strpos($host_url, 'localhost') !== false || strpos($host_url, '127.0.0.1') !== false) {
    $host_url = gethostbyname(gethostname());
}

$base_url = $protocolo . "://" . $host_url . "/ProyectoFinal/proyectohospital/";
?>