<?php
require_once '../php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Traslados y Ambulancias</title>

    <!-- CSS de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="../css/TrasladosYAmbulancias.css">
</head>
<body>

    <a href="../index.html" class="box-inicio" title="Enlace a inicio">Regresar al inicio</a>

    <div class="contenedor-principal">

        <!-- COLUMNA IZQUIERDA: FORMULARIO DE REGISTRO DE TRASLADOS -->
        <div class="bloque-formulario">
            <h2>Registro de Traslado</h2>
            <p>Complete los datos solicitados para coordinar y registrar el traslado de la ambulancia.</p>

            <form class="TrasladosAmbulancias-form" id="formTraslado" action="../php/GuardarTraslado.php" method="POST">
                
                <!-- ORIGEN -->
                <div>
                    <label for="origen">Origen</label>
                    <div class="campo-con-boton">
                        <input class="TrasladosAmbulancias-input" type="text" id="origen" name="origen" maxlength="255" placeholder="Ingrese origen" required>
                        <button type="button" class="btn-mapa" id="ubicarOrigen">Buscar origen</button>
                        <!-- BOTÓN DE GPS INTEGRADO -->
                        <button type="button" class="btn-mapa" id="btnGpsOrigen" style="background-color: #28a745;" title="Usar mi ubicación GPS actual">Mi GPS</button>
                    </div>
                </div>

                <!-- DEPARTAMENTO DE ORIGEN -->
                <div>
                    <label for="departamento_origen">Departamento de origen</label>
                    <select class="TrasladosAmbulancias-select" id="departamento_origen" name="departamento" required>
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

                <!-- DESTINO -->
                <div>
                    <label for="destino">Destino</label>
                    <div class="campo-con-boton">
                        <input class="TrasladosAmbulancias-input" type="text" id="destino" name="destino" maxlength="255" placeholder="Ingrese destino" required>
                        <button type="button" class="btn-mapa" id="ubicarDestino">Buscar destino</button>
                    </div>
                </div>

                <!-- DEPARTAMENTO DE DESTINO -->
                <div>
                    <label for="departamento_destino">Departamento de destino</label>
                    <select class="TrasladosAmbulancias-select" id="departamento_destino" name="departamento_destino">
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
                    <input class="TrasladosAmbulancias-input" type="datetime-local" id="hora_salida" name="hora_salida" required>
                </div>

                <!-- HORA LLEGADA -->
                <div>
                    <label for="hora_llegada">Hora de llegada (opcional)</label>
                    <input class="TrasladosAmbulancias-input" type="datetime-local" id="hora_llegada" name="hora_llegada">
                </div>

                <!-- ACOMPAÑANTE -->
                <div>
                    <label for="acompaniante">Acompañante (opcional)</label>
                    <input class="TrasladosAmbulancias-input" type="text" id="acompaniante" name="acompaniante" maxlength="100" placeholder="Ingrese nombre completo">
                </div>

                <!-- ESTADO -->
                <div>
                    <label for="estado">Estado del traslado</label>
                    <select class="TrasladosAmbulancias-select" id="estado" name="estado" required>
                        <option value="Pendiente" selected>Pendiente</option>
                        <option value="En curso">En curso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                <!-- FUNCIONARIO (OPERADOR LOGÍSTICO) -->
                <div>
                    <label for="ci_funcionario">Funcionario a cargo</label>
                    <select class="TrasladosAmbulancias-select" id="ci_funcionario" name="ci_funcionario" required>
                        <option value="" disabled selected>Seleccione el operador</option>
                        <?php
                        $queryFunc = $con->query("SELECT ci_funcionario, nombre, apellido, cargo FROM funcionario WHERE cargo = 'Operador logístico'");
                        if ($queryFunc) {
                            while ($func = $queryFunc->fetch_assoc()) {
                                echo '<option value="'.$func['ci_funcionario'].'">'.$func['ci_funcionario'].' - '.$func['nombre'].' '.$func['apellido'].' ('.$func['cargo'].')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- AMBULANCIA (Filtrada por disponibilidad y estado) -->
                <div>
                    <label for="matricula">Ambulancia (matrícula)</label>
                    <select class="TrasladosAmbulancias-select" id="matricula" name="matricula" required>
                        <option value="" disabled selected>Seleccione la ambulancia</option>
                        <?php
                        $queryAmb = $con->query("SELECT matricula, estado, disponibilidad_ambulancia FROM ambulancia WHERE disponibilidad_ambulancia = 'Disponible' AND estado = 'Operativa'");
                        if ($queryAmb) {
                            while ($amb = $queryAmb->fetch_assoc()) {
                                echo '<option value="'.$amb['matricula'].'">'.$amb['matricula'].' - Estado: '.$amb['estado'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- CHOFER (Filtrado por disponibilidad) -->
                <div>
                    <label for="ci_chofer">Chofer asignado</label>
                    <select class="TrasladosAmbulancias-select" id="ci_chofer" name="ci_chofer" required>
                        <option value="" disabled selected>Seleccione el chofer</option>
                        <?php
                        $queryChofer = $con->query("SELECT ci_chofer, nombre, apellido, disponibilidad FROM chofer WHERE disponibilidad = 'Disponible'");
                        if ($queryChofer) {
                            while ($chofer = $queryChofer->fetch_assoc()) {
                                echo '<option value="'.$chofer['ci_chofer'].'">'.$chofer['ci_chofer'].' - '.$chofer['nombre'].' '.$chofer['apellido'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <input class="TrasladosAmbulancias-input TrasladosAmbulancias-submit" type="submit" value="Guardar traslado">
            </form>

            <!-- SECCIÓN EXTRA ADOSADA: REGISTRO DE AMBULANCIAS -->
            <h2 class="Registro-ambulancia">Registro de Ambulancias</h2>
            <p>Ingrese una nueva unidad vehicular a la flota del hospital.</p>

            <form class="TrasladosAmbulancias-form" id="formAmbulancia" action="../php/GuardarAmbulancia.php" method="POST">
                <div>
                    <label for="matricula_amb">Matrícula de la unidad</label>
                    <input class="TrasladosAmbulancias-input" type="text" id="matricula_amb" name="matricula" maxlength="20" placeholder="Ej: SAM 1234" required>
                </div>

                <div>
                    <label for="estado_amb">Estado técnico</label>
                    <select class="TrasladosAmbulancias-select" id="estado_amb" name="estado" required>
                        <option value="Operativa" selected>Operativa</option>
                        <option value="En mantenimiento">En mantenimiento</option>
                        <option value="Fuera de servicio">Fuera de servicio</option>
                    </select>
                </div>

                <div>
                    <label for="disponibilidad_ambulancia">Disponibilidad</label>
                    <select class="TrasladosAmbulancias-select" id="disponibilidad_ambulancia" name="disponibilidad_ambulancia" required>
                        <option value="Disponible" selected>Disponible</option>
                        <option value="En traslado">En traslado</option>
                        <option value="No disponible">No disponible</option>
                    </select>
                </div>

                <div>
                    <label for="ultimo_mantenimiento">Último mantenimiento (opcional)</label>
                    <input class="TrasladosAmbulancias-input" type="date" id="ultimo_mantenimiento" name="ultimo_mantenimiento">
                </div>

                <input class="TrasladosAmbulancias-input TrasladosAmbulancias-submit" type="submit" value="Guardar ambulancia">
            </form>
        </div>

        <!-- COLUMNA DERECHA: MAPA Y BOTÓN DE SIMULACIÓN -->
        <div class="bloque-mapa">
            <div id="visualizar"></div>
            <!-- Botón para simular recorrido (inicialmente deshabilitado) -->
            <div style="margin-top: 15px;">
                <button type="button" id="btnSimularRecorrido" class="TrasladosAmbulancias-input TrasladosAmbulancias-submit" style="background-color: #6c757d; cursor: not-allowed;" disabled>
                    🚑 Simular Recorrido de Ambulancia
                </button>
            </div>
        </div>

    </div>

    <!-- JS de Leaflet y scripts personalizados -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../JavaScript/TrasladosYAmbulancias.js"></script>
</body>
</html>