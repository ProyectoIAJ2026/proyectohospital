<?php
session_start();
require_once 'conexion.php';

// Verificar que sea la gerencia quien realiza la acción
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'funcionario' || $_SESSION['cargo'] !== 'Gerencia') {
    header("Location: ../html/IniciarSesion.html");
    exit();
}

$tabla = $_GET['tabla'] ?? '';
$ci = $_GET['ci'] ?? '';

if (($tabla === 'funcionario' || $tabla === 'chofer') && !empty($ci)) {
    
    $columnaCi = ($tabla === 'funcionario') ? 'ci_funcionario' : 'ci_chofer';

    $stmt = $con->prepare("UPDATE $tabla SET estado = 'activo' WHERE $columnaCi = ?");
    $stmt->bind_param("s", $ci);

    if ($stmt->execute()) {
        $stmt->close();
        $con->close();
        // Redirigir de vuelta al panel con éxito
        header("Location: PanelAdmin.php?exito=aprobado");
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