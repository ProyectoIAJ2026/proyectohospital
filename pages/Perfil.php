<?php
session_start();

// Si no hay una sesión activa, redirigimos al inicio de sesión
if (!isset($_SESSION['cedula']) || empty($_SESSION['cedula'])) {
    header("Location: IniciarSesion.html");
    exit();
}

// Recuperamos los datos directamente desde las variables de sesión creadas en IniciarSesion.php
$cedula = isset($_SESSION['cedula']) ? $_SESSION['cedula'] : '';
$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
$apellido = isset($_SESSION['apellido']) ? $_SESSION['apellido'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$rol = isset($_SESSION['rol']) ? ucfirst($_SESSION['rol']) : '';
$cargo = isset($_SESSION['cargo']) ? $_SESSION['cargo'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - S.I.G.S.M.</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="contenedor-titulos">
        <h1>Perfil de Usuario</h1>
        <p class="subtitulo-sistema">S.I.G.S.M. - Hospital de Clínicas Dr. Manuel Quintela</p>
    </div>

    <div style="max-width: 600px; margin: 30px auto; padding: 25px; background: #f4f4f4; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2>Información de la Cuenta</h2>
        <p><strong>Cédula:</strong> <?php echo htmlspecialchars($cedula); ?></p>
        <p><strong>Nombre completo:</strong> <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Rol en el sistema:</strong> <?php echo htmlspecialchars($rol); ?></p>

        <?php if (!empty($cargo)): ?>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($cargo); ?></p>
        <?php endif; ?>

        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <a href="../index.html" class="box" style="display: inline-block; text-decoration: none; padding: 10px 20px; background: #004080; color: white; border-radius: 4px;">Volver al Inicio</a>
            <a href="../php/CerrarSesion.php" class="box" style="display: inline-block; text-decoration: none; padding: 10px 20px; background: #d9534f; color: white; border-radius: 4px;">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>