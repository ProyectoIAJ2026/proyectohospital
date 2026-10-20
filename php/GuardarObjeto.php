<?php
// Requerir el archivo de conexión (asegúrate de que la ruta sea correcta desde la carpeta php)
require_once 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_objeto']);
    $tipo = trim($_POST['tipo_recurso']);
    $descripcion = trim($_POST['descripcion']);

    if (empty($nombre) || empty($tipo)) {
        echo "Error: Completa todos los campos obligatorios.";
        exit;
    }

    try {
        // 1. Insertar primero en la tabla 'elemento' usando consultas preparadas de mysqli
        $stmtElemento = $con->prepare("INSERT INTO elemento (tipo) VALUES (?)");
        $stmtElemento->bind_param("s", $tipo);
        $stmtElemento->execute();

        // Obtener el ID generado automáticamente en 'elemento'
        $id_elemento = $con->insert_id;
        $stmtElemento->close();

        // 2. Insertar en la tabla 'objeto'
        $stmtObjeto = $con->prepare("INSERT INTO objeto (id_elemento, nombre, descripcion) VALUES (?, ?, ?)");
        $stmtObjeto->bind_param("iss", $id_elemento, $nombre, $descripcion);
        $stmtObjeto->execute();
        $stmtObjeto->close();

        // Si todo sale bien, devolvemos "ok" para que el JS muestre la alerta
        echo "ok";

    } catch (Exception $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    echo "Método de solicitud no válido.";
}
?>