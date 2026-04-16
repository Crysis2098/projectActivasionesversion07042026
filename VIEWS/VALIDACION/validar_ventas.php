<?php
if (!isset($ventas_validacion)) {
    $ventas_validacion = [
        'pendientes' => [],
        'aprobadas' => [],
        'rechazadas' => []
    ];
}

if (!isset($id_empleado)) {
    $id_empleado = 0;
}
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Validación de ventas</span>
</div>

<div id="contenido_formulario">
    <section class="form-card">
        <div class="form-card-header">
            <h2>Ventas del día por estatus</h2>
            <p>Las validaciones corresponden únicamente a ventas capturadas el día de hoy.</p>
        </div>

        <div class="validacion-bloques">

            <div class="validacion-seccion">
                <div class="validacion-titulo">Pendientes</div>
                <div class="tabla-responsive">
                    <table class="tabla-consulta-campanas tabla-validacion">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Orden Servicio</th>
                                <th>Valor</th>
                                <th>Fecha captura</th>
                                <th>Ejecutivo</th>
                                <th>Supervisor</th>
                                <th>Comentarios ADMON</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas_validacion['pendientes'])): ?>
                                <?php foreach ($ventas_validacion['pendientes'] as $venta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($venta['cuenta']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['orden_servicio']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$venta['rgus']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['fecha_captura'] . ' ' . $venta['hora_captura']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_supervisor']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['observaciones']); ?></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn_secundario btn-validar-venta"
                                                data-id-venta="<?php echo (int)$venta['id']; ?>"
                                                data-id-asignado="<?php echo (int)$venta['id_asignado_a']; ?>"
                                                data-cuenta="<?php echo htmlspecialchars($venta['cuenta']); ?>"
                                                data-orden="<?php echo htmlspecialchars($venta['orden_servicio']); ?>"
                                                data-ejecutivo="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>"
                                                data-hora="<?php echo htmlspecialchars($venta['hora_captura']); ?>"
                                                data-indicador="<?php echo htmlspecialchars($venta['texto_indicador']); ?>"
                                                data-valor="<?php echo htmlspecialchars((string)$venta['rgus']); ?>"
                                                data-comentarios="<?php echo htmlspecialchars($venta['comentarios_validador']); ?>"
                                                data-estatus="<?php echo (int)$venta['estatus']; ?>"
                                            >
                                                Validar esta venta
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="fila-formulario-validacion" id="fila-validacion-<?php echo (int)$venta['id']; ?>" style="display:none;">
                                        <td colspan="8">
                                            <div class="validacion-form-box">
                                                <div class="validacion-form-header">
                                                    Validar venta, este es el indicador: <?php echo htmlspecialchars($venta['texto_indicador']); ?>
                                                </div>

                                                <form class="form-validacion-venta" data-id-venta="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_venta" value="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_asignado_a" value="<?php echo (int)$venta['id_asignado_a']; ?>">
                                                    <input type="hidden" name="id_validador_actual" value="<?php echo (int)$id_empleado; ?>">

                                                    <div class="form-grid validacion-form-grid">
                                                        <div class="form-field">
                                                            <label>Cuenta</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['cuenta']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Orden Servicio</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['orden_servicio']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Ejecutivo</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Horario</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['hora_captura']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label for="estatus_<?php echo (int)$venta['id']; ?>">Estado</label>
                                                            <select name="estatus" id="estatus_<?php echo (int)$venta['id']; ?>" class="caja-select" required>
                                                                <option value="" selected disabled>Seleccionar estado</option>
                                                                <option value="1">Aprobado</option>
                                                                <option value="2">Rechazado</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-field">
                                                            <label><?php echo htmlspecialchars($venta['texto_indicador']); ?></label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars((string)$venta['rgus']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field form-field-full">
                                                            <label for="comentarios_validador_<?php echo (int)$venta['id']; ?>">Comentarios</label>
                                                            <textarea
                                                                name="comentarios_validador"
                                                                id="comentarios_validador_<?php echo (int)$venta['id']; ?>"
                                                                class="caja-inputs"
                                                                rows="3"
                                                                placeholder="Comentarios del validador"
                                                            ><?php echo htmlspecialchars($venta['comentarios_validador']); ?></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="validacion-acciones">
                                                        <button type="submit" class="btn_principal">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="tabla-vacia-celda">No hay ventas pendientes.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="validacion-seccion">
                <div class="validacion-titulo">Aprobadas</div>
                <div class="tabla-responsive">
                    <table class="tabla-consulta-campanas tabla-validacion">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Orden Servicio</th>
                                <th>Valor</th>
                                <th>Fecha captura</th>
                                <th>Ejecutivo</th>
                                <th>Supervisor</th>
                                <th>Comentarios</th>
                                <th>Validado por</th>
                                <th>Fecha validación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas_validacion['aprobadas'])): ?>
                                <?php foreach ($ventas_validacion['aprobadas'] as $venta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($venta['cuenta']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['orden_servicio']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$venta['rgus']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['fecha_captura'] . ' ' . $venta['hora_captura']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_supervisor']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['comentarios_validador']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_validado_por']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['fecha_validacion_termina'] ?: $venta['fecha_validacion']); ?></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn_secundario btn-validar-venta"
                                                data-id-venta="<?php echo (int)$venta['id']; ?>"
                                                data-id-asignado="<?php echo (int)$venta['id_asignado_a']; ?>"
                                                data-cuenta="<?php echo htmlspecialchars($venta['cuenta']); ?>"
                                                data-orden="<?php echo htmlspecialchars($venta['orden_servicio']); ?>"
                                                data-ejecutivo="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>"
                                                data-hora="<?php echo htmlspecialchars($venta['hora_captura']); ?>"
                                                data-indicador="<?php echo htmlspecialchars($venta['texto_indicador']); ?>"
                                                data-valor="<?php echo htmlspecialchars((string)$venta['rgus']); ?>"
                                                data-comentarios="<?php echo htmlspecialchars($venta['comentarios_validador']); ?>"
                                                data-estatus="<?php echo (int)$venta['estatus']; ?>"
                                            >
                                                Validar esta venta
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="fila-formulario-validacion" id="fila-validacion-<?php echo (int)$venta['id']; ?>" style="display:none;">
                                        <td colspan="10">
                                            <div class="validacion-form-box">
                                                <div class="validacion-form-header">
                                                    Validar venta, este es el indicador: <?php echo htmlspecialchars($venta['texto_indicador']); ?>
                                                </div>

                                                <form class="form-validacion-venta" data-id-venta="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_venta" value="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_asignado_a" value="<?php echo (int)$venta['id_asignado_a']; ?>">
                                                    <input type="hidden" name="id_validador_actual" value="<?php echo (int)$id_empleado; ?>">

                                                    <div class="form-grid validacion-form-grid">
                                                        <div class="form-field">
                                                            <label>Cuenta</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['cuenta']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Orden Servicio</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['orden_servicio']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Ejecutivo</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Horario</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['hora_captura']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label for="estatus_<?php echo (int)$venta['id']; ?>">Estado</label>
                                                            <select name="estatus" id="estatus_<?php echo (int)$venta['id']; ?>" class="caja-select" required>
                                                                <option value="1" <?php echo ((int)$venta['estatus'] === 1) ? 'selected' : ''; ?>>Aprobado</option>
                                                                <option value="2" <?php echo ((int)$venta['estatus'] === 2) ? 'selected' : ''; ?>>Rechazado</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-field">
                                                            <label><?php echo htmlspecialchars($venta['texto_indicador']); ?></label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars((string)$venta['rgus']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field form-field-full">
                                                            <label for="comentarios_validador_<?php echo (int)$venta['id']; ?>">Comentarios</label>
                                                            <textarea
                                                                name="comentarios_validador"
                                                                id="comentarios_validador_<?php echo (int)$venta['id']; ?>"
                                                                class="caja-inputs"
                                                                rows="3"
                                                                placeholder="Comentarios del validador"
                                                            ><?php echo htmlspecialchars($venta['comentarios_validador']); ?></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="validacion-acciones">
                                                        <button type="submit" class="btn_principal">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="tabla-vacia-celda">No hay ventas aprobadas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="validacion-seccion">
                <div class="validacion-titulo">Rechazadas</div>
                <div class="tabla-responsive">
                    <table class="tabla-consulta-campanas tabla-validacion">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Orden Servicio</th>
                                <th>Valor</th>
                                <th>Fecha captura</th>
                                <th>Ejecutivo</th>
                                <th>Supervisor</th>
                                <th>Comentarios</th>
                                <th>Validado por</th>
                                <th>Fecha validación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ventas_validacion['rechazadas'])): ?>
                                <?php foreach ($ventas_validacion['rechazadas'] as $venta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($venta['cuenta']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['orden_servicio']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$venta['rgus']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['fecha_captura'] . ' ' . $venta['hora_captura']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_supervisor']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['comentarios_validador']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nombre_validado_por']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['fecha_validacion_termina'] ?: $venta['fecha_validacion']); ?></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn_secundario btn-validar-venta"
                                                data-id-venta="<?php echo (int)$venta['id']; ?>"
                                                data-id-asignado="<?php echo (int)$venta['id_asignado_a']; ?>"
                                                data-cuenta="<?php echo htmlspecialchars($venta['cuenta']); ?>"
                                                data-orden="<?php echo htmlspecialchars($venta['orden_servicio']); ?>"
                                                data-ejecutivo="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>"
                                                data-hora="<?php echo htmlspecialchars($venta['hora_captura']); ?>"
                                                data-indicador="<?php echo htmlspecialchars($venta['texto_indicador']); ?>"
                                                data-valor="<?php echo htmlspecialchars((string)$venta['rgus']); ?>"
                                                data-comentarios="<?php echo htmlspecialchars($venta['comentarios_validador']); ?>"
                                                data-estatus="<?php echo (int)$venta['estatus']; ?>"
                                            >
                                                Validar esta venta
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="fila-formulario-validacion" id="fila-validacion-<?php echo (int)$venta['id']; ?>" style="display:none;">
                                        <td colspan="10">
                                            <div class="validacion-form-box">
                                                <div class="validacion-form-header">
                                                    Validar venta, este es el indicador: <?php echo htmlspecialchars($venta['texto_indicador']); ?>
                                                </div>

                                                <form class="form-validacion-venta" data-id-venta="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_venta" value="<?php echo (int)$venta['id']; ?>">
                                                    <input type="hidden" name="id_asignado_a" value="<?php echo (int)$venta['id_asignado_a']; ?>">
                                                    <input type="hidden" name="id_validador_actual" value="<?php echo (int)$id_empleado; ?>">

                                                    <div class="form-grid validacion-form-grid">
                                                        <div class="form-field">
                                                            <label>Cuenta</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['cuenta']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Orden Servicio</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['orden_servicio']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Ejecutivo</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['nombre_ejecutivo']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label>Horario</label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($venta['hora_captura']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field">
                                                            <label for="estatus_<?php echo (int)$venta['id']; ?>">Estado</label>
                                                            <select name="estatus" id="estatus_<?php echo (int)$venta['id']; ?>" class="caja-select" required>
                                                                <option value="1" <?php echo ((int)$venta['estatus'] === 1) ? 'selected' : ''; ?>>Aprobado</option>
                                                                <option value="2" <?php echo ((int)$venta['estatus'] === 2) ? 'selected' : ''; ?>>Rechazado</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-field">
                                                            <label><?php echo htmlspecialchars($venta['texto_indicador']); ?></label>
                                                            <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars((string)$venta['rgus']); ?>" disabled>
                                                        </div>

                                                        <div class="form-field form-field-full">
                                                            <label for="comentarios_validador_<?php echo (int)$venta['id']; ?>">Comentarios</label>
                                                            <textarea
                                                                name="comentarios_validador"
                                                                id="comentarios_validador_<?php echo (int)$venta['id']; ?>"
                                                                class="caja-inputs"
                                                                rows="3"
                                                                placeholder="Comentarios del validador"
                                                            ><?php echo htmlspecialchars($venta['comentarios_validador']); ?></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="validacion-acciones">
                                                        <button type="submit" class="btn_principal">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="tabla-vacia-celda">No hay ventas rechazadas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<div id="toast_container" class="toast-container"></div>

<script src="ACTIVACIONES_SL/JS/validacion.js"></script>