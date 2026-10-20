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

    // Iniciar transacción para asegurar que todas las operaciones se realicen correctamente
    $con->begin_transaction();

    // 4. CONSULTA PREPARADA PARA INSERTAR EL TRASLADO
    $sql = "INSERT INTO traslados (origen, destino, hora_salida, hora_llegada, acompaniante, estado, departamento, ci_funcionario, matricula, ci_chofer) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $con->error);
    }

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

    if (!$stmt->execute()) {
        throw new Exception("Error al registrar el traslado: " . $stmt->error);
    }

    // 5. ACTUALIZAR DISPONIBILIDAD DE AMBULANCIA Y CHOFER
    // Marcamos la ambulancia como 'En traslado'
    $stmtAmb = $con->prepare("UPDATE ambulancia SET disponibilidad_ambulancia = 'En traslado' WHERE matricula = ?");
    $stmtAmb->bind_param("s", $matricula);
    $stmtAmb->execute();
    $stmtAmb->close();

    // Marcamos el chofer como 'No disponible'
    $stmtChofer = $con->prepare("UPDATE chofer SET disponibilidad = 'No Disponible' WHERE ci_chofer = ?");
    $stmtChofer->bind_param("s", $ci_chofer);
    $stmtChofer->execute();
    $stmtChofer->close();

    // Confirmar los cambios en la base de datos
    $con->commit();

    echo "ok";

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    // Si algo falla, se deshacen los cambios
    $con->rollback();
    echo "Excepción capturada: " . $e->getMessage();
}
?>