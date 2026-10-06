<?php
header("Content-type: application/json");

require_once 'conexion.php';

if(!isset)($_FILES['archivo']) || !isset($_POST['nombre'])) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos: archivo o nombre."
    ]);
    exit;
}

$archivo = $_FILES['archivo'];
$nombre = trim($_POST['nombre']);

if (empty($nombre)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "El nombre del documento es obligatorio."
    ]);
    exit;
}

if archivo["error"] === 0) {
    $extension = "strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION))";

    $extensiones_permitidas = ['pdf', 'jpeg', 'docx', 'jpg', 'png', 'doc'];
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Extension" $extension "no permitida Solo: " implode(", ", $extensiones_permitidas)

        ]);
        exit;
    }
$nombre_unico = uniqid() . "." . $extension;

$directorio = _DIR_ . "/archivos/"; 

if (!is_dir($directorio)) {
    if (!mkdir($directorio, 0777, true)) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Error al crear el directorio de archivos: " . $directorio
        ]);
        exit;
    }
}

if (!is_writable($directorio)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "El directorio de archivos no tiene permisos de escritura: " . $directorio
    ]);
    exit;
}

$ruta_completa = $directorio . $nombre_unico;
$ruta_bd = "archivos/" . $nombre_unico;

if(move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    $stmt = $con->prepare("INSERT INTO documentos (titulo, archivo_nombre, archivo_ruta) VALUES (?, ?,?)");
if(!stmt){
    echo json_encode([
        "exito" => false,
        "mensaje" => "Error al preparar la consulta: " . $con->error
    ]);
    exit;
}

    