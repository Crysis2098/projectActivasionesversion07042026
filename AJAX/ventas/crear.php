<?php
 
header('Content-Type: application/json; charset=utf-8');
 
require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../MODELS/ventas_model.php';
 
function responder($ok, $mensaje, $extra = [])
{
    echo json_encode(array_merge([
        'ok' => $ok,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}
 
function limpiar_texto($valor)
{
    return trim((string)$valor);
}
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}
 
$id_activacion = isset($_POST['id_activacion']) ? (int)$_POST['id_activacion'] : 0;
$id_empleado = isset($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : 0;
$id_supervisor = isset($_POST['id_supervisor']) ? (int)$_POST['id_supervisor'] : 0;
$id_jefe = isset($_POST['id_jefe']) ? limpiar_texto($_POST['id_jefe']) : '';
$callcenter = isset($_POST['callcenter']) ? (int)$_POST['callcenter'] : 0;
$campania = isset($_POST['campania']) ? limpiar_texto($_POST['campania']) : '';
$modalidad = isset($_POST['modalidad']) ? limpiar_texto($_POST['modalidad']) : '';
$cuenta = isset($_POST['cuenta']) ? limpiar_texto($_POST['cuenta']) : '';
$orden_servicio = isset($_POST['orden_servicio']) ? limpiar_texto($_POST['orden_servicio']) : '';
$comentarios = isset($_POST['comentarios']) ? limpiar_texto($_POST['comentarios']) : '';
$rgus = isset($_POST['rgus']) && $_POST['rgus'] !== '' ? (int)$_POST['rgus'] : 0;
$comentarios_rgus = isset($_POST['comentarios_rgus']) ? limpiar_texto($_POST['comentarios_rgus']) : '';
 
if ($id_activacion <= 0) {
    responder(false, 'No se recibió una campaña válida.');
}
 
if ($id_empleado <= 0) {
    responder(false, 'No se identificó al empleado.');
}
 
if ($callcenter <= 0) {
    responder(false, 'No se identificó el callcenter.');
}
 
if ($campania === '') {
    responder(false, 'No se identificó la campaña.');
}
 
if ($modalidad === '') {
    responder(false, 'Selecciona una modalidad válida.');
}
 
if ($cuenta === '') {
    responder(false, 'La cuenta es obligatoria.');
}
 
if ($orden_servicio === '') {
    responder(false, 'La orden de servicio es obligatoria.');
}
 
/* VALIDACIÓN REAL DE OS DUPLICADA */
$validacionOrden = validar_orden_servicio_disponible($conn, $orden_servicio);
 
if ($validacionOrden['ok'] !== true) {
    responder(
        false,
        $validacionOrden['mensaje'],
        isset($validacionOrden['debug']) ? ['debug' => $validacionOrden['debug']] : []
    );
}
 
if ($rgus < 0) {
    responder(false, 'RGUs no puede ser menor a cero.');
}
 
$mes = (int)date('n');
$anio = (int)date('Y');
 
$data = [
    'id_empleado' => $id_empleado,
    'id_supervisor' => $id_supervisor,
    'campania' => $campania,
    'modalidad' => $modalidad,
    'cuenta' => $cuenta,
    'orden_servicio' => $orden_servicio,
    'estatus' => 0,
    'comentarios' => $comentarios,
    'premio' => null,
    'incentivo' => null,
    'mes' => $mes,
    'anio' => $anio,
    'id_jefe' => $id_jefe !== '' ? $id_jefe : null,
    'id_activacion' => $id_activacion,
    'callcenter' => $callcenter,
    'rgus' => $rgus,
    'comentarios_rgus' => $comentarios_rgus !== '' ? $comentarios_rgus : null
];
 
if (!sqlsrv_begin_transaction($conn)) {
    responder(false, 'No fue posible iniciar la transacción.');
}
 
$resultado = insertar_venta_activacion($conn, $data);
 
if (!is_int($resultado) || $resultado <= 0) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible guardar la venta.', [
        'debug' => $resultado
    ]);
}
 
if (!sqlsrv_commit($conn)) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible confirmar la venta.');
}
 
responder(true, 'Venta registrada correctamente.', [
    'id_venta' => $resultado
]);