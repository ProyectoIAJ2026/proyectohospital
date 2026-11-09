<?php
// Evitar que errores de PHP o espacios en blanco rompan el formato JSON
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['activo' => false, 'cargo' => '', 'rol' => ''];

if (isset($_SESSION['cedula'])) {
    require_once 'conexion.php';
    
    $cedula = $_SESSION['cedula'];
    
    // 1. Buscar en la tabla 'funcionario'
    $stmt = $con->prepare("SELECT estado, cargo FROM funcionario WHERE ci_funcionario = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        if (strtolower($row['estado']) === 'activo') {
            $response['activo'] = true;
            $response['cargo'] = isset($row['cargo']) ? $row['cargo'] : '';
            $response['rol'] = 'funcionario';
        } else {
            session_unset();
            session_destroy();
        }
    } else {
        // 2. Buscar en la tabla 'chofer'
        $stmtChofer = $con->prepare("SELECT estado FROM chofer WHERE ci_chofer = ?");
        $stmtChofer->bind_param("s", $cedula);
        $stmtChofer->execute();
        $resChofer = $stmtChofer->get_result();
        
        if ($rowChofer = $resChofer->fetch_assoc()) {
            if (strtolower($rowChofer['estado']) === 'activo') {
                $response['activo'] = true;
                $response['cargo'] = 'Chofer';
                $response['rol'] = 'chofer';
            } else {
                session_unset();
                session_destroy();
            }
        } else {
            // 3. Buscar en la tabla 'paciente'
            $stmtPaciente = $con->prepare("SELECT cedula FROM paciente WHERE cedula = ?");
            $stmtPaciente->bind_param("s", $cedula);
            $stmtPaciente->execute();
            $resPaciente = $stmtPaciente->get_result();
            
            if ($rowPaciente = $resPaciente->fetch_assoc()) {
                $response['activo'] = true;
                $response['cargo'] = 'Paciente';
                $response['rol'] = 'paciente';
            } else {
                session_unset();
                session_destroy();
            }
        }
    }
}

ob_end_clean();
echo json_encode($response);
exit;
?>