<?php
// 1. CONEXIÓN A LA BASE DE DATOS
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');

$tipoUsuario = $_POST['tipoUsuario'] ?? '';

if (empty($tipoUsuario)) {
    echo "Tipo de usuario no especificado.";
    exit();
}

try {
    $cedula = $_POST['cedula'] ?? '';

    if (empty($cedula)) {
        echo "La cédula es requerida.";
        exit();
    }

    // 2. CONSULTA (SELECT): COMPROBAR SI LA CÉDULA YA EXISTE
    $tabla = '';
    $columnaCi = '';

    if ($tipoUsuario === 'funcionario') {
        $tabla = 'funcionario';
        $columnaCi = 'ci_funcionario';
    } else if ($tipoUsuario === 'paciente') {
        $tabla = 'paciente';
        $columnaCi = 'cedula';
    } else if ($tipoUsuario === 'chofer') {
        $tabla = 'chofer';
        $columnaCi = 'ci_chofer';
    }

    if ($tabla !== '') {
        $checkCiStmt = $con->prepare("SELECT $columnaCi FROM $tabla WHERE $columnaCi = ?");
        $checkCiStmt->bind_param("s", $cedula);
        $checkCiStmt->execute();
        $checkCiStmt->store_result();

        // Cancela si la cédula ya está registrada
        if ($checkCiStmt->num_rows > 0) {
            echo "Error: La cédula $cedula ya se encuentra registrada.";
            $checkCiStmt->close();
            $con->close();
            exit();
        }
        $checkCiStmt->close();
    }

    // 3. CONSULTA (SELECT): COMPROBAR SI EL NOMBRE DE USUARIO YA EXISTE (solo funcionarios)
    if ($tipoUsuario === 'funcionario') {
        $usuario = $_POST['usuario'] ?? '';

        if (!empty($usuario)) {
            $checkUserStmt = $con->prepare("SELECT usuario FROM funcionario WHERE usuario = ?");
            $checkUserStmt->bind_param("s", $usuario);
            $checkUserStmt->execute();
            $checkUserStmt->store_result();

            if ($checkUserStmt->num_rows > 0) {
                echo "Error: El nombre de usuario '$usuario' ya está en uso. Por favor elija otro.";
                $checkUserStmt->close();
                $con->close();
                exit();
            }
            $checkUserStmt->close();
        }
    }

    // 4. CONSULTA (INSERT): PREPARAR Y GUARDAR LOS DATOS
    if ($tipoUsuario === 'funcionario') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $email = $_POST['email'];
        $usuario = $_POST['usuario'];
        $contrasenia = $_POST['contrasenia'];
        $cargo = $_POST['cargo'];

        if ($cargo === 'Medico' || $cargo === 'Medicina General') {
            $cargo = 'Medicina General';
        } else if ($cargo === 'Administrador' || $cargo === 'Gerencia') {
            $cargo = 'Gerencia';
        }

        // Encripta la contraseña por seguridad
        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);

        $stmt = $con->prepare("INSERT INTO funcionario (ci_funcionario, nombre, apellido, email, cargo, usuario, contrasenia) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $cedula, $nombre, $apellido, $email, $cargo, $usuario, $hash);

    } else if ($tipoUsuario === 'paciente') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $contrasenia = $_POST['contrasenia'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);

        $stmt = $con->prepare("INSERT INTO paciente (cedula, nombre, apellido, contrasenia, telefono, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cedula, $nombre, $apellido, $hash, $telefono, $email);

    } else if ($tipoUsuario === 'chofer') {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $email = $_POST['email'];
        $contrasenia = $_POST['contrasenia'];
        $disponibilidad = $_POST['disponibilidad'];

        $hash = password_hash($contrasenia, PASSWORD_BCRYPT);

        $stmt = $con->prepare("INSERT INTO chofer (ci_chofer, nombre, apellido, email, contrasenia, disponibilidad) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cedula, $nombre, $apellido, $email, $hash, $disponibilidad);
    }

    // 5. RESPUESTA AL JAVASCRIPT
    if ($stmt->execute()) {
        echo "ok"; // Le confirma a JS que se guardó correctamente
    } else {
        echo "Error al registrar: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>