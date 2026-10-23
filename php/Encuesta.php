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
    $id_encuesta = intval($_POST['id_encuesta'] ?? 1);
    $email       = trim($_POST['email'] ?? '');
    $atencion    = trim($_POST['atencion'] ?? '');
    $tiempo      = trim($_POST['tiempo'] ?? '');
    $acceso      = trim($_POST['acceso'] ?? '');
    $comentarios = trim($_POST['comentarios'] ?? '');

    // Validar que los campos obligatorios no estén vacíos
    if (empty($atencion) || empty($tiempo) || empty($acceso) || empty($comentarios)) {
        echo "Por favor complete todos los campos obligatorios de la encuesta.";
        exit();
    }

    $cedula_paciente = null;

    // 4. Si el usuario ingresó un email, buscamos si existe en la tabla 'paciente' para obtener su cédula
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

    // 5. Si encontramos la cédula del paciente, registramos la participación en 'paciente_encuesta'
    if (!empty($cedula_paciente)) {
        $sql_insert = "INSERT INTO paciente_encuesta (cedula, id_encuesta) VALUES (?, ?)";
        $stmt_ins = $con->prepare($sql_insert);

        if ($stmt_ins) {
            $stmt_ins->bind_param("si", $cedula_paciente, $id_encuesta);
            
            try {
                $stmt_ins->execute();
            } catch (mysqli_sql_exception $e) {
                // El paciente ya respondió antes o hubo un duplicado controlado
            }
            $stmt_ins->close();
        }
    }

    // 6. RESPUESTA EXITOSA PARA EL FETCH (Imprime exactamente 'ok')
    echo "ok";

    $con->close();

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage();
}
?>