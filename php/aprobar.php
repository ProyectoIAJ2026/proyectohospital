<?php
session_start();
require_once 'conexion.php';

// Solo Gerencia puede aprobar cuentas
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'funcionario' || $_SESSION['cargo'] !== 'Gerencia') {
    header("Location: ../pages/IniciarSesion.html");
    exit();
}

$tabla = $_GET['tabla'] ?? '';
$ci    = $_GET['ci'] ?? '';

if (($tabla === 'funcionario' || $tabla === 'chofer') && !empty($ci)) {

    $columnaCi = ($tabla === 'funcionario') ? 'ci_funcionario' : 'ci_chofer';

    $stmt = $con->prepare("UPDATE $tabla SET estado = 'Activo' WHERE $columnaCi = ?");
    $stmt->bind_param("s", $ci);

    if ($stmt->execute()) {
        $stmt->close();
        $con->close();
        header("Location: ../pages/PanelAdmin.php?exito=aprobado");
        exit();
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
        $stmt->close();
    }
} else {
    echo "Parámetros inválidos.";
}

$con->close();
?>