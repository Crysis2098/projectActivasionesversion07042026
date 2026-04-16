<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../SERVICES/mailer_service.php';
require_once __DIR__ . '/../../MODELS/ventas_model.php';

function responder($ok, $mensaje, $extra = [])
{
    echo json_encode(array_merge([
        'ok' => $ok,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

$id_campana = isset($_POST['id_campana']) ? (int)$_POST['id_campana'] : 0;
$id_campana_incentivo = isset($_POST['id_campana_incentivo']) ? (int)$_POST['id_campana_incentivo'] : 0;
$id_empleado = isset($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : 0;
$modalidad = isset($_POST['modalidad']) ? $_POST['modalidad'] : '';


if ($id_campana <= 0 || $id_campana_incentivo <= 0 || $id_empleado <= 0) {
    responder(false, 'Datos insuficientes para realizar el canje.');
}

$incentivo = obtener_incentivo_por_id($conn, $id_campana_incentivo, $id_campana);

if (!$incentivo) {
    responder(false, 'El incentivo seleccionado no existe o ya no está disponible.');
}

$cantidad_requerida = (int)$incentivo['cantidad_solicitada'];
$stock_actual = (int)$incentivo['stock'];
$nombre_incentivo = $incentivo['nombre_incentivo'];

if ($cantidad_requerida <= 0) {
    responder(false, 'La configuración del incentivo no es válida.');
}

if ($stock_actual <= 0) {
    responder(false, 'Este incentivo ya no tiene stock disponible.');
}

$ventas_disponibles = contar_ventas_aprobadas_disponibles_para_canje($conn, $id_empleado, $id_campana);

if ($ventas_disponibles < $cantidad_requerida) {
    responder(false, 'No cuentas con suficientes ventas aprobadas para canjear este incentivo.');
}

if (!sqlsrv_begin_transaction($conn)) {
    responder(false, 'No fue posible iniciar la transacción.');
}

$ids_ventas = obtener_ids_ventas_para_canje($conn, $id_empleado, $id_campana, $cantidad_requerida);

if (count($ids_ventas) < $cantidad_requerida) {
    sqlsrv_rollback($conn);
    responder(false, 'No se encontraron suficientes ventas aprobadas disponibles para el canje.');
}

$folio_canje = generar_folio_canje();

$resultadoVentas = aplicar_canje_a_ventas($conn, $ids_ventas, $nombre_incentivo, $folio_canje);

if ($resultadoVentas !== true) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible aplicar el canje a las ventas.', [
        'debug' => $resultadoVentas
    ]);
}

$resultadoStock = descontar_stock_incentivo($conn, $id_campana_incentivo);

if ($resultadoStock !== true) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible actualizar el stock del incentivo.', [
        'debug' => $resultadoStock
    ]);
}

if (!sqlsrv_commit($conn)) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible confirmar el canje.');
}

$nombre_empleado = obtener_nombre_empleado_por_id($conn, $id_empleado);
$datos_campana = obtener_datos_basicos_campana_para_canje($conn, $id_campana);

$correo = enviar_correo_canje_premio([
    'nombre_empleado' => $nombre_empleado,
    'id_empleado' => $id_empleado,
    'campania' => $datos_campana ? $datos_campana['programadores'] : ('ID campaña ' . $id_campana),
    'modalidad' => $modalidad,
    'incentivo' => $nombre_incentivo,
    'folio_canje' => $folio_canje,
    'cantidad_ventas_usadas' => $cantidad_requerida,
    'fecha_canje' => date('Y-m-d H:i:s')
]);

responder(true, 'Canje realizado correctamente.', [
    'folio_canje' => $folio_canje,
    'correo' => $correo
]);