<?php
// Evitar que salidas accidentales o errores corrompan la respuesta JSON
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';
session_start();

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasenia = isset($_POST['contrasenia']) ? trim($_POST['contrasenia']) : '';

if ($usuario === '' || $contrasenia === '') {
    ob_end_clean();
    echo json_encode(['error' => 'Por favor complete todos los campos']);
    exit;
}

if (!$con) {
    ob_end_clean();
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$rolEncontrado = '';

// --- PASO 1: Consultar la tabla "paciente" ---
$stmt = $con->prepare("SELECT cedula, nombre, apellido, email, contrasenia FROM paciente WHERE cedula = ? OR email = ?");

if (!$stmt) {
    ob_end_clean();
    echo json_encode(['error' => 'Error en la consulta: ' . $con->error]);
    exit;
}

$stmt->bind_param('ss', $usuario, $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $rolEncontrado = 'paciente';
} else {
    $stmt->close();
    
    // --- PASO 2: Consultar la tabla "chofer" ---
    $stmt = $con->prepare("SELECT ci_chofer AS cedula, nombre, apellido, email, contrasenia, estado FROM chofer WHERE ci_chofer = ? OR email = ?");
    if (!$stmt) {
        ob_end_clean();
        echo json_encode(['error' => 'Error en la consulta: ' . $con->error]);
        exit;
    }
    
    $stmt->bind_param('ss', $usuario, $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $rolEncontrado = 'chofer';
    } else {
        $stmt->close();
        
        // --- PASO 3: Consultar la tabla "funcionario" ---
        $stmt = $con->prepare("SELECT ci_funcionario AS cedula, nombre, apellido, email, usuario, cargo, contrasenia, estado FROM funcionario WHERE ci_funcionario = ? OR email = ? OR usuario = ?");
        if (!$stmt) {
            ob_end_clean();
            echo json_encode(['error' => 'Error en la consulta: ' . $con->error]);
            exit;
        }
        
        $stmt->bind_param('sss', $usuario, $usuario, $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $rolEncontrado = 'funcionario';
        }
    }
}

if ($resultado->num_rows === 0 || $rolEncontrado === '') {
    ob_end_clean();
    echo json_encode(['error' => 'Usuario, cédula o correo no registrado']);
    if (isset($stmt)) $stmt->close();
    $con->close();
    exit;
}

$fila = $resultado->fetch_assoc();

// --- PASO 4: Validar si la cuenta requiere aprobación (Choferes y Funcionarios pendientes) ---
if (($rolEncontrado === 'funcionario' || $rolEncontrado === 'chofer') && isset($fila['estado']) && strtolower($fila['estado']) === 'pendiente') {
    ob_end_clean();
    echo json_encode(['error' => 'Su cuenta se encuentra pendiente de aprobación por la administración del hospital.']);
    $stmt->close();
    $con->close();
    exit;
}

// 5. Validar contraseña
$passGuardada = $fila['contrasenia'];
$passwordValida = false;

if (strpos($passGuardada, '$2y$') === 0 || strpos($passGuardada, '$2a$') === 0) {
    $passwordValida = password_verify($contrasenia, $passGuardada);
} else {
    $passwordValida = ($contrasenia === $passGuardada);
}

// 6. Enviar respuesta final en JSON
ob_end_clean();

if ($passwordValida) {
    // Guardar variables clave en la sesión
    $_SESSION['cedula'] = $fila['cedula'];
    $_SESSION['nombre'] = $fila['nombre'];
    $_SESSION['apellido'] = $fila['apellido'];
    $_SESSION['email'] = $fila['email'];
    $_SESSION['rol'] = $rolEncontrado; // Guarda 'paciente', 'chofer' o 'funcionario'

    // Usamos la ruta absoluta desde la raíz del servidor local para evitar el 404
    $redirectUrl = '/ProyectoFinal/proyectohospital/index.html'; 

    if ($rolEncontrado === 'funcionario') {
        $_SESSION['cargo'] = $fila['cargo'];
        $_SESSION['usuario_nombre'] = $fila['usuario'];

        switch ($fila['cargo']) {
            case 'Gerencia':
                $redirectUrl = '/ProyectoFinal/proyectohospital/pages/PanelAdmin.php';
                break;
            default:
                $redirectUrl = '/ProyectoFinal/proyectohospital/index.html';
                break;
        }
    }

    echo json_encode([
        'exito' => true,
        'redirect' => $redirectUrl,
        'usuario' => [
            'cedula' => $fila['cedula'],
            'nombre' => $fila['nombre'],
            'apellido' => $fila['apellido'],
            'email' => $fila['email'],
            'rol' => $rolEncontrado,
            'cargo' => isset($fila['cargo']) ? $fila['cargo'] : null
        ]
    ]);
} else {
    echo json_encode(['error' => 'Contraseña incorrecta']);
}

$stmt->close();
$con->close();
?>