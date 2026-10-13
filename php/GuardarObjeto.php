<?php
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método no permitido.";
    exit();
}

// Captura de variables del formulario
$tipo = trim($_POST['tipo_recurso'] ?? 'Objeto');
$nombre = trim($_POST['nombre_objeto'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if (empty($nombre)) {
    echo "El nombre del recurso es obligatorio.";
    exit();
}

// Iniciar transacción para garantizar consistencia entre las tablas
$con->begin_transaction();

try {
    // 1. Insertar primero en la tabla PADRE (elemento)
    // Nota: Asegúrate de que en la tabla 'elemento' el campo 'id_traslados' permita NULL si no se asigna en este momento.
    $sqlElemento = "INSERT INTO elemento (tipo) VALUES (?)";
    $stmt1 = $con->prepare($sqlElemento);
    $stmt1->bind_param("s", $tipo);
    $stmt1->execute();

    // Obtener el ID de elemento recién creado
    $idElemento = $con->insert_id;
    $stmt1->close();

    // 2. Insertar en la tabla HIJA (objeto) utilizando las columnas separadas
    $sqlObjeto = "INSERT INTO objeto (id_elemento, nombre, descripcion) VALUES (?, ?, ?)";
    $stmt2 = $con->prepare($sqlObjeto);
    $stmt2->bind_param("iss", $idElemento, $nombre, $descripcion);
    $stmt2->execute();
    $stmt2->close();

    // Confirmar los cambios si todo salió bien
    $con->commit();
    echo "ok";

} catch (Exception $e) {
    // Si algo falla, revertimos la transacción
    $con->rollback();
    echo "Error al registrar el recurso: " . $e->getMessage();
}

$con->close();
?>