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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

$id_venta = isset($_POST['id_venta']) ? (int)$_POST['id_venta'] : 0;
$id_validador = isset($_POST['id_validador']) ? (int)$_POST['id_validador'] : 0;

if ($id_venta <= 0 || $id_validador <= 0) {
    responder(false, 'Datos insuficientes para asignar la venta.');
}

$resultado = asignar_venta_validacion($conn, $id_venta, $id_validador);

if ($resultado !== true) {
    responder(false, 'No fue posible asignarte esta venta.', [
        'debug' => $resultado
    ]);
}

responder(true, 'La venta se te asignó correctamente.');