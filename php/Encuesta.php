<?php
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email       = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $atencion    = $_POST['atencion'] ?? '';
    $tiempo      = $_POST['tiempo'] ?? '';
    $acceso      = $_POST['acceso'] ?? '';
    $comentarios = $_POST['comentarios'] ?? '';

    $descripcion = "Atención: $atencion | Tiempo adecuado: $tiempo | Acceso: $acceso | Comentarios: $comentarios";
    if ($email) {
        $descripcion .= " | Email: $email";
    }

    $titulo = "Encuesta de Satisfacción";
    $fecha  = date('Y-m-d');
    $activa = 1;

    $sql = "INSERT INTO encuesta (titulo, fecha_creacion, activa, descripcion) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssis", $titulo, $fecha, $activa, $descripcion);

        if ($stmt->execute()) {
            http_response_code(200);
            echo "OK";
        } else {
            http_response_code(500);
            echo "Error en la base de datos: " . $stmt->error;
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo "Error en la consulta: " . $con->error;
    }
}

$con->close();
?>