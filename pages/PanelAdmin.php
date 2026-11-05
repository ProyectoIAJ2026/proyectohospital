<?php
session_start();
require_once '../php/conexion.php';
require_once '../php/ControlAcceso.php'; // Esto ya valida sesión y estado activo automáticamente

// Obtener funcionarios pendientes (con 'Pendiente' en mayúscula)
$queryFunc = "SELECT ci_funcionario AS cedula, nombre, apellido, email, cargo, 'funcionario' AS tabla FROM funcionario WHERE estado = 'Pendiente'";
$resFunc = $con->query($queryFunc);

// Obtener choferes pendientes (con 'Pendiente' en mayúscula)
$queryChofer = "SELECT ci_chofer AS cedula, nombre, apellido, email, 'Chofer' AS cargo, 'chofer' AS tabla FROM chofer WHERE estado = 'Pendiente'";
$resChofer = $con->query($queryChofer);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Panel de Administración</title>
    <link rel="stylesheet" href="../css/IniciarSesion.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #fcfcfc; padding: 20px; }
        h2 { 
            color: rgb(4, 29, 255); 
            background-color: rgb(189, 217, 250); 
            text-align: center; 
            padding: 15px 30px; 
            border-radius: 10px; 
            width: fit-content; 
            margin: 0 auto 20px auto; 
        }
        .tabla-admin { width: 90%; margin: 0 auto; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .tabla-admin th, .tabla-admin td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .tabla-admin th { background-color: rgb(0, 97, 177); color: white; }
        .btn-aprobar { background-color: #28a745; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; }
        .btn-aprobar:hover { background-color: #218838; }
        .no-pendientes { text-align: center; color: #666; margin-top: 20px; font-size: 16px; }
        .menu-superior { 
            text-align: left; 
            width: 90%; 
            margin: 0 auto 20px auto; 
        }
    </style>
</head>
<body>

    <div class="menu-superior">
        <a href="../index.html">Regresar al inicio</a>
    </div>

    <h2>Panel de Aprobación de Personal</h2>

    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre y Apellido</th>
                <th>Email</th>
                <th>Cargo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $hayPendientes = false;

            // Mostrar funcionarios pendientes
            if ($resFunc && $resFunc->num_rows > 0) {
                while ($row = $resFunc->fetch_assoc()) {
                    $hayPendientes = true;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['cedula']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre'] . " " . $row['apellido']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
                    echo "<td><a class='btn-aprobar' href='aprobar.php?tabla=funcionario&ci=" . urlencode($row['cedula']) . "'>Aprobar</a></td>";
                    echo "</tr>";
                }
            }

            // Mostrar choferes pendientes
            if ($resChofer && $resChofer->num_rows > 0) {
                while ($row = $resChofer->fetch_assoc()) {
                    $hayPendientes = true;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['cedula']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre'] . " " . $row['apellido']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
                    echo "<td><a class='btn-aprobar' href='aprobar.php?tabla=chofer&ci=" . urlencode($row['cedula']) . "'>Aprobar</a></td>";
                    echo "</tr>";
                }
            }

            if (!$hayPendientes) {
                echo "<tr><td colspan='5' class='no-pendientes'>No hay cuentas pendientes de aprobación en este momento.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>