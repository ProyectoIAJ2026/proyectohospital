<?php
// Incluimos la conexión a la base de datos
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recibir los datos del formulario
    $nombre_documento = mysqli_real_escape_string($con, $_POST['nombre_documento']);
    $descripcion      = mysqli_real_escape_string($con, $_POST['descripcion']);
    $estado           = mysqli_real_escape_string($con, $_POST['estado']);
    $fecha_carga      = mysqli_real_escape_string($con, $_POST['fecha_carga']);
    $ci_paciente      = mysqli_real_escape_string($con, $_POST['ci_paciente']);
    $ci_funcionario   = mysqli_real_escape_string($con, $_POST['ci_funcionario']);

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
        
        $dest_path = $uploadFileDir . $newFileName;
        $archivo_url = 'archivos/' . $newFileName; // Ruta relativa para la base de datos y vista previa

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            
            // Texto descriptivo para el código QR
            $texto_qr = "http://192.168.1.5/ProyectoFinal/proyectohospital/" . $archivo_url;

            // Inserción en la base de datos
            $query = "INSERT INTO documento (titulo, archivo_url, descripcion, estado, codigo_qr, fecha_carga, ci_funcionario, cedula) 
                      VALUES ('$nombre_documento', '$archivo_url', '$descripcion', '$estado', '$texto_qr', '$fecha_carga', '$ci_funcionario', '$ci_paciente')";
            
            $resultado = mysqli_query($con, $query);

            if ($resultado) {
                echo json_encode([
                    "success" => true,
                    "message" => "Documento cargado correctamente.",
                    "texto_qr" => $texto_qr,
                    "archivo_url" => $archivo_url
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error al guardar en la base de datos: " . mysqli_error($con)
                ]);
            }

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