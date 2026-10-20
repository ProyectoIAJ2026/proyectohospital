<?php
// 1. CONEXIÓN A LA BASE DE DATOS Y CONFIGURACIÓN
require_once 'conexion.php';

// 2. VERIFICAR QUE LA SOLICITUD SEA POR POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método de solicitud no permitido.";
    exit();
}

try {
    // 3. CAPTURA Y LIMPIEZA DE LOS DATOS DEL FORMULARIO
    $matricula               = trim($_POST['matricula'] ?? '');
    $estado                  = $_POST['estado'] ?? 'Operativa';
    $disponibilidad_ambulancia = $_POST['disponibilidad_ambulancia'] ?? 'Disponible';
    
    // El campo de mantenimiento es opcional, si viene vacío asignamos null
    $ultimo_mantenimiento    = !empty($_POST['ultimo_mantenimiento']) ? $_POST['ultimo_mantenimiento'] : null;

    // Validar que la matrícula no esté vacía (es la clave primaria)
    if (empty($matricula)) {
        echo "Por favor ingrese la matrícula de la ambulancia.";
        exit();
    }

    // 4. CONSULTA PREPARADA PARA INSERTAR LA AMBULANCIA
    $sql = "INSERT INTO ambulancia (matricula, estado, disponibilidad_ambulancia, ultimo_mantenimiento) 
            VALUES (?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $con->error;
        exit();
    }

    // Vincular los parámetros (4 cadenas de texto, el último puede ser null)
    $stmt->bind_param(
        "ssss",
        $matricula,
        $estado,
        $disponibilidad_ambulancia,
        $ultimo_mantenimiento
    );

    // 5. EJECUTAR Y RESPONDER
    if ($stmt->execute()) {
        echo "ok"; // Respuesta para que tu JavaScript detecte el éxito
    } else {
        echo "Error al registrar la ambulancia: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>