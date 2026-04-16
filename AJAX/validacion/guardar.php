<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../MODELS/validacion_model.php';

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

$id_venta = isset($_POST['id_venta']) ? (int)$_POST['id_venta'] : 0;
$id_asignado_a = isset($_POST['id_asignado_a']) ? (int)$_POST['id_asignado_a'] : 0;
$estatus = isset($_POST['estatus']) ? (int)$_POST['estatus'] : -1;
$comentarios_validador = isset($_POST['comentarios_validador']) ? limpiar_texto($_POST['comentarios_validador']) : '';

if ($id_venta <= 0 || $id_asignado_a <= 0) {
    responder(false, 'Datos insuficientes para guardar la validación.');
}

if (!in_array($estatus, [1, 2], true)) {
    responder(false, 'Selecciona un estatus válido.');
}

$resultado = guardar_validacion_venta($conn, [
    'id_venta' => $id_venta,
    'id_asignado_a' => $id_asignado_a,
    'validado_por' => $id_asignado_a,
    'estatus' => $estatus,
    'comentarios_validador' => $comentarios_validador
]);

if ($resultado !== true) {
    responder(false, 'No fue posible guardar la validación.', [
        'debug' => $resultado
    ]);
}

responder(true, 'La validación se guardó correctamente.');