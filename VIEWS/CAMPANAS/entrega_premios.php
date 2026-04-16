<?php
if (!isset($canjes_entrega)) {
    $canjes_entrega = [];
}

if (!isset($id_empleado)) {
    $id_empleado = 0;
}

$fecha_hoy = date('Y-m-d');
$fecha_inicio_actual = isset($_GET['fecha_inicio']) && trim($_GET['fecha_inicio']) !== '' ? trim($_GET['fecha_inicio']) : $fecha_hoy;
$fecha_fin_actual = isset($_GET['fecha_fin']) && trim($_GET['fecha_fin']) !== '' ? trim($_GET['fecha_fin']) : $fecha_hoy;
$estatus_entrega_actual = isset($_GET['estatus_entrega']) && trim($_GET['estatus_entrega']) !== '' ? trim($_GET['estatus_entrega']) : 'todos';
$check_actual = isset($_GET['check']) ? $_GET['check'] : '6641311242';
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Entrega de premios</span>
</div>

<div id="contenido_formulario">
    <section class="form-card">
        <div class="form-card-header">
            <h2>Filtros</h2>
            <p>Consulta y marca la entrega física de incentivos por folio de canje.</p>
        </div>

        <form method="GET" class="form-filtros-entrega">
            <input type="hidden" name="check" value="<?php echo htmlspecialchars($check_actual); ?>">
            <input type="hidden" name="vista" value="entrega_premios">

            <div class="form-grid filtros-entrega-grid">
                <div class="form-field">
                    <label for="fecha_inicio">Fecha inicio</label>
                    <input
                        type="date"
                        name="fecha_inicio"
                        id="fecha_inicio"
                        class="caja-inputs"
                        value="<?php echo htmlspecialchars($fecha_inicio_actual); ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="fecha_fin">Fecha fin</label>
                    <input
                        type="date"
                        name="fecha_fin"
                        id="fecha_fin"
                        class="caja-inputs"
                        value="<?php echo htmlspecialchars($fecha_fin_actual); ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="estatus_entrega">Estatus de entrega</label>
                    <select name="estatus_entrega" id="estatus_entrega" class="caja-select">
                        <option value="todos" <?php echo ($estatus_entrega_actual === 'todos') ? 'selected' : ''; ?>>Todos</option>
                        <option value="pendientes" <?php echo ($estatus_entrega_actual === 'pendientes') ? 'selected' : ''; ?>>Pendientes</option>
                        <option value="entregados" <?php echo ($estatus_entrega_actual === 'entregados') ? 'selected' : ''; ?>>Entregados</option>
                    </select>
                </div>
            </div>

            <div id="acciones_formulario">
                <button type="submit" class="btn_principal">Filtrar</button>
                <a href="?check=<?php echo htmlspecialchars($check_actual); ?>&vista=entrega_premios" class="btn_secundario btn-link-like">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="tabla-campanas-card">
        <div class="form-card-header">
            <h2>Checklist de entrega física</h2>
            <p>Marca si el incentivo ya fue entregado físicamente al ejecutivo.</p>
        </div>

        <div class="tabla-responsive">
            <table class="tabla-consulta-campanas tabla-entrega-premios">
                <thead>
                    <tr>
                        <th>Ejecutivo</th>
                        <th>Nombre del ejecutivo</th>
                        <th>Folio de canje</th>
                        <th>Incentivo otorgado</th>
                        <th>Ventas usadas</th>
                        <th>Callcenter</th>
                        <th>Fecha de canje</th>
                        <th>Entregado</th>
                    </tr>
                </thead>
                <tbody id="tabla_entrega_premios_body">
                    <?php if (!empty($canjes_entrega)): ?>
                        <?php foreach ($canjes_entrega as $canje): ?>
                            <tr>
                                <td><?php echo (int)$canje['id_empleado']; ?></td>
                                <td><?php echo htmlspecialchars($canje['nombre_empleado']); ?></td>
                                <td><?php echo htmlspecialchars($canje['folio_canje']); ?></td>
                                <td><?php echo htmlspecialchars($canje['incentivo']); ?></td>
                                <td><?php echo (int)$canje['ventas_usadas']; ?></td>
                                <td><?php echo htmlspecialchars($canje['callcenter']); ?></td>
                                <td><?php echo htmlspecialchars($canje['fecha_canje']); ?></td>
                                <td>
                                    <label class="switch-entrega">
                                        <input
                                            type="checkbox"
                                            class="check-entrega-premio"
                                            data-folio-canje="<?php echo htmlspecialchars($canje['folio_canje']); ?>"
                                            data-id-admin="<?php echo (int)$id_empleado; ?>"
                                            <?php echo ((int)$canje['entrega_premio_estatus'] === 1) ? 'checked' : ''; ?>
                                        >
                                        <span class="slider-entrega"></span>
                                    </label>
                                    <div class="texto-entrega-estado">
                                        <?php echo ((int)$canje['entrega_premio_estatus'] === 1) ? 'Entregado' : 'Pendiente'; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="tabla-vacia-celda">
                                No hay canjes registrados con los filtros seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="toast_container" class="toast-container"></div>

<script src="ACTIVACIONES_SL/JS/entrega_premios.js"></script>
