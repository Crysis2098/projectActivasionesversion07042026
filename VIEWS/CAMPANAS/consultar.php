<?php
if (!isset($callcenters_disponibles)) {
   $callcenters_disponibles = [];
}
if (!isset($programadores)) {
   $programadores = [];
}
if (!isset($tipo_usuario)) {
   $tipo_usuario = '';
}
if (!isset($callcenter)) {
   $callcenter = 0;
}
$fecha_hoy = date('Y-m-d');
$es_admin_callcenter = ($tipo_usuario === 'administrador_callcenter');
?>
<link rel="stylesheet" href="ACTIVACIONES_SL/CSS/campanas.css">
<div id="cam_titulo">
<span>Consultar campañas</span>
</div>
<div id="contenido_formulario">
<section class="form-card">
<div class="form-card-header">
<h2>Filtros de consulta</h2>
<p>Selecciona los criterios para consultar campañas y ventas relacionadas.</p>
</div>
<form id="form_filtros_consulta" novalidate>
<input type="hidden" name="tipo_usuario" value="<?php echo htmlspecialchars($tipo_usuario); ?>">
<input type="hidden" name="callcenter_usuario" value="<?php echo (int)$callcenter; ?>">
<div class="form-grid filtros-consulta-grid">
<div class="form-field">
<label for="fecha_inicio">Fecha inicio</label>
<input
                       type="date"
                       name="fecha_inicio"
                       id="fecha_inicio"
                       class="caja-inputs"
                       value="<?php echo htmlspecialchars($fecha_hoy); ?>"
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
                       value="<?php echo htmlspecialchars($fecha_hoy); ?>"
                       required
>
</div>
<div class="form-field">
<label for="callcenter">Callcenter</label>
<select
                       name="callcenter"
                       id="callcenter"
                       class="caja-select"
<?php echo $es_admin_callcenter ? 'disabled' : ''; ?>
>
<option value="todos">Todos</option>
<?php foreach ($callcenters_disponibles as $cc): ?>
<option
                               value="<?php echo (int)$cc['id_callcenter']; ?>"
<?php echo ($es_admin_callcenter && (int)$cc['id_callcenter'] === (int)$callcenter) ? 'selected' : ''; ?>
>
<?php echo htmlspecialchars($cc['callcenter']); ?>
</option>
<?php endforeach; ?>
</select>
<?php if ($es_admin_callcenter): ?>
<input type="hidden" name="callcenter" value="<?php echo (int)$callcenter; ?>">
<?php endif; ?>
</div>
<div class="form-field">
<label for="programador">Programador</label>
<select name="programador" id="programador" class="caja-select">
<option value="todos">Todos</option>
<?php foreach ($programadores as $programador): ?>
<option value="<?php echo (int)$programador['id']; ?>">
<?php echo htmlspecialchars($programador['programadores']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="revisar_por">Revisar por</label>
<select name="revisar_por" id="revisar_por" class="caja-select">
<option value="general">General</option>
<option value="ganadores">Ganadores</option>
</select>
</div>
<div class="form-field">
<label for="estatus">Estatus</label>
<select name="estatus" id="estatus" class="caja-select">
<option value="todos">Todos</option>
<option value="0">Pendiente</option>
<option value="1">Aprobado</option>
<option value="2">Rechazado</option>
<option value="4">Canjeado</option>
</select>
</div>
</div>
<div id="acciones_formulario">
<button type="submit" class="btn_principal">Consultar</button>
<button type="button" id="btn_exportar_consulta" class="btn_secundario">Exportar</button>
</div>
</form>
</section>
<section class="tabla-campanas-card">
<div class="form-card-header">
<h2>Resultados</h2>
<p>Listado de campañas y ventas según los filtros seleccionados.</p>
</div>
<div class="tabla-responsive">
<table class="tabla-consulta-campanas" id="tabla_consulta_campanas">
<thead>
<tr>
<th>Callcenter</th>
<th>Gerente</th>
<th>Jefe</th>
<th>Supervisor</th>
<th>Ejecutivo</th>
<th>Telco</th>
<th>Subárea</th>
<th>Fecha</th>
<th>Horario de captura</th>
<th>Cuenta</th>
<th>Orden Servicio</th>
<th>Valor</th>
<th>Incentivo</th>
<th>Modalidad</th>
<th>Folio Canje</th>
<th>Estatus</th>
</tr>
</thead>
<tbody id="tabla_consulta_body">
<tr>
<td colspan="16" class="tabla-vacia-celda">
Realiza una consulta para ver resultados.
</td>
</tr>
</tbody>
</table>
</div>
</section>
</div>
<div id="toast_container" class="toast-container"></div>
<script src="ACTIVACIONES_SL/JS/consultar_campanas.js"></script>