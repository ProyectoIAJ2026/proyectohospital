<?php
session_start();
require_once 'ControlAcceso.php';
require_once 'conexion.php';

// Restringir acceso exclusivamente a personal clínico autorizado
restringirAcceso(['Medicina General', 'Enfermero']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recibir los datos del formulario (sin escapar: la consulta preparada ya los protege)
    $nombre_documento = $_POST['nombre_documento'] ?? '';
    $descripcion      = $_POST['descripcion'] ?? '';
    $estado           = $_POST['estado'] ?? '';
    $fecha_carga      = $_POST['fecha_carga'] ?? '';
    $ci_paciente      = $_POST['ci_paciente'] ?? '';
    $ci_funcionario   = $_POST['ci_funcionario'] ?? '';

    // Manejar la subida del archivo principal
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['archivo']['tmp_name'];
        $fileName      = $_FILES['archivo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $newFileName   = "doc_" . md5(time() . $fileName) . "." . $fileExtension;
        $uploadFileDir = '../archivos/';

        // Crear la carpeta si no existe
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path   = $uploadFileDir . $newFileName;
        $archivo_url = 'archivos/' . $newFileName; // Ruta relativa para la base de datos y vista previa

        if (move_uploaded_file($fileTmpPath, $dest_path)) {

            // En la base se guarda la ruta corta; la URL completa se arma con $base_url
            $texto_qr = $archivo_url;

            // Inserción con consulta preparada (previene SQL Injection)
            $stmt = mysqli_prepare($con,
                "INSERT INTO documento (titulo, archivo_url, descripcion, estado, codigo_qr, fecha_carga, ci_funcionario, cedula)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssss",
                $nombre_documento, $archivo_url, $descripcion, $estado,
                $texto_qr, $fecha_carga, $ci_funcionario, $ci_paciente);

            $resultado = mysqli_stmt_execute($stmt);

            if ($resultado) {
                echo json_encode([
                    "success" => true,
                    "message" => "Documento cargado correctamente.",
                    "texto_qr" => $base_url . $archivo_url,
                    "archivo_url" => $archivo_url
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error al guardar en la base de datos: " . mysqli_stmt_error($stmt)
                ]);
            }
            mysqli_stmt_close($stmt);

        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error al mover el archivo subido al servidor."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Faltan datos obligatorios o el archivo no se adjuntó correctamente."
        ]);
    }
}
?>