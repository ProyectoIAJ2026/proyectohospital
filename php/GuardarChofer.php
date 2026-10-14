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
    $ci_chofer     = trim($_POST['ci_chofer'] ?? '');
    $nombre        = trim($_POST['nombre'] ?? '');
    $apellido      = trim($_POST['apellido'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $contrasenia   = $_POST['contrasenia'] ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? 'Disponible';

    // Validar que no falte ningún campo obligatorio
    if (empty($ci_chofer) || empty($nombre) || empty($apellido) || empty($email) || empty($contrasenia)) {
        echo "Por favor complete todos los campos obligatorios.";
        exit();
    }

    // Encriptar la contraseña de forma segura (coincidente con el estándar del sistema)
    $passwordHash = password_hash($contrasenia, PASSWORD_DEFAULT);

    // 4. CONSULTA PREPARADA PARA INSERTAR EL CHOFER
    $sql = "INSERT INTO chofer (ci_chofer, nombre, apellido, email, contrasenia, disponibilidad) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $con->error;
        exit();
    }

    // Vincular los parámetros (6 cadenas de texto)
    $stmt->bind_param(
        "ssssss",
        $ci_chofer,
        $nombre,
        $apellido,
        $email,
        $passwordHash,
        $disponibilidad
    );

    // 5. EJECUTAR Y RESPONDER
    if ($stmt->execute()) {
        echo "ok"; // Respuesta para que tu JavaScript detecte el éxito
    } else {
        echo "Error al registrar el chofer: " . $stmt->error;
    }

    $stmt->close();
    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>