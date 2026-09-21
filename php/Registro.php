<?php
require_once 'conexion.php';
date_default_timezone_set('America/Montevideo'); //[cite: 9]

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

        // Encriptar la contraseña[cite: 9]
        $hash = password_hash($contrasenia, PASSWORD_BCRYPT); //[cite: 9]

        // Guardar en la tabla 'funcionario' (según MER/Normalización)[cite: 7]
        $stmt = $con->prepare("INSERT INTO funcionario (CI_Funcionario, Nombre, Apellido, Cargo, Usuario, Contrasena) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cedula, $nombre, $apellido, $cargo, $usuario, $hash);

    } else if ($tipoUsuario === 'paciente') {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        // Guardar en la tabla 'paciente'[cite: 7]
        $stmt = $con->prepare("INSERT INTO paciente (CI, Nombre, Telefono, Email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $cedula, $nombre, $telefono, $email);

    } else if ($tipoUsuario === 'chofer') {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $contrasenia = $_POST['contrasenia'];
        $disponibilidad = $_POST['disponibilidad'];

        // Encriptar la contraseña si manejan login para chofer[cite: 9]
        $hash = password_hash($contrasenia, PASSWORD_BCRYPT); //[cite: 9]

        // Guardar en la tabla 'chofer'[cite: 7]
        $stmt = $con->prepare("INSERT INTO chofer (CI_Chofer, Disponibilidad) VALUES (?, ?)");
        $stmt->bind_param("ss", $cedula, $disponibilidad);
    }

    // Ejecución de la consulta preparada[cite: 9]
    if ($stmt->execute()) {
        echo "ok"; // Respuesta que espera la función fetch[cite: 8, 9]
    } else {
        echo "Error al registrar: " . $stmt->error; //[cite: 9]
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>