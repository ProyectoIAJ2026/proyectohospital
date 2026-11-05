<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

if (!isset($_SESSION['cedula'])) {
    header('Location: ../index.html');
    exit;
}

$cedula = $_SESSION['cedula'];
$stmt = $con->prepare("SELECT estado FROM funcionario WHERE ci_funcionario = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    if ($row['estado'] !== 'Activo') {
        session_unset();
        session_destroy();
        header('Location: ../index.html?error=cuenta_pendiente');
        exit;
    }
} else {
    session_unset();
    session_destroy();
    header('Location: ../index.html');
    exit;
}
?>