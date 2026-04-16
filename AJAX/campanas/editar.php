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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

$id_campana = isset($_POST['id_campana']) ? (int)$_POST['id_campana'] : 0;
$incentivo_para = isset($_POST['incentivo_para']) ? (int)$_POST['incentivo_para'] : 0;
$id_programador = isset($_POST['id_programador']) ? (int)$_POST['id_programador'] : 0;
$id_indicador = isset($_POST['id_indicador']) ? (int)$_POST['id_indicador'] : 0;
$fecha = isset($_POST['fecha']) ? limpiar_texto($_POST['fecha']) : '';
$hora_inicio = isset($_POST['hora_inicio']) ? limpiar_texto($_POST['hora_inicio']) : '';
$hora_fin = isset($_POST['hora_fin']) ? limpiar_texto($_POST['hora_fin']) : '';

/*
    Temporal:
    mientras sigues probando contigo, puedes usar el mismo id.
    Después aquí puedes tomar el usuario real en sesión.
*/
$actualizado_por = 5274;

if ($id_campana <= 0) {
    responder(false, 'No se recibió un id_campana válido.');
}

if (!in_array($incentivo_para, [1, 2], true)) {
    responder(false, 'Selecciona correctamente el campo "Incentivo para".');
}

if ($id_programador <= 0) {
    responder(false, 'Selecciona un programador válido.');
}

if ($id_indicador <= 0) {
    responder(false, 'Selecciona un indicador válido.');
}

if ($fecha === '') {
    responder(false, 'La fecha es obligatoria.');
}

if ($hora_inicio === '' || $hora_fin === '') {
    responder(false, 'Debes capturar el horario completo.');
}

if ($hora_inicio >= $hora_fin) {
    responder(false, 'La hora de inicio debe ser menor que la hora final.');
}

if (!sqlsrv_begin_transaction($conn)) {
    responder(false, 'No fue posible iniciar la transacción.');
}

/*
    1. Primero actualizar cabecera de campaña
*/
$resultado = actualizar_campana($conn, [
    'id_campana' => $id_campana,
    'incentivo_para' => $incentivo_para,
    'id_programador' => $id_programador,
    'id_indicador' => $id_indicador,
    'fecha' => $fecha,
    'hora_inicio' => $hora_inicio,
    'hora_fin' => $hora_fin,
    'actualizado_por' => $actualizado_por
]);

if ($resultado !== true) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible actualizar la campaña.', [
        'debug' => $resultado
    ]);
}

/*
    2. Después actualizar incentivos
*/
if (isset($_POST['incentivos']) && is_array($_POST['incentivos'])) {
    foreach ($_POST['incentivos'] as $id_incentivo => $datos) {
        $id_incentivo = (int)$id_incentivo;

        if ($id_incentivo <= 0) {
            sqlsrv_rollback($conn);
            responder(false, 'ID de incentivo inválido.');
        }

        $nombre = isset($datos['nombre_incentivo']) ? limpiar_texto($datos['nombre_incentivo']) : '';
        $cantidad = isset($datos['cantidad_solicitada']) ? (int)$datos['cantidad_solicitada'] : 0;
        $stock = isset($datos['stock']) ? (int)$datos['stock'] : 0;

        if ($nombre === '') {
            sqlsrv_rollback($conn);
            responder(false, 'El nombre del incentivo no puede estar vacío.');
        }

        if ($cantidad < 0 || $stock < 0) {
            sqlsrv_rollback($conn);
            responder(false, 'Cantidad y stock deben ser mayores o iguales a cero.');
        }

        $ok = actualizar_incentivo_campana(
            $conn,
            $id_incentivo,
            $nombre,
            $cantidad,
            $stock
        );

        if ($ok !== true) {
            sqlsrv_rollback($conn);
            responder(false, 'No fue posible actualizar los incentivos.', [
                'debug' => $ok
            ]);
        }
    }
}

if (!sqlsrv_commit($conn)) {
    sqlsrv_rollback($conn);
    responder(false, 'No fue posible confirmar los cambios.');
}

responder(true, 'Campaña actualizada correctamente.');