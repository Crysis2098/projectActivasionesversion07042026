<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../MODELS/consultar_campanas_model.php';
require_once __DIR__ . '/../../detectar_plantilla.php';
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
$filtros = [
   'fecha_inicio' => isset($_POST['fecha_inicio']) ? trim((string)$_POST['fecha_inicio']) : '',
   'fecha_fin' => isset($_POST['fecha_fin']) ? trim((string)$_POST['fecha_fin']) : '',
   'callcenter' => isset($_POST['callcenter']) ? trim((string)$_POST['callcenter']) : 'todos',
   'programador' => isset($_POST['programador']) ? trim((string)$_POST['programador']) : 'todos',
   'revisar_por' => isset($_POST['revisar_por']) ? trim((string)$_POST['revisar_por']) : 'general',
   'estatus' => isset($_POST['estatus']) ? trim((string)$_POST['estatus']) : 'todos',
];
$tipo_usuario = isset($_POST['tipo_usuario']) ? trim((string)$_POST['tipo_usuario']) : '';
$callcenter_usuario = isset($_POST['callcenter_usuario']) ? (int)$_POST['callcenter_usuario'] : 0;
if ($filtros['fecha_inicio'] === '' || $filtros['fecha_fin'] === '') {
   responder(false, 'Debes seleccionar fecha inicio y fecha fin.');
}
$data = consultar_registros_campanas($conn, $filtros, $tipo_usuario, $callcenter_usuario);
responder(true, 'Consulta realizada correctamente.', [
   'data' => $data
]);