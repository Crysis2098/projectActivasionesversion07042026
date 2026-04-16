<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../MODELS/entrega_premios_model.php';
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
$folio_canje = isset($_POST['folio_canje']) ? trim((string)$_POST['folio_canje']) : '';
$id_admin = isset($_POST['id_admin']) ? (int)$_POST['id_admin'] : 0;
$entregado = isset($_POST['entregado']) ? (int)$_POST['entregado'] : -1;
if ($folio_canje === '') {
   responder(false, 'No se recibió un folio de canje válido.');
}
if ($id_admin <= 0) {
   responder(false, 'No se identificó al administrador.');
}
if (!in_array($entregado, [0, 1], true)) {
   responder(false, 'Estatus de entrega inválido.');
}
$resultado = actualizar_entrega_premio_por_folio($conn, $folio_canje, $entregado, $id_admin);
if ($resultado !== true) {
   responder(false, 'No fue posible actualizar la entrega del premio.', [
       'debug' => $resultado
   ]);
}
responder(true, $entregado === 1 ? 'Premio marcado como entregado.' : 'Premio marcado como pendiente.');