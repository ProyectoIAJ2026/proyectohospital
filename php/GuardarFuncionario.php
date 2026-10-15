<?php
// 1. CONEXIÓN A LA BASE DE DATOS Y CONFIGURACIÓN
require_once 'conexion.php';

// 2. VERIFICAR QUE LA SOLICITUD SEA POR POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método de solicitud no permitido.";
    exit();
}

try {
    // 3. CAPTURA Y LIMPIEZA DE LOS DATOS DEL FORMULARIO
    $ci_funcionario = trim($_POST['ci_funcionario'] ?? '');
    $nombre         = trim($_POST['nombre'] ?? '');
    $apellido       = trim($_POST['apellido'] ?? '');
    $usuario        = trim($_POST['usuario'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contrasenia    = $_POST['contrasenia'] ?? '';
    $cargo          = $_POST['cargo'] ?? '';

    // Validar que no falte ningún campo obligatorio
    if (empty($ci_funcionario) || empty($nombre) || empty($apellido) || empty($usuario) || empty($email) || empty($contrasenia) || empty($cargo)) {
        echo "Por favor complete todos los campos obligatorios.";
        exit();
    }

    // Encriptar la contraseña de forma segura
    $passwordHash = password_hash($contrasenia, PASSWORD_DEFAULT);

    // 4. CONSULTA PREPARADA PARA INSERTAR EL FUNCIONARIO
    $sql = "INSERT INTO funcionario (ci_funcionario, nombre, apellido, usuario, email, contrasenia, cargo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $con->error;
        exit();
    }

    // Vincular los parámetros (7 cadenas de texto)
    $stmt->bind_param(
        "sssssss",
        $ci_funcionario,
        $nombre,
        $apellido,
        $usuario,
        $email,
        $passwordHash,
        $cargo
    );

    // 5. EJECUTAR Y RESPONDER
    if ($stmt->execute()) {
        echo "ok"; // Respuesta que lee tu JavaScript para mostrar la alerta de éxito
    } else {
        echo "Error al registrar el funcionario: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>