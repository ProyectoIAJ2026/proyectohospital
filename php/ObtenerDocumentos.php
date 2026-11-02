<?php
session_start();
require_once 'ControlAcceso.php';
require_once 'conexion.php';

// Restringir acceso exclusivamente a personal clínico autorizado
restringirAcceso(['Medicina General', 'Enfermero', 'Gerencia']);

header('Content-Type: application/json; charset=utf-8');

$query = "SELECT id_documento, titulo, archivo_url, codigo_qr FROM documento ORDER BY id_documento DESC";
$resultado = mysqli_query($con, $query);

$documentos = [];

if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $documentos[] = $row;
    }
    echo json_encode([
        "success" => true,
        "documentos" => $documentos
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener los documentos."
    ]);
}
?>