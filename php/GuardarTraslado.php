<?php
require_once 'conexion.php';

// Capturar los datos del formulario POST
$origen               = trim($_POST['origen'] ?? '');
$destino              = trim($_POST['destino'] ?? '');
$hora_salida          = $_POST['hora_salida'] ?? null;
$hora_llegada         = !empty($_POST['hora_llegada']) ? $_POST['hora_llegada'] : null;
$acompaniante         = !empty($_POST['acompaniante']) ? trim($_POST['acompaniante']) : null;
$estado               = $_POST['estado'] ?? 'Pendiente';

// Capturar origen y destino independientes
$departamento_origen  = trim($_POST['departamento'] ?? ''); // Viene del select id="departamento_origen" con name="departamento"
$departamento_destino = trim($_POST['departamento_destino'] ?? ''); // Viene del select id="departamento_destino" con name="departamento_destino"

$ci_funcionario       = $_POST['ci_funcionario'] ?? null;
$matricula            = $_POST['matricula'] ?? null;
$ci_chofer            = $_POST['ci_chofer'] ?? null;

// Validar campos obligatorios
if (empty($origen) || empty($destino) || empty($hora_salida) || empty($departamento_origen) || empty($departamento_destino) || empty($ci_funcionario) || empty($matricula) || empty($ci_chofer)) {
    echo "Por favor complete todos los campos obligatorios.";
    exit;
}

// Iniciar transacción
$con->begin_transaction();

try {
    // 1. Insertar el registro de traslado con ambos departamentos
    $sqlTraslado = "INSERT INTO traslados (origen, destino, hora_salida, hora_llegada, acompaniante, estado, departamento_origen, departamento_destino, ci_funcionario, matricula, ci_chofer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmtTraslado = $con->prepare($sqlTraslado);
    
    // 11 tipos string ("sssssssssss")
    $stmtTraslado->bind_param(
        "sssssssssss", 
        $origen, 
        $destino, 
        $hora_salida, 
        $hora_llegada, 
        $acompaniante, 
        $estado, 
        $departamento_origen, 
        $departamento_destino, 
        $ci_funcionario, 
        $matricula, 
        $ci_chofer
    );
    
    if (!$stmtTraslado->execute()) {
        throw new Exception("Error al registrar el traslado: " . $stmtTraslado->error);
    }

    // 2. Actualizar disponibilidad de la ambulancia
    $sqlAmbulancia = "UPDATE ambulancia SET disponibilidad_ambulancia = 'En traslado' WHERE matricula = ?";
    $stmtAmbulancia = $con->prepare($sqlAmbulancia);
    $stmtAmbulancia->bind_param("s", $matricula);
    if (!$stmtAmbulancia->execute()) {
        throw new Exception("Error al actualizar la ambulancia: " . $stmtAmbulancia->error);
    }

    // 3. Actualizar disponibilidad del chofer
    $sqlChofer = "UPDATE chofer SET disponibilidad = 'No Disponible' WHERE ci_chofer = ?";
    $stmtChofer = $con->prepare($sqlChofer);
    $stmtChofer->bind_param("s", $ci_chofer);
    if (!$stmtChofer->execute()) {
        throw new Exception("Error al actualizar la disponibilidad del chofer: " . $stmtChofer->error);
    }

    // Confirmar cambios
    $con->commit();
    echo "ok";

} catch (Exception $e) {
    // Revertir en caso de error
    $con->rollback();
    echo $e->getMessage();
}

$con->close();
?>