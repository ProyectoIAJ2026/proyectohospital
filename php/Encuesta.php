<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Validar que la petición sea por método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Método de solicitud no permitido.";
    exit();
}

try {
    // Recoger y limpiar los datos enviados desde el formulario JS (Fetch)
    $id_encuesta = intval($_POST['id_encuesta'] ?? 1);
    $email       = trim($_POST['email'] ?? '');
    $atencion    = trim($_POST['atencion'] ?? '');
    $tiempo      = trim($_POST['tiempo'] ?? '');
    $acceso      = trim($_POST['acceso'] ?? '');
    $comentarios = trim($_POST['comentarios'] ?? '');

    // Validar campos obligatorios
    if (empty($atencion) || empty($tiempo) || empty($acceso) || empty($comentarios)) {
        echo "Por favor complete todos los campos obligatorios de la encuesta.";
        exit();
    }

    // =========================================================================
    // NUEVO: Auto-verificación de la encuesta base para evitar errores de FK
    // =========================================================================
    $check_enc = $con->prepare("SELECT id_encuesta FROM encuesta WHERE id_encuesta = ?");
    if ($check_enc) {
        $check_enc->bind_param("i", $id_encuesta);
        $check_enc->execute();
        $res_enc = $check_enc->get_result();
        
        if ($res_enc->num_rows === 0) {
            // Si la tabla se vació, creamos la encuesta base automáticamente
            $insert_base = $con->prepare("INSERT INTO encuesta (id_encuesta, titulo, fecha_creacion, activa, descripcion) VALUES (?, 'Encuesta de Satisfacción del Paciente', CURDATE(), 1, 'Encuesta general de calidad de atención')");
            if ($insert_base) {
                $insert_base->bind_param("i", $id_encuesta);
                $insert_base->execute();
                $insert_base->close();
            }
        }
        $check_enc->close();
    }

    $cedula_paciente = null;

    // 1. Buscar la cédula del paciente en la tabla 'paciente' usando el correo electrónico
    if (!empty($email)) {
        $sql_paciente = "SELECT cedula FROM paciente WHERE email = ? LIMIT 1";
        $stmt_p = $con->prepare($sql_paciente);
        if ($stmt_p) {
            $stmt_p->bind_param("s", $email);
            $stmt_p->execute();
            $resultado = $stmt_p->get_result();
            if ($row = $resultado->fetch_assoc()) {
                $cedula_paciente = $row['cedula'];
            }
            $stmt_p->close();
        }
    }

    // 2. Si se encontró el paciente, registrar la relación en la tabla puente 'paciente_encuesta'
    if (!empty($cedula_paciente)) {
        $sql_insert_pe = "INSERT INTO paciente_encuesta (cedula, id_encuesta) VALUES (?, ?)";
        $stmt_pe = $con->prepare($sql_insert_pe);
        if ($stmt_pe) {
            $stmt_pe->bind_param("si", $cedula_paciente, $id_encuesta);
            try {
                $stmt_pe->execute();
            } catch (mysqli_sql_exception $e) {
                // Si el paciente ya había respondido esta encuesta antes, se ignora el error de clave duplicada
            }
            $stmt_pe->close();
        }
    }

    // 3. Guardar las respuestas detalladas en la tabla 'respuesta_encuesta' incluyendo la fecha actual
    $sql_resp = "INSERT INTO respuesta_encuesta (atencion, tiempo, acceso, comentarios, fecha_respuesta, cedula, id_encuesta) VALUES (?, ?, ?, ?, CURDATE(), ?, ?)";
    $stmt_resp = $con->prepare($sql_resp);

    if ($stmt_resp) {
        $stmt_resp->bind_param("sssssi", $atencion, $tiempo, $acceso, $comentarios, $cedula_paciente, $id_encuesta);
        $stmt_resp->execute();
        $stmt_resp->close();
    }

    // Responder con "ok" para que el archivo JS detecte el éxito y muestre la alerta
    echo "ok";
    $con->close();

} catch (Exception $e) {
    echo "Error al procesar la encuesta: " . $e->getMessage();
}
?>