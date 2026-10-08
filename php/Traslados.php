<?php
// 1. Incluir el archivo de conexión
require_once 'conexion.php';

// 2. Verificar que los datos vengan por el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 3. Capturar y limpiar los datos recibidos del formulario
    $origen         = trim($_POST['origen'] ?? '');
    $destino        = trim($_POST['destino'] ?? '');
    $departamento   = trim($_POST['departamento'] ?? '');
    $hora_salida    = $_POST['hora_salida'] ?? null;
    $hora_llegada   = !empty($_POST['hora_llegada']) ? $_POST['hora_llegada'] : null;
    $acompaniante   = !empty($_POST['acompaniante']) ? trim($_POST['acompaniante']) : null;
    $estado         = $_POST['estado'] ?? 'Pendiente';
    $ci_funcionario = $_POST['ci_funcionario'] ?? '';
    $matricula      = $_POST['matricula'] ?? '';
    $ci_chofer      = $_POST['ci_chofer'] ?? '';

    // 4. Validar campos obligatorios básicos
    if (empty($origen) || empty($destino) || empty($departamento) || empty($hora_salida) || empty($ci_funcionario) || empty($matricula) || empty($ci_chofer)) {
        die("Por favor complete todos los campos obligatorios.");
    }

    // 5. Preparar la consulta SQL
    $sql = "INSERT INTO traslados (origen, destino, hora_salida, hora_llegada, acompaniante, estado, departamento, ci_funcionario, matricula, ci_chofer) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if ($stmt) {
        // Asignar los 10 parámetros
        $stmt->bind_param(
            "ssssssssss",
            $origen,
            $destino,
            $hora_salida,
            $hora_llegada,
            $acompaniante,
            $estado,
            $departamento,
            $ci_funcionario,
            $matricula,
            $ci_chofer
        );

        // 6. Ejecutar la consulta y verificar resultado
        if ($stmt->execute()) {
            echo "<script>
                    alert('¡Traslado registrado con éxito!');git
                    window.location.href = '../html/Traslados.html';
                  </script>";
        } else {
            echo "Error al guardar el traslado: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta: " . $con->error;
    }

    $con->close();
} else {
    header("Location: ../html/Traslados.html");
    exit();
}
?>