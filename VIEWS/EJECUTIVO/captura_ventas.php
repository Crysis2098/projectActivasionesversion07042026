<?php
if (!isset($campana_seleccionada)) {
    $campana_seleccionada = null;
}

if (!isset($modalidad)) {
    $modalidad = '';
}

if (!isset($resumen_ventas_hoy)) {
    $resumen_ventas_hoy = [
        'realizadas' => 0,
        'aceptadas' => 0,
        'rechazadas' => 0,
        'pendientes' => 0,
        'canjeadas' => 0
    ];
}

if (!isset($ventas_hoy)) {
    $ventas_hoy = [];
}

if (!isset($ventas_aprobadas_disponibles)) {
    $ventas_aprobadas_disponibles = 0;
}

if (!isset($incentivos_canjeables)) {
    $incentivos_canjeables = [];
}

$id_supervisor_actual = isset($id_super) ? (int)$id_super : 0;
$id_jefe_actual = isset($id_jefe) ? trim((string)$id_jefe) : '';
$check_actual = isset($_GET['check']) ? $_GET['check'] : '6641311242';
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Captura de Venta</span>
</div>

<div id="contenido_formulario">
    <?php if ($campana_seleccionada): ?>
        <form id="form_captura_venta" novalidate>
            <input type="hidden" name="id_activacion" value="<?php echo (int)$campana_seleccionada['id_campana']; ?>">
            <input type="hidden" name="id_empleado" value="<?php echo (int)$id_empleado; ?>">
            <input type="hidden" name="id_supervisor" value="<?php echo (int)$id_supervisor_actual; ?>">
            <input type="hidden" name="id_jefe" value="<?php echo htmlspecialchars($id_jefe_actual); ?>">
            <input type="hidden" name="callcenter" value="<?php echo (int)$callcenter; ?>">
            <input type="hidden" name="campania" value="<?php echo htmlspecialchars($campana_seleccionada['programadores']); ?>">
            <input type="hidden" name="modalidad" value="<?php echo htmlspecialchars($modalidad); ?>">
            <input type="hidden" name="check" value="<?php echo htmlspecialchars($check_actual); ?>">

            <section class="form-card">
                <div class="form-card-header">
                    <h2><?php echo htmlspecialchars($campana_seleccionada['programadores']); ?></h2>
                    <p>
                        Indicador: <?php echo htmlspecialchars($campana_seleccionada['indicadores']); ?>
                        · Modalidad: <?php echo htmlspecialchars($modalidad); ?>
                    </p>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Campaña</label>
                        <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($campana_seleccionada['programadores']); ?>" disabled>
                    </div>

                    <div class="form-field">
                        <label>Callcenter</label>
                        <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($campana_seleccionada['nombre_callcenter']); ?>" disabled>
                    </div>

                    <div class="form-field">
                        <label>Horario</label>
                        <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($campana_seleccionada['hora_inicio'] . ' - ' . $campana_seleccionada['hora_fin']); ?>" disabled>
                    </div>

                    <div class="form-field">
                        <label>Modalidad</label>
                        <input type="text" class="caja-inputs" value="<?php echo htmlspecialchars($modalidad); ?>" disabled>
                    </div>

                    <div class="form-field">
                        <label for="cuenta">Cuenta</label>
                        <input type="text" name="cuenta" id="cuenta" class="caja-inputs" maxlength="50" oninput="validateInput(this)" required>
                    </div>

                    <div class="form-field">
                        <label for="orden_servicio">Orden de servicio</label>
                        <input type="text" name="orden_servicio" id="orden_servicio" class="caja-inputs" maxlength="50" oninput="validateInput(this)" required>
                    </div>

                    <?php if($callcenter !== 20){   ?>
                    <div class="form-field">
                        <label for="rgus"><?php echo htmlspecialchars($texto_mostrar); ?></label>
                        <input type="number" name="rgus" id="rgus" class="caja-inputs" value="1" readonly>
                    </div> <?php }else{?>
                    <div class="form-field">
                        <label for="rgus"><?php echo htmlspecialchars($texto_mostrar); ?></label>
                        <input type="number" name="rgus" id="rgus" class="caja-inputs"min="1" max="5">
                    </div> <?php } ?>
                </div>
            </section>

            <div id="acciones_formulario">
                <button type="submit" class="btn_principal">Guardar venta</button>
                <a href="?check=<?php echo htmlspecialchars($check_actual); ?>&vista=activaciones_disponibles" class="btn_secundario btn-link-like">Regresar</a>
            </div>

            <div id="respuesta_formulario"></div>
        </form>

        <section class="ventas-hoy-card">
            <div class="form-card-header">
                <h2>Canje de premios</h2>
                <p>Ventas aprobadas disponibles para canje: <?php echo (int)$ventas_aprobadas_disponibles; ?></p>
            </div>

            <?php if (!empty($incentivos_canjeables)): ?>
                <div class="canje-grid">
                    <?php foreach ($incentivos_canjeables as $incentivo): ?>
                        <?php
                        $cantidad_requerida = (int)$incentivo['cantidad_solicitada'];
                        $img_incentivo = $incentivo['imagen'];
                        $stock_actual = (int)$incentivo['stock'];
                        $canjes_posibles_por_ventas = $cantidad_requerida > 0 ? floor($ventas_aprobadas_disponibles / $cantidad_requerida) : 0;
                        $canjes_reales_posibles = min($canjes_posibles_por_ventas, $stock_actual);
                        $puede_canjear = ($canjes_reales_posibles > 0);
                        ?>
                        <div class="canje-card">
                            <h3><?php echo htmlspecialchars($incentivo['nombre_incentivo']); ?></h3>
                            <img src="ACTIVACIONES_SL/UPLOADS/incentivos/<?php echo htmlspecialchars($img_incentivo); ?>" alt="img" class="card-campana-logo-captura-ventas">
                            <p>Requiere: <strong><?php echo $cantidad_requerida; ?></strong> ventas aprobadas</p>
                            <p>Stock: <strong><?php echo $stock_actual; ?></strong></p>
                            <p>Canjes posibles: <strong><?php echo (int)$canjes_reales_posibles; ?></strong></p>

                            <button
                                type="button"
                                class="btn_principal btn-canjear-premio"
                                data-id-campana="<?php echo (int)$campana_seleccionada['id_campana']; ?>"
                                data-id-incentivo="<?php echo (int)$incentivo['id_campana_incentivo']; ?>"
                                data-nombre-incentivo="<?php echo htmlspecialchars($incentivo['nombre_incentivo']); ?>"
                                data-modalidad="<?php echo htmlspecialchars($modalidad); ?>"
                                <?php echo $puede_canjear ? '' : 'disabled'; ?>
                            >
                                Canjear premio
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="tabla-vacia">
                    Esta campaña no tiene incentivos configurados.
                </div>
            <?php endif; ?>
        </section>

        <section class="ventas-hoy-card">
            <div class="form-card-header">
                <h2>Resumen del día</h2>
                <p>Estado actual de tus capturas para esta campaña.</p>
            </div>

            <div class="resumen-ventas-grid">
                <div class="resumen-box resumen-canjeadas">
                    <span class="resumen-label">Canjeadas</span>
                    <span class="resumen-value"><?php echo (int)$resumen_ventas_hoy['canjeadas']; ?></span>
                </div>

                <div class="resumen-box resumen-realizadas">
                    <span class="resumen-label">Realizadas</span>
                    <span class="resumen-value"><?php echo (int)$resumen_ventas_hoy['realizadas']; ?></span>
                </div>

                <div class="resumen-box resumen-aceptadas">
                    <span class="resumen-label">Aceptadas</span>
                    <span class="resumen-value"><?php echo (int)$resumen_ventas_hoy['aceptadas']; ?></span>
                </div>

                <div class="resumen-box resumen-rechazadas">
                    <span class="resumen-label">Rechazadas</span>
                    <span class="resumen-value"><?php echo (int)$resumen_ventas_hoy['rechazadas']; ?></span>
                </div>

                <div class="resumen-box resumen-pendientes">
                    <span class="resumen-label">Pendientes</span>
                    <span class="resumen-value"><?php echo (int)$resumen_ventas_hoy['pendientes']; ?></span>
                </div>
            </div>
        </section>

