<?php
if (!isset($programadores)) {
    $programadores = [];
}

if (!isset($indicadores)) {
    $indicadores = [];
}

if (!isset($callcenter)) {
    $callcenter = '';
}

if (!isset($id_empleado)) {
    $id_empleado = '';
}

if (!isset($callcenters_disponibles)) {
    $callcenters_disponibles = [];
}

$es_admin_global = (isset($tipo_usuario) && $tipo_usuario === 'administrador_global');

/*
    Obtener nombre del callcenter actual sin hardcodearlo
*/
$nombre_callcenter_actual = (string)$callcenter;

foreach ($callcenters_disponibles as $cc) {
    if ((int)$cc['id_callcenter'] === (int)$callcenter) {
        $nombre_callcenter_actual = $cc['callcenter'];
        break;
    }
}
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Crear Campaña</span>
</div>

<div id="contenido_formulario">
    <form id="form_crear_campana" enctype="multipart/form-data" novalidate>
        
        <?php if ($es_admin_global): ?>
            <input type="hidden" name="callcenter" id="callcenter_hidden" value="">
        <?php else: ?>
            <input type="hidden" name="callcenter" id="callcenter" value="<?php echo htmlspecialchars((string)$callcenter); ?>">
        <?php endif; ?>

        <input type="hidden" name="creado_por" id="creado_por" value="<?php echo htmlspecialchars((string)$id_empleado); ?>">

        <section class="form-card">
            <div class="form-card-header">
                <h2>Datos generales</h2>
                <p>Configura la campaña y define su vigencia.</p>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="incentivo_para">Incentivo para</label>
                    <select name="incentivo_para" id="incentivo_para" class="caja-select" required>
                        <option value="" selected disabled>Seleccionar</option>
                        <option value="1">Ejecutivo</option>
                        <option value="2">Supervisor</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="id_indicador">Indicador</label>
                    <select name="id_indicador" id="id_indicador" class="caja-select" required>
                        <option value="" selected disabled>Seleccionar indicador</option>
                        <?php foreach ($indicadores as $indicador): ?>
                            <option value="<?php echo (int)$indicador['id']; ?>">
                                <?php echo htmlspecialchars($indicador['indicadores']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="id_programador">Programador</label>
                    <select name="id_programador" id="id_programador" class="caja-select" required>
                        <option value="" selected disabled>Seleccionar programador</option>
                        <?php foreach ($programadores as $programador): ?>
                            <option value="<?php echo (int)$programador['id']; ?>">
                                <?php echo htmlspecialchars($programador['programadores']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="callcenter_select">Callcenter</label>

                    <?php if ($es_admin_global): ?>
                        <select name="callcenter_select" id="callcenter_select" class="caja-select" required>
                            <option value="" selected disabled>Seleccionar callcenter</option>
                            <?php foreach ($callcenters_disponibles as $cc): ?>
                                <option value="<?php echo (int)$cc['id_callcenter']; ?>">
                                    <?php echo htmlspecialchars($cc['callcenter']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input
                            type="text"
                            class="caja-inputs"
                            value="<?php echo htmlspecialchars($nombre_callcenter_actual); ?>"
                            disabled
                        >
                    <?php endif; ?>
                </div>

                <div class="form-field">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="caja-inputs" required>
                </div>

                <div class="form-field">
                    <label for="hora_inicio">Horario de</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" class="caja-inputs" required>
                </div>

                <div class="form-field">
                    <label for="hora_fin">A</label>
                    <input type="time" name="hora_fin" id="hora_fin" class="caja-inputs" required>
                </div>

                <div class="form-field form-field-full">
                    <label for="observaciones">Observaciones</label>
                    <textarea
                        name="observaciones"
                        id="observaciones"
                        class="caja-inputs"
                        rows="4"
                        placeholder="Observaciones de la campaña (opcional)"
                    ></textarea>
                </div>
            </div>
        </section>

        <section id="bloque_incentivos">
            <div class="cabecera_incentivos">
                <div>
                    <h3>Incentivos de la campaña</h3>
                    <p class="subtexto-bloque">Agrega uno o varios incentivos con sus cantidades e imágenes.</p>
                </div>

                <button type="button" id="btn_agregar_incentivo" class="btn_secundario">
                    Agregar incentivo
                </button>
            </div>

            <div id="contenedor_incentivos">
                <div class="fila_incentivo" data-index="1">
                    <div class="campo_incentivo">
                        <label for="nombre_incentivo_1">Incentivo</label>
                        <input
                            type="text"
                            name="nombre_incentivo[]"
                            id="nombre_incentivo_1"
                            class="caja-inputs"
                            placeholder="Nombre del incentivo"
                            required
                        >
                    </div>

                    <div class="campo_incentivo">
                        <label for="cantidad_solicitada_1">Cantidad solicitada</label>
                        <input
                            type="number"
                            name="cantidad_solicitada[]"
                            id="cantidad_solicitada_1"
                            class="caja-inputs"
                            min="0"
                            step="1"
                            placeholder="0"
                            required
                        >
                    </div>

                    <div class="campo_incentivo">
                        <label for="stock_1">Stock</label>
                        <input
                            type="number"
                            name="stock[]"
                            id="stock_1"
                            class="caja-inputs"
                            min="0"
                            step="1"
                            placeholder="0"
                            required
                        >
                    </div>

                    <div class="campo_incentivo">
                        <label for="imagen_1">Imagen</label>
                        <input
                            type="file"
                            name="imagen[]"
                            id="imagen_1"
                            class="caja-inputs"
                            accept=".jpg,.jpeg,.png,.webp"
                        >
                    </div>

                    <div class="campo_incentivo acciones_incentivo">
                        <label>&nbsp;</label>
                        <button type="button" class="btn_eliminar_incentivo" disabled>
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div id="acciones_formulario">
            <button type="submit" id="btn_guardar_campana" class="btn_principal">
                Guardar campaña
            </button>

            <button type="reset" id="btn_reset_campana" class="btn_secundario">
                Cancelar
            </button>
        </div>

        <div id="respuesta_formulario"></div>
    </form>
</div>
<div id="toast_container" class="toast-container"></div>

<?php if (!isset($campanas_del_dia)) { $campanas_del_dia = []; } ?>

<section class="tabla-campanas-card">
    <div class="form-card-header">
        <h2>Campañas activas del día</h2>
        <p>Consulta y edita las campañas vigentes de hoy.</p>
    </div>

    <div class="tabla-campanas-wrapper">
        <div class="tabla-campanas-head tabla-campanas-grid">
            <div>Callcenter</div>
            <div>Incentivo para</div>
            <div>Programador</div>
            <div>Indicador</div>
            <div>Incentivos</div>
            <div>Fecha</div>
            <div>Horario</div>
            <div>Acciones</div>
        </div>

        <?php if (!empty($campanas_del_dia)): ?>
            <?php foreach ($campanas_del_dia as $campana): ?>
                <div class="tabla-campanas-row tabla-campanas-grid" data-id-campana="<?php echo (int)$campana['id_campana']; ?>">
                    <div class="celda">
                        <span class="modo-texto"><?php echo htmlspecialchars($campana['nombre_callcenter']); ?></span>
                    </div>

                    <div class="celda">
                        <span class="modo-texto">
                            <?php echo ((int)$campana['incentivo_para'] === 1) ? 'Ejecutivo' : 'Supervisor'; ?>
                        </span>
                        <select class="modo-edicion caja-select campo-editable" data-field="incentivo_para" style="display:none;">
                            <option value="1" <?php echo ((int)$campana['incentivo_para'] === 1) ? 'selected' : ''; ?>>Ejecutivo</option>
                            <option value="2" <?php echo ((int)$campana['incentivo_para'] === 2) ? 'selected' : ''; ?>>Supervisor</option>
                        </select>
                    </div>

                    <div class="celda">
                        <span class="modo-texto"><?php echo htmlspecialchars($campana['programadores']); ?></span>
                        <select class="modo-edicion caja-select campo-editable" data-field="id_programador" style="display:none;">
                            <?php foreach ($programadores as $programador): ?>
                                <option value="<?php echo (int)$programador['id']; ?>" <?php echo ((int)$campana['id_programador'] === (int)$programador['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($programador['programadores']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="celda">
                        <span class="modo-texto"><?php echo htmlspecialchars($campana['indicadores']); ?></span>
                        <select class="modo-edicion caja-select campo-editable" data-field="id_indicador" style="display:none;">
                            <?php foreach ($indicadores as $indicador): ?>
                                <option value="<?php echo (int)$indicador['id']; ?>" <?php echo ((int)$campana['id_indicador'] === (int)$indicador['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($indicador['indicadores']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="celda">
                        <div class="lista-incentivos">
                            <?php foreach ($campana['incentivos'] as $incentivo): ?>
<div class="item-incentivo" data-id-incentivo="<?php echo (int)$incentivo['id_campana_incentivo']; ?>">

    <div class="modo-texto">
        <strong><?php echo htmlspecialchars($incentivo['nombre_incentivo']); ?></strong>
        <span>Cant: <?php echo (int)$incentivo['cantidad_solicitada']; ?></span>
        <span>Stock: <?php echo (int)$incentivo['stock']; ?></span>
    </div>

    <div class="modo-edicion incentivo-edicion" style="display:none;">
        <input
            type="text"
            class="caja-inputs campo-editable-incentivo"
            data-field="nombre_incentivo"
            value="<?php echo htmlspecialchars($incentivo['nombre_incentivo']); ?>"
        >

        <input
            type="number"
            class="caja-inputs campo-editable-incentivo"
            data-field="cantidad_solicitada"
            value="<?php echo (int)$incentivo['cantidad_solicitada']; ?>"
            min="0"
        >

        <input
            type="number"
            class="caja-inputs campo-editable-incentivo"
            data-field="stock"
            value="<?php echo (int)$incentivo['stock']; ?>"
            min="0"
        >
    </div>

</div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="celda">
                        <span class="modo-texto"><?php echo htmlspecialchars($campana['fecha']); ?></span>
                        <input type="date" class="modo-edicion caja-inputs campo-editable" data-field="fecha" value="<?php echo htmlspecialchars($campana['fecha']); ?>" style="display:none;">
                    </div>

                    <div class="celda">
                        <span class="modo-texto"><?php echo htmlspecialchars($campana['hora_inicio'] . ' - ' . $campana['hora_fin']); ?></span>

                        <div class="modo-edicion horario-edicion" style="display:none;">
                            <input type="time" class="caja-inputs campo-editable" data-field="hora_inicio" value="<?php echo htmlspecialchars($campana['hora_inicio']); ?>">
                            <input type="time" class="caja-inputs campo-editable" data-field="hora_fin" value="<?php echo htmlspecialchars($campana['hora_fin']); ?>">
                        </div>
                    </div>

                    <div class="celda acciones-celda">
                        <div class="acciones-modo-texto">
                            <button type="button" class="btn_secundario btn-editar-campana">Editar</button>
                        </div>

                        <div class="acciones-modo-edicion" style="display:none;">
                            <button type="button" class="btn_principal btn-guardar-campana">Guardar</button>
                            <button type="button" class="btn_eliminar_incentivo btn-cancelar-edicion">Cancelar</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="tabla-vacia">
                No hay campañas activas registradas para el día de hoy.
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="ACTIVACIONES_SL/JS/campanas.js"></script>