<?php
// 1. CONEXIÓN A LA BASE DE DATOS Y CONFIGURACIÓN
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');

// 2. VERIFICAR QUE LA SOLICITUD SEA POR POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método de solicitud no permitido.";
    exit();
}

try {
    // 3. CAPTURA Y LIMPIEZA DE LOS DATOS DEL FORMULARIO
    $origen          = trim($_POST['origen'] ?? '');
    $destino         = trim($_POST['destino'] ?? '');
    $departamento    = trim($_POST['departamento'] ?? '');
    $hora_salida     = $_POST['hora_salida'] ?? '';
    $hora_llegada    = !empty($_POST['hora_llegada']) ? $_POST['hora_llegada'] : null;
    $acompaniante    = !empty($_POST['acompaniante']) ? trim($_POST['acompaniante']) : null;
    $estado          = $_POST['estado'] ?? 'Pendiente';
    $ci_funcionario  = $_POST['ci_funcionario'] ?? '';
    $matricula       = $_POST['matricula'] ?? '';
    $ci_chofer       = $_POST['ci_chofer'] ?? '';

    // Validar campos obligatorios
    if (empty($origen) || empty($destino) || empty($departamento) || empty($hora_salida) || empty($ci_funcionario) || empty($matricula) || empty($ci_chofer)) {
        echo "Por favor complete todos los campos obligatorios.";
        exit();
    }

    // 4. CONSULTA PREPARADA PARA INSERTAR EL TRASLADO
    $sql = "INSERT INTO traslados (origen, destino, hora_salida, hora_llegada, acompaniante, estado, departamento, ci_funcionario, matricula, ci_chofer) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $con->error;
        exit();
    }

    // Vincular los parámetros (10 valores)
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

    // 5. EJECUTAR Y RESPONDER
    if ($stmt->execute()) {
        echo "ok"; // Respuesta que lee tu JavaScript para mostrar la alerta de éxito
    } else {
        echo "Error al registrar el traslado: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>