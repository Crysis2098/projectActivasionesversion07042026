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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   responder(false, 'Método no permitido.');
}
$id_venta = isset($_POST['id_venta']) ? (int)$_POST['id_venta'] : 0;
$id_empleado = isset($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : 0;
$id_activacion = isset($_POST['id_activacion']) ? (int)$_POST['id_activacion'] : 0;
if ($id_venta <= 0 || $id_empleado <= 0 || $id_activacion <= 0) {
   responder(false, 'Datos insuficientes para reenviar la venta.');
}
$resultado = reenviar_venta_rechazada_a_pendiente($conn, $id_venta, $id_empleado, $id_activacion);
if ($resultado !== true) {
   responder(false, 'No fue posible reenviar la venta a pendientes.', [
       'debug' => $resultado
   ]);
}
responder(true, 'La venta rechazada volvió a pendientes correctamente.');