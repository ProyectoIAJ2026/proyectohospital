<?php
require_once 'conexion.php';

$cedula = $_POST['cedula'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$usuario = $_POST['usuario'];
$contrasenia = $_POST['contrasenia'];
$rol = $_POST['rol'];

$hash = password_hash($contrasenia, PASSWORD_BCRYPT);

$stmt = $con->prepare("INSERT INTO funcionario (ci_funcionario, nombre, apellido, cargo, usuario, contrasenia) VALUES (?,?,?,?,?,?)");
$stmt->bind_param('ssssss', $cedula, $nombre, $apellido, $rol, $usuario, $hash);
$stmt->execute();

?>