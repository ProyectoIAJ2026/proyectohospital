<?php
require_once 'conexion.php';
date_default_timezone_set('America/Montevideo');

$tipoUsuario = $_POST['tipoUsuario'] ?? '';

if (empty($tipoUsuario)) {
    echo "Tipo de usuario no especificado.";
    exit();
}

try {
    if ($tipoUsuario === 'funcionario') {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $usuario = $_POST['usuario'];
        $contrasenia = $_POST['contrasenia'];
        $cargo = $_POST['cargo'];

        // MAPEO DE CARGOS:
        if ($cargo === 'Medico' || $cargo === 'Medicina General') {
            $cargo = 'Medicina General';
        } else if ($cargo === 'Administrador' || $cargo === 'Gerencia') {
            $cargo = 'Gerencia';
        }

        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);
        $stmt = $con->prepare("INSERT INTO funcionario (ci_funcionario, nombre, apellido, cargo, usuario, contrasenia) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cedula, $nombre, $apellido, $cargo, $usuario, $hash);

    } else if ($tipoUsuario === 'paciente') {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $contrasenia = $_POST['contrasenia'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);

        // Inserción en la tabla paciente con 6 columnas
        $stmt = $con->prepare("INSERT INTO paciente (cedula, nombre, apellido, contrasenia, telefono, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cedula, $nombre, $apellido, $hash, $telefono, $email);

    } else if ($tipoUsuario === 'chofer') {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $contrasenia = $_POST['contrasenia'];
        $disponibilidad = $_POST['disponibilidad'];

        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);

        $stmt = $con->prepare("INSERT INTO chofer (ci_chofer, nombre, apellido, contrasenia, disponibilidad) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $cedula, $nombre, $apellido, $hash, $disponibilidad);
    }

    // Ejecución de la consulta preparada
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error al registrar: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>