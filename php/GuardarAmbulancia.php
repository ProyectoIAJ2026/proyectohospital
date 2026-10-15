<?php
// 1. CONEXIÓN Y CONFIGURACIÓN
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');

// 2. VERIFICAR MÉTODO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método no permitido.";
    exit();
}

try {
    // 3. CAPTURA Y LIMPIEZA DE DATOS
    $matricula = trim($_POST['matricula'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Operativa');
    $disponibilidad = trim($_POST['disponibilidad_ambulancia'] ?? 'Disponible');
    $mantenimiento = !empty($_POST['ultimo_mantenimiento']) ? $_POST['ultimo_mantenimiento'] : null;

    if (empty($matricula)) {
        echo "La matrícula es obligatoria.";
        exit();
    }

    // 4. INSERCIÓN SQL PREPARADA
    $sql = "INSERT INTO ambulancia (matricula, estado, disponibilidad_ambulancia, ultimo_mantenimiento) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación: " . $con->error;
        exit();
    }

    $stmt->bind_param("ssss", $matricula, $estado, $disponibilidad, $mantenimiento);

    // 5. RESPUESTA A JAVASCRIPT
    if ($stmt->execute()) {
        echo "ok";
    } else {
        if ($con->errno === 1062) {
            echo "Error: La matrícula '$matricula' ya está registrada.";
        } else {
            echo "Error al guardar: " . $stmt->error;
        }
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage();
}
?>