<?php
require_once '../php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.D.M.I.N. - Traslados</title>

    <!-- CSS de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="../css/Traslados.css">
</head>
<body>

    <a href="../index.html" class="box-inicio" title="Enlace a inicio">Regresar al inicio</a>

    <div class="contenedor-principal">

        <!-- COLUMNA IZQUIERDA: FORMULARIO DE REGISTRO -->
        <div class="bloque-formulario">
            <h2>Registro de Traslado</h2>
            <p>Complete los datos solicitados para coordinar y registrar el traslado de la ambulancia.</p>

            <!-- El action apunta correctamente al archivo procesador GuardarTraslado.php -->
            <form class="Traslados-form" id="formTraslado" action="../php/GuardarTraslado.php" method="POST">
                
                <!-- ORIGEN -->
                <div>
                    <label for="origen">Origen</label>
                    <div class="campo-con-boton">
                        <input class="Traslados-input" type="text" id="origen" name="origen" maxlength="255" placeholder="Ingrese origen" required>
                        <button type="button" class="btn-mapa" id="ubicarOrigen">Buscar origen</button>
                    </div>
                </div>

                <!-- DESTINO -->
                <div>
                    <label for="destino">Destino</label>
                    <div class="campo-con-boton">
                        <input class="Traslados-input" type="text" id="destino" name="destino" maxlength="255" placeholder="Ingrese destino" required>
                        <button type="button" class="btn-mapa" id="ubicarDestino">Buscar destino</button>
                    </div>
                </div>

                <!-- DEPARTAMENTO -->
                <div>
                    <label for="departamento">Departamento</label>
                    <select class="Traslados-select" id="departamento" name="departamento" required>
                        <option value="" disabled selected>Seleccione un departamento</option>
                        <option value="Artigas">Artigas</option>
                        <option value="Canelones">Canelones</option>
                        <option value="Cerro Largo">Cerro Largo</option>
                        <option value="Colonia">Colonia</option>
                        <option value="Durazno">Durazno</option>
                        <option value="Flores">Flores</option>
                        <option value="Florida">Florida</option>
                        <option value="Lavalleja">Lavalleja</option>
                        <option value="Maldonado">Maldonado</option>
                        <option value="Montevideo">Montevideo</option>
                        <option value="Paysandú" selected>Paysandú</option>
                        <option value="Río Negro">Río Negro</option>
                        <option value="Rivera">Rivera</option>
                        <option value="Rocha">Rocha</option>
                        <option value="Salto">Salto</option>
                        <option value="San José">San José</option>
                        <option value="Soriano">Soriano</option>
                        <option value="Tacuarembó">Tacuarembó</option>
                        <option value="Treinta y Tres">Treinta y Tres</option>
                    </select>
                </div>

                <!-- HORA SALIDA -->
                <div>
                    <label for="hora_salida">Hora de salida</label>
                    <input class="Traslados-input" type="datetime-local" id="hora_salida" name="hora_salida" required>
                </div>

                <!-- HORA LLEGADA -->
                <div>
                    <label for="hora_llegada">Hora de llegada (opcional)</label>
                    <input class="Traslados-input" type="datetime-local" id="hora_llegada" name="hora_llegada">
                </div>

                <!-- ACOMPAÑANTE -->
                <div>
                    <label for="acompaniante">Acompañante (opcional)</label>
                    <input class="Traslados-input" type="text" id="acompaniante" name="acompaniante" maxlength="100" placeholder="Ingrese nombre completo del acompañante">
                </div>

                <!-- ESTADO -->
                <div>
                    <label for="estado">Estado del traslado</label>
                    <select class="Traslados-select" id="estado" name="estado" required>
                        <option value="Pendiente" selected>Pendiente</option>
                        <option value="En curso">En curso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                <!-- FUNCIONARIO (DINÁMICO DESDE MYSQL) -->
                <div>
                    <label for="ci_funcionario">Funcionario a cargo</label>
                    <select class="Traslados-select" id="ci_funcionario" name="ci_funcionario" required>
                        <option value="" disabled selected>Seleccione el funcionario</option>
                        <?php
                        $queryFunc = $con->query("SELECT ci_funcionario, nombre, apellido, cargo FROM funcionario");
                        if ($queryFunc) {
                            while ($func = $queryFunc->fetch_assoc()) {
                                echo '<option value="'.$func['ci_funcionario'].'">'.$func['ci_funcionario'].' - '.$func['nombre'].' '.$func['apellido'].' ('.$func['cargo'].')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- AMBULANCIA (MATRÍCULA DINÁMICA DESDE MYSQL) -->
                <div>
                    <label for="matricula">Ambulancia (matrícula)</label>
                    <select class="Traslados-select" id="matricula" name="matricula" required>
                        <option value="" disabled selected>Seleccione la ambulancia</option>
                        <?php
                        $queryAmb = $con->query("SELECT matricula, estado FROM ambulancia");
                        if ($queryAmb) {
                            while ($amb = $queryAmb->fetch_assoc()) {
                                echo '<option value="'.$amb['matricula'].'">'.$amb['matricula'].' - Estado: '.$amb['estado'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- CHOFER (DINÁMICO DESDE MYSQL) -->
                <div>
                    <label for="ci_chofer">Chofer asignado</label>
                    <select class="Traslados-select" id="ci_chofer" name="ci_chofer" required>
                        <option value="" disabled selected>Seleccione el chofer</option>
                        <?php
                        $queryChofer = $con->query("SELECT ci_chofer, nombre, apellido, disponibilidad FROM chofer");
                        if ($queryChofer) {
                            while ($chofer = $queryChofer->fetch_assoc()) {
                                echo '<option value="'.$chofer['ci_chofer'].'">'.$chofer['ci_chofer'].' - '.$chofer['nombre'].' '.$chofer['apellido'].' (Estado: '.$chofer['disponibilidad'].')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <input class="Traslados-input Traslados-submit" type="submit" value="Guardar traslado">
            </form>
        </div>

        <!-- COLUMNA DERECHA: MAPA -->
        <div class="bloque-mapa">
            <div id="visualizar"></div>
        </div>

    </div>

    <!-- JS de Leaflet y script personalizado -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../JavaScript/Traslados.js"></script>
</body>
</html>