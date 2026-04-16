<?php
if (!isset($metricas_validadores)) {
    $metricas_validadores = [];
}

$fecha_hoy = date('Y-m-d');
$fecha_inicio_actual = isset($_GET['fecha_inicio']) && trim($_GET['fecha_inicio']) !== '' ? trim($_GET['fecha_inicio']) : $fecha_hoy;
$fecha_fin_actual = isset($_GET['fecha_fin']) && trim($_GET['fecha_fin']) !== '' ? trim($_GET['fecha_fin']) : $fecha_hoy;
$check_actual = isset($_GET['check']) ? $_GET['check'] : '6641311242';
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Métricas de validación</span>
</div>

<div id="contenido_formulario">
    <section class="form-card">
        <div class="form-card-header">
            <h2>Filtros</h2>
            <p>Consulta el tiempo promedio de atención por validador.</p>
        </div>

        <form method="GET" class="form-filtros-entrega">
            <input type="hidden" name="check" value="<?php echo htmlspecialchars($check_actual); ?>">
            <input type="hidden" name="vista" value="metricas_validacion">

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
            </div>

            <div id="acciones_formulario">
                <button type="submit" class="btn_principal">Filtrar</button>
                <a href="?check=<?php echo htmlspecialchars($check_actual); ?>&vista=metricas_validacion" class="btn_secundario btn-link-like">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="tabla-campanas-card">
        <div class="form-card-header">
            <h2>Tiempo promedio por validador</h2>
            <p>Se calcula usando la diferencia entre fecha_validacion y fecha_validacion_termina.</p>
        </div>

        <div class="tabla-responsive">
            <table class="tabla-consulta-campanas tabla-metricas-validacion">
                <thead>
                    <tr>
                        <th>Nombre del validador</th>
                        <th>Ventas atendidas</th>
                        <th>Tiempo atención promedio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($metricas_validadores)): ?>
                        <?php foreach ($metricas_validadores as $fila): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['nombre_validador']); ?></td>
                                <td><?php echo (int)$fila['ventas_atendidas']; ?></td>
                                <td><?php echo htmlspecialchars($fila['promedio_formateado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="tabla-vacia-celda">
                                No hay validaciones terminadas en el rango seleccionado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>