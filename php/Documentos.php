<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexion.php';

// Manejo de petición GET (Obtener lista de documentos)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id_documento, titulo, archivo_url, descripcion, estado, fecha_carga, ci_funcionario, cedula FROM documento ORDER BY id_documento DESC";
    $resultado = $con->query($sql);

    $documentos = [];
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $documentos[] = $row;
        }
    }
    echo json_encode($documentos);
    exit;
}

// Manejo de petición POST (Cargar un nuevo documento)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar recepción de campos obligatorios
    if (!isset($_FILES['archivo_url']) || empty($_POST['titulo']) || empty($_POST['fecha_carga']) || empty($_POST['ci_funcionario'])) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Faltan datos obligatorios para registrar el documento."
        ]);
        exit;
    }

    $titulo         = trim($_POST['titulo']);
    $descripcion    = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $estado         = isset($_POST['estado']) ? trim($_POST['estado']) : 'Activo';
    $fecha_carga    = $_POST['fecha_carga'];
    $ci_funcionario = trim($_POST['ci_funcionario']);
    $cedula         = !empty($_POST['cedula']) ? trim($_POST['cedula']) : null;
    $archivo        = $_FILES['archivo_url'];

    // Validar errores en la subida del archivo
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Error en la recepción del archivo subido."
        ]);
        exit;
    }

    // Validar extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['pdf', 'jpeg', 'docx', 'jpg', 'png', 'doc'];

    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Extensión ." . $extension . " no permitida. Formatos válidos: " . implode(", ", $extensiones_permitidas)
        ]);
        exit;
    }

    // Corrección crítica: Uso de __DIR__ con doble guion bajo
    $directorio = __DIR__ . "/archivos/";
    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0777, true)) {
            echo json_encode([
                "exito" => false,
                "mensaje" => "No se pudo crear el directorio para almacenar archivos."
            ]);
            exit;
        }
    }

    // Guardar el archivo
    $nombre_unico = uniqid('doc_') . "." . $extension;
    $ruta_completa = $directorio . $nombre_unico;
    $ruta_bd = "php/archivos/" . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        $codigo_qr = "QR_" . $nombre_unico;

        $stmt = $con->prepare("INSERT INTO documento (titulo, archivo_url, descripcion, estado, codigo_qr, fecha_carga, ci_funcionario, cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al preparar la consulta SQL: " . $con->error
            ]);
            exit;
        }

        $stmt->bind_param("ssssssss", $titulo, $ruta_bd, $descripcion, $estado, $codigo_qr, $fecha_carga, $ci_funcionario, $cedula);

        if ($stmt->execute()) {
            echo json_encode([
                "exito" => true,
                "mensaje" => "Documento registrado exitosamente."
            ]);
        } else {
            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al insertar en la base de datos: " . $stmt->error
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Error al guardar el archivo en el servidor."
        ]);
    }
}
?>