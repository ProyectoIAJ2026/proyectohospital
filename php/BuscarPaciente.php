<?php
session_start();
require_once 'ControlAcceso.php';
require_once 'conexion.php';

// Validar que únicamente el personal Administrativo pueda realizar esta consulta
restringirAcceso('Administrativo', 'Gerencia');

// Verificamos si se recibió la cédula por POST
if (isset($_POST['cedula'])) {
    $cedula = trim($_POST['cedula']);

    // Consulta preparada en la tabla 'paciente'
    $sql = "SELECT * FROM paciente WHERE cedula = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($row = $resultado->fetch_assoc()) {
        // Mostramos la información utilizando la clase con animación
        echo "<div class='resultado-tarjeta'>";
        echo "<h4>Paciente Encontrado</h4>";
        echo "<p><strong>Cédula:</strong> " . htmlspecialchars($row['cedula']) . "</p>";
        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($row['nombre']) . "</p>";
        echo "<p><strong>Apellido:</strong> " . htmlspecialchars($row['apellido']) . "</p>";
        echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($row['telefono']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red; margin-top: 15px; font-weight: bold; text-align: center;'>No se encontró ningún paciente con la cédula ingresada.</p>";
    }

    $stmt->close();
    $con->close();
}
?>