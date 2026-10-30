<?php
// Incluimos la conexión subiendo un nivel desde la carpeta 'pages/' hacia 'php/'
require_once '../php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M - Gestión de Documentos Médicos</title>
    <link rel="stylesheet" href="../css/Documentos.css">
</head>
<body>

    <a href="../index.html" class="btn-regresar" title="Enlace a inicio">Regresar al inicio</a>

    <!-- Contenedor Principal en Dos Columnas -->
    <div class="contenedor-principal">

        <!-- COLUMNA IZQUIERDA: Formulario de Carga -->
        <div class="columna-izquierda">
            <div class="box">
                <h2 style="text-align: left; margin-top: 0; margin-bottom: 20px; color: #0056b3; font-size: 22px;">Gestión de Documentos Médicos</h2>
                
                <form id="formularioCarga" action="../php/CargarDocumento.php" method="POST" enctype="multipart/form-data">

                    <label for="nombre_documento">Nombre del documento (título)</label>
                    <input type="text" class="box-input" id="nombre_documento" name="nombre_documento" placeholder="Nombre de documento" required>

                    <label for="archivo">Subir documento (archivo)</label>
                    <input type="file" class="box-input" id="archivo" name="archivo" required>

                    <label for="descripcion">Descripción (opcional)</label>
                    <textarea class="box-input" id="descripcion" name="descripcion" placeholder="Descripción del documento"></textarea>

                    <label for="estado">Estado (opcional)</label>
                    <select class="box-input" id="estado" name="estado">
                        <option value="">Seleccione el estado</option>
                        <option value="Activo">Activo</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Archivado">Archivado</option>
                    </select>

                    <label for="fecha_carga">Ingresar fecha de carga</label>
                    <input type="date" class="box-input" id="fecha_carga" name="fecha_carga" required>

                    <label for="ci_paciente">Cédula de identidad (paciente)</label>
                    <select class="box-input" id="ci_paciente" name="ci_paciente" required>
                        <option value="">Seleccione un paciente</option>
                        <?php
                        $query_pacientes = "SELECT cedula, nombre, apellido FROM paciente";
                        $res_pacientes = mysqli_query($con, $query_pacientes);
                        if ($res_pacientes) {
                            while ($p = mysqli_fetch_assoc($res_pacientes)) {
                                echo "<option value='" . $p['cedula'] . "'>C.I: " . $p['cedula'] . " - " . $p['nombre'] . " " . $p['apellido'] . "</option>";
                            }
                        }
                        ?>
                    </select>

                    <label for="ci_funcionario">Cédula de identidad (funcionario)</label>
                    <select class="box-input" id="ci_funcionario" name="ci_funcionario" required>
                        <option value="">Seleccione un funcionario</option>
                        <?php
                        $query_func = "SELECT ci_funcionario, nombre, apellido FROM funcionario WHERE cargo = 'Administrativo'";
                        $resultado_func = mysqli_query($con, $query_func);

                        if ($resultado_func) {
                            while ($row = mysqli_fetch_assoc($resultado_func)) {
                                echo "<option value='" . $row['ci_funcionario'] . "'>C.I: " . $row['ci_funcionario'] . " - " . $row['nombre'] . " " . $row['apellido'] . " - Administrativo</option>";
                            }
                        }
                        ?>
                    </select>

                    <button type="submit" class="box-submit">Cargar documento</button>
                </form>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Obtener, QR y Vista Previa -->
        <div class="columna-derecha">
            
            <div class="box" id="obtener">
                <button id="boton" class="box-submit">Obtener documentos</button>
                <select name="listaDoc" id="listaDoc" class="box-input" style="margin-top: 15px; margin-bottom: 0;">
                    <option value="">Seleccione un documento</option>
                </select>
            </div>

            <div class="box" id="contenedorQR">
                <p><strong>Código QR del documento:</strong></p>
                <img id="imagenQR" src="" alt="Código QR del documento">
            </div>

            <div class="box" style="padding: 15px;">
                <p style="margin: 0 0 10px 0; font-weight: bold; color: #333;">Vista previa:</p>
                <!-- Vista previa -->
                <iframe id="previsualizacion"></iframe>
            </div>

        </div>

    </div>
    
    <div id="mensaje"></div>
    
    <script src="../JavaScript/Documentos.js"></script>
</body>
</html>