<section class="tabla-campanas-card">
<div class="form-card-header">
<h2>Desglose de cuentas capturadas</h2>
<p>Ventas registradas hoy por el ejecutivo en esta campaña.</p>
</div>
<div class="tabla-responsive">
<table class="tabla-captura-ventas">
<thead>
<tr>
<th>Ejecutivo</th>
<th>Cuenta</th>
<th>Orden Servicio</th>
<th>Suscripción</th>
<th>Estatus</th>
<th>Ventas</th>
<th>Fecha subida</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php if (!empty($ventas_hoy)): ?>
<?php foreach ($ventas_hoy as $venta): ?>
<tr>
<td><?php echo htmlspecialchars($venta['nombre'] ?: 'Ejecutivo'); ?></td>
<td><?php echo htmlspecialchars($venta['cuenta']); ?></td>
<td><?php echo htmlspecialchars($venta['orden_servicio']); ?></td>
<td><?php echo htmlspecialchars($venta['campania']); ?></td>
<td>
<?php echo htmlspecialchars(obtener_texto_estatus_venta($venta['estatus'])); ?>
</td>
<td><?php echo (int)$venta['rgus']; ?></td>
<td><?php echo htmlspecialchars($venta['fecha_subida']); ?></td>
<td>
<?php if ((int)$venta['estatus'] === 2): ?>
<button
type="button"
class="btn_secundario btn-reenviar-pendiente"
data-id-venta="<?php echo (int)$venta['id']; ?>"
data-id-empleado="<?php echo (int)$id_empleado; ?>"
data-id-activacion="<?php echo (int)$campana_seleccionada['id_campana']; ?>"
>Reconsideración</button>
<?php else: ?>
<span class="texto-sin-accion">—</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="8" class="tabla-vacia-celda">
Aún no has capturado ventas para esta campaña el día de hoy.
</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

        <div id="toast_container" class="toast-container"></div>
        <script src="ACTIVACIONES_SL/JS/captura_ventas.js"></script>
    <?php else: ?>
        <div class="vista-placeholder">
            La campaña seleccionada no existe, no está activa o ya no corresponde a tu callcenter.
        </div>
    <?php endif; ?>
</div>