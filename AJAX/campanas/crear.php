<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../CONEXION_CON_UTF8.PHP';
require_once __DIR__ . '/../../MODELS/campanas_model.php';

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

function subir_imagen_incentivo($archivo, $indice)
{
    if (!isset($archivo['name'][$indice]) || $archivo['name'][$indice] === '') {
        return null;
    }

    if (!isset($archivo['tmp_name'][$indice]) || $archivo['tmp_name'][$indice] === '') {
        return null;
    }

    $nombreOriginal = $archivo['name'][$indice];
    $tmpName = $archivo['tmp_name'][$indice];
    $error = $archivo['error'][$indice];
    $size = $archivo['size'][$indice];

    if ($error !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($size <= 0) {
        return false;
    }

    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensionesPermitidas, true)) {
        return false;
    }

    $carpetaBase = '../../UPLOADS/incentivos/';
    if (!is_dir($carpetaBase)) {
        mkdir($carpetaBase, 0777, true);
    }

    $nombreNuevo = 'incentivo_' . date('YmdHis') . '_' . $indice . '_' . mt_rand(1000, 9999) . '.' . $extension;
    $rutaDestino = $carpetaBase . $nombreNuevo;

    if (!move_uploaded_file($tmpName, $rutaDestino)) {
        return false;
    }

    return $nombreNuevo;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

$incentivo_para = isset($_POST['incentivo_para']) ? (int)$_POST['incentivo_para'] : 0;
$id_programador = isset($_POST['id_programador']) ? (int)$_POST['id_programador'] : 0;
$id_indicador = isset($_POST['id_indicador']) ? (int)$_POST['id_indicador'] : 0;
$fecha = isset($_POST['fecha']) ? limpiar_texto($_POST['fecha']) : '';
$hora_inicio = isset($_POST['hora_inicio']) ? limpiar_texto($_POST['hora_inicio']) : '';
$hora_fin = isset($_POST['hora_fin']) ? limpiar_texto($_POST['hora_fin']) : '';
$callcenter = isset($_POST['callcenter']) ? (int)$_POST['callcenter'] : 0;
$creado_por = isset($_POST['creado_por']) ? (int)$_POST['creado_por'] : 0;
$observaciones = isset($_POST['observaciones']) ? limpiar_texto($_POST['observaciones']) : '';

$nombres = isset($_POST['nombre_incentivo']) && is_array($_POST['nombre_incentivo']) ? $_POST['nombre_incentivo'] : [];
$cantidades = isset($_POST['cantidad_solicitada']) && is_array($_POST['cantidad_solicitada']) ? $_POST['cantidad_solicitada'] : [];
$stocks = isset($_POST['stock']) && is_array($_POST['stock']) ? $_POST['stock'] : [];

if (!in_array($incentivo_para, [1, 2], true)) {
    responder(false, 'Selecciona correctamente el campo "Incentivo para".');
}

if ($id_programador <= 0) {
    responder(false, 'Selecciona un programador.');
}

if ($id_indicador <= 0) {
    responder(false, 'Selecciona un indicador.');
}

if ($fecha === '') {
    responder(false, 'La fecha es obligatoria.');
}

if ($hora_inicio === '' || $hora_fin === '') {
    responder(false, 'Debes capturar el horario completo.');
}

if ($callcenter <= 0) {
    responder(false, 'No se pudo identificar el callcenter.');
}

if ($creado_por <= 0) {
    responder(false, 'No se pudo identificar al usuario creador.');
}

if (count($nombres) === 0) {
    responder(false, 'Debes agregar al menos un incentivo.');
}

$incentivos = [];

for ($i = 0; $i < count($nombres); $i++) {
    $nombre = isset($nombres[$i]) ? limpiar_texto($nombres[$i]) : '';
    $cantidad = isset($cantidades[$i]) ? (int)$cantidades[$i] : 0;
    $stock = isset($stocks[$i]) ? (int)$stocks[$i] : 0;

    if ($nombre === '') {
        continue;
    }

    if ($cantidad < 0 || $stock < 0) {
        responder(false, 'Cantidad y stock deben ser números iguales o mayores a cero.');
    }

    $nombreImagen = null;
    if (isset($_FILES['imagen']) && is_array($_FILES['imagen']['name'])) {
        $subida = subir_imagen_incentivo($_FILES['imagen'], $i);

        if ($subida === false) {
            responder(false, 'Una de las imágenes no pudo subirse o tiene un formato no permitido.');
        }

        $nombreImagen = $subida;
    }

    $incentivos[] = [
        'nombre_incentivo' => $nombre,
        'cantidad_solicitada' => $cantidad,
        'stock' => $stock,
        'imagen' => $nombreImagen,
        'orden_visual' => $i + 1
    ];
}

if (count($incentivos) === 0) {
    responder(false, 'Debes capturar al menos un incentivo válido.');
}

sqlsrv_begin_transaction($conn);

$dataCampana = [
    'incentivo_para' => $incentivo_para,
    'id_programador' => $id_programador,
    'id_indicador' => $id_indicador,
    'fecha' => $fecha,
    'hora_inicio' => $hora_inicio,
    'hora_fin' => $hora_fin,
    'callcenter' => $callcenter,
    'observaciones' => $observaciones,
    'creado_por' => $creado_por
];

$id_campana = insertar_campana($conn, $dataCampana);



if (!is_int($id_campana) || $id_campana <= 0) {

    sqlsrv_rollback($conn);

    responder(false, 'No fue posible guardar la campaña.', [

        'debug' => $id_campana

    ]);

}

foreach ($incentivos as $incentivo) {
    $ok = insertar_incentivo_campana($conn, [
        'id_campana' => $id_campana,
        'nombre_incentivo' => $incentivo['nombre_incentivo'],
        'cantidad_solicitada' => $incentivo['cantidad_solicitada'],
        'stock' => $incentivo['stock'],
        'imagen' => $incentivo['imagen'],
        'orden_visual' => $incentivo['orden_visual']
    ]);

    if (!$ok) {
        sqlsrv_rollback($conn);
        responder(false, 'No fue posible guardar los incentivos de la campaña.');
    }
}

sqlsrv_commit($conn);

responder(true, 'Campaña registrada correctamente.', [
    'id_campana' => $id_campana
]);