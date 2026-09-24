<?php
$host = "localhost";
$user = "root";      // Usuario por defecto de XAMPP
$pass = "";          // Contraseña por defecto de XAMPP (vacía)
$db   = "hospital";  // Nombre de tu base de datos en phpMyAdmin

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_error) {
    die("Error en la conexión a la base de datos: " . $con->connect_error);
}

$con->set_charset("utf8");
?>