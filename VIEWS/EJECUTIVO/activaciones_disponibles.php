<?php
if (!isset($campanas_disponibles)) {
    $campanas_disponibles = [];
}
?>

<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">

<div id="cam_titulo">
    <span>Activación de Venta</span>
</div>

<div id="contenido_formulario">
    <section class="form-card">
        <div class="form-card-header">
            <h2>Selecciona la campaña en la que quieres participar</h2>
            <p>Elige una campaña activa y tu modalidad de trabajo.</p>
        </div>

        <?php if (!empty($campanas_disponibles)): ?>
            <form id="form_seleccion_campana" method="get" action="">
                <input type="hidden" name="check" value="<?php echo htmlspecialchars(isset($_GET['check']) ? $_GET['check'] : '6641311242'); ?>">
                <input type="hidden" name="vista" value="captura_ventas">

                <div class="campanas-disponibles-grid">
                    <?php foreach ($campanas_disponibles as $campana): ?>
                        <label class="card-campana-disponible">
                            <input
                                type="radio"
                                name="id_campana"
                                value="<?php echo (int)$campana['id_campana']; ?>"
                                required
                            >

                            <div class="card-campana-body">
                                <div class="card-campana-top">
                                    <?php if (!empty($campana['imagen'])): ?>
                                        <img
                                            src="ACTIVACIONES/imagenes/img_program/<?php echo htmlspecialchars($campana['imagen']); ?>"
                                            alt="<?php echo htmlspecialchars($campana['programadores']); ?>"
                                            class="card-campana-logo"
                                        >
                                    <?php else: ?>
                                        <div class="card-campana-logo-placeholder">
                                            <?php echo htmlspecialchars(substr($campana['programadores'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h3><?php echo htmlspecialchars($campana['programadores']); ?></h3>
                                        <p><?php echo htmlspecialchars($campana['indicadores']); ?></p>
                                    </div>
                                </div>

                                <div class="card-campana-meta">
                                    <span><?php echo htmlspecialchars($campana['nombre_callcenter']); ?></span>
                                    <span><?php echo htmlspecialchars($campana['hora_inicio'] . ' - ' . $campana['hora_fin']); ?></span>
                                </div>

                                <?php if (!empty($campana['observaciones'])): ?>
                                    <div class="card-campana-observaciones">
                                        <?php echo htmlspecialchars($campana['observaciones']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="modalidad-grid">
                    <label class="opcion-modalidad">
                        <input type="radio" name="modalidad" value="Presencial CC" required>
                        <span>Presencial CC</span>
                    </label>

                    <label class="opcion-modalidad">
                        <input type="radio" name="modalidad" value="Homeoffice" required>
                        <span>Homeoffice</span>
                    </label>
                </div>

                <div id="acciones_formulario">
                    <button type="submit" class="btn_principal">Continuar</button>
                    <a href="?check=<?php echo htmlspecialchars(isset($_GET['check']) ? $_GET['check'] : '6641311242'); ?>" class="btn_secundario btn-link-like">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <div class="vista-placeholder">
                No hay campañas activas disponibles en este momento.
            </div>
        <?php endif; ?>
    </section>
</div>