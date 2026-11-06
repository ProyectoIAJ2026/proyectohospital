<?php
header('Content-Type: application/json; charset=utf-8');

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host_url  = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos($host_url, 'localhost') !== false || strpos($host_url, '127.0.0.1') !== false) {
    $host_url = gethostbyname(gethostname());
}

echo json_encode([
    "url_encuesta" => $protocolo . "://" . $host_url . "/ProyectoFinal/proyectohospital/pages/Encuesta.html"
]);
?>