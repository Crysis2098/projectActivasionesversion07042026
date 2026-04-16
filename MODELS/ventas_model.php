<?php

function obtener_campanas_disponibles_ejecutivo($conn, $callcenter, $id_empleado = null)
{
    $campanas = [];

    $sql = "SELECT
                c.id_campana,
                c.incentivo_para,
                c.id_programador,
                p.programadores,
                p.imagen,
                c.id_indicador,
                i.indicadores,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.callcenter,
                cc.callcenter AS nombre_callcenter,
                c.observaciones
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            INNER JOIN tbl_activaciones_indicadores i
                ON i.id = c.id_indicador
            INNER JOIN tbl_callcenter cc
                ON cc.id_callcenter = c.callcenter
            WHERE c.estatus = 1
              AND c.incentivo_para = 1
              AND c.callcenter = ?
              AND c.fecha = CONVERT(date, GETDATE())
              AND CONVERT(time(0), GETDATE()) BETWEEN c.hora_inicio AND c.hora_fin
            ORDER BY c.hora_inicio ASC, c.id_campana DESC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$callcenter]);

    if ($stmt === false) {
        return $campanas;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['fecha'] instanceof DateTime) {
            $row['fecha'] = $row['fecha']->format('Y-m-d');
        }

        if ($row['hora_inicio'] instanceof DateTime) {
            $row['hora_inicio'] = $row['hora_inicio']->format('H:i');
        }

        if ($row['hora_fin'] instanceof DateTime) {
            $row['hora_fin'] = $row['hora_fin']->format('H:i');
        }

        $campanas[] = $row;
    }

    return $campanas;
}

function obtener_campana_disponible_por_id($conn, $id_campana, $callcenter)
{
    $sql = "SELECT
                c.id_campana,
                c.incentivo_para,
                c.id_programador,
                p.programadores,
                p.imagen,
                c.id_indicador,
                i.indicadores,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.callcenter,
                cc.callcenter AS nombre_callcenter,
                c.observaciones
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            INNER JOIN tbl_activaciones_indicadores i
                ON i.id = c.id_indicador
            INNER JOIN tbl_callcenter cc
                ON cc.id_callcenter = c.callcenter
            WHERE c.id_campana = ?
              AND c.estatus = 1
              AND c.incentivo_para = 1
              AND c.callcenter = ?
              AND c.fecha = CONVERT(date, GETDATE())";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana, (int)$callcenter]);

    if ($stmt === false) {
        return null;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    if ($row['fecha'] instanceof DateTime) {
        $row['fecha'] = $row['fecha']->format('Y-m-d');
    }

    if ($row['hora_inicio'] instanceof DateTime) {
        $row['hora_inicio'] = $row['hora_inicio']->format('H:i');
    }

    if ($row['hora_fin'] instanceof DateTime) {
        $row['hora_fin'] = $row['hora_fin']->format('H:i');
    }

    return $row;
}

function insertar_venta_activacion($conn, array $data)
{
    $sql = "INSERT INTO tbl_activaciones_ventas (
                id_empleado,
                id_supervisor,
                campania,
                modalidad,
                cuenta,
                orden_servicio,
                estatus,
                comentarios,
                premio,
                incentivo,
                fecha,
                validado_por,
                fecha_validacion,
                mes,
                anio,
                fecha_subida,
                id_super_ganador,
                id_jefe,
                estatus_premio,
                estatus_premio_super,
                fecha_premio,
                fecha_premio_super,
                id_activacion,
                callcenter,
                asignado_a,
                rgus,
                comentarios_rgus,
                folio_canje
            )
            OUTPUT INSERTED.id
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(),
                NULL, NULL, ?, ?, GETDATE(),
                NULL, ?, NULL, NULL, NULL, NULL,
                ?, ?, NULL, ?, ?, NULL
            )";

    $params = [
        (int)$data['id_empleado'],
        $data['id_supervisor'] > 0 ? (int)$data['id_supervisor'] : null,
        $data['campania'],
        $data['modalidad'],
        $data['cuenta'],
        $data['orden_servicio'],
        (int)$data['estatus'],
        $data['comentarios'],
        $data['premio'],
        $data['incentivo'],
        (int)$data['mes'],
        (int)$data['anio'],
        $data['id_jefe'],
        (int)$data['id_activacion'],
        (int)$data['callcenter'],
        $data['rgus'],
        $data['comentarios_rgus']
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'insert_venta',
            'errores' => sqlsrv_errors(),
            'params' => $params
        ];
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$row || !isset($row['id']) || $row['id'] === null) {
        return [
            'ok' => false,
            'fase' => 'output_inserted',
            'row' => $row
        ];
    }

    return (int)$row['id'];
}

function obtener_resumen_ventas_hoy_ejecutivo($conn, $id_empleado, $id_activacion)
{
    $resumen = [
        'realizadas' => 0,
        'aceptadas' => 0,
        'rechazadas' => 0,
        'pendientes' => 0,
        'canjeadas' => 0
    ];

    $sql = "SELECT
                COUNT(*) AS realizadas,
                SUM(CASE WHEN estatus = 1 THEN 1 ELSE 0 END) AS aceptadas,
                SUM(CASE WHEN estatus = 2 THEN 1 ELSE 0 END) AS rechazadas,
                SUM(CASE WHEN estatus = 0 THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estatus_premio = 1 THEN 1 ELSE 0 END) AS canjeadas
            FROM tbl_activaciones_ventas
            WHERE id_empleado = ?
              AND id_activacion = ?
              AND CONVERT(date, fecha_subida) = CONVERT(date, GETDATE())";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado, (int)$id_activacion]);

    if ($stmt === false) {
        return $resumen;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$row) {
        return $resumen;
    }

    $resumen['realizadas'] = isset($row['realizadas']) ? (int)$row['realizadas'] : 0;
    $resumen['aceptadas'] = isset($row['aceptadas']) ? (int)$row['aceptadas'] : 0;
    $resumen['rechazadas'] = isset($row['rechazadas']) ? (int)$row['rechazadas'] : 0;
    $resumen['pendientes'] = isset($row['pendientes']) ? (int)$row['pendientes'] : 0;
    $resumen['canjeadas'] = isset($row['canjeadas']) ? (int)$row['canjeadas'] : 0;

    return $resumen;
}

function obtener_ventas_hoy_ejecutivo($conn, $id_empleado, $id_activacion)
{
    $ventas = [];

    $sql = "SELECT
                v.id,
                v.id_empleado,
                e.nombre,
                v.cuenta,
                v.orden_servicio,
                v.campania,
                v.modalidad,
                v.estatus,
                v.comentarios,
                v.comentarios_rgus,
                v.rgus,
                v.fecha,
                v.fecha_subida
            FROM tbl_activaciones_ventas v
            LEFT JOIN tbl_empleados e
                ON e.id_empleado = v.id_empleado
            WHERE v.id_empleado = ?
              AND v.id_activacion = ?
              AND CONVERT(date, v.fecha_subida) = CONVERT(date, GETDATE())
            ORDER BY v.id DESC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado, (int)$id_activacion]);

    if ($stmt === false) {
        return $ventas;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['fecha'] instanceof DateTime) {
            $row['fecha'] = $row['fecha']->format('Y-m-d H:i:s');
        }

        if ($row['fecha_subida'] instanceof DateTime) {
            $row['fecha_subida'] = $row['fecha_subida']->format('Y-m-d H:i:s');
        }

        $ventas[] = $row;
    }

    return $ventas;
}

function obtener_texto_estatus_venta($estatus)
{
   $estatus = (int)$estatus;
   switch ($estatus) {
       case 0:
           return 'Pendiente';
       case 1:
           return 'Aceptada';
       case 2:
           return 'Rechazada';
       case 4:
           return 'Canjeada';
       default:
           return 'Desconocido';
   }
}


function obtener_ventas_aprobadas_disponibles_para_canje($conn, $id_empleado, $id_activacion)
{
    $ventas = [];

    $sql = "SELECT
                id,
                cuenta,
                orden_servicio,
                fecha_subida
            FROM tbl_activaciones_ventas
            WHERE id_empleado = ?
              AND id_activacion = ?
              AND estatus = 1
              AND (folio_canje IS NULL OR LTRIM(RTRIM(folio_canje)) = '')
            ORDER BY id ASC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado, (int)$id_activacion]);

    if ($stmt === false) {
        return $ventas;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['fecha_subida'] instanceof DateTime) {
            $row['fecha_subida'] = $row['fecha_subida']->format('Y-m-d H:i:s');
        }

        $ventas[] = $row;
    }

    return $ventas;
}

function contar_ventas_aprobadas_disponibles_para_canje($conn, $id_empleado, $id_activacion)
{
    $sql = "SELECT COUNT(*) AS total
            FROM tbl_activaciones_ventas
            WHERE id_empleado = ?
              AND id_activacion = ?
              AND estatus = 1
              AND (folio_canje IS NULL OR LTRIM(RTRIM(folio_canje)) = '')";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado, (int)$id_activacion]);

    if ($stmt === false) {
        return 0;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    return $row && isset($row['total']) ? (int)$row['total'] : 0;
}

function obtener_incentivos_canjeables_por_campana($conn, $id_campana)
{
    $incentivos = [];

    $sql = "SELECT
                id_campana_incentivo,
                id_campana,
                nombre_incentivo,
                cantidad_solicitada,
                stock,
                imagen,
                orden_visual
            FROM tbl_activaciones_sl_campanas_incentivos
            WHERE id_campana = ?
              AND estatus = 1
            ORDER BY orden_visual ASC, id_campana_incentivo ASC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana]);

    if ($stmt === false) {
        return $incentivos;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $incentivos[] = $row;
    }

    return $incentivos;
}

function generar_folio_canje()
{
    return 'CANJ-' . date('Ymd-His') . '-' . mt_rand(1000, 9999);
}

function obtener_incentivo_por_id($conn, $id_campana_incentivo, $id_campana)
{
    $sql = "SELECT
                id_campana_incentivo,
                id_campana,
                nombre_incentivo,
                cantidad_solicitada,
                stock,
                imagen,
                orden_visual
            FROM tbl_activaciones_sl_campanas_incentivos
            WHERE id_campana_incentivo = ?
              AND id_campana = ?
              AND estatus = 1";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana_incentivo, (int)$id_campana]);

    if ($stmt === false) {
        return null;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    return $row ?: null;
}

function obtener_ids_ventas_para_canje($conn, $id_empleado, $id_activacion, $cantidad_requerida)
{
    $ids = [];

    $sql = "SELECT TOP (" . (int)$cantidad_requerida . ")
                id
            FROM tbl_activaciones_ventas
            WHERE id_empleado = ?
              AND id_activacion = ?
              AND estatus = 1
              AND (folio_canje IS NULL OR LTRIM(RTRIM(folio_canje)) = '')
            ORDER BY id ASC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado, (int)$id_activacion]);

    if ($stmt === false) {
        return $ids;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $ids[] = (int)$row['id'];
    }

    return $ids;
}

function aplicar_canje_a_ventas($conn, array $ids_ventas, $nombre_incentivo, $folio_canje)
{
    if (empty($ids_ventas)) {
        return [
            'ok' => false,
            'fase' => 'ventas_vacias'
        ];
    }

    $placeholders = implode(',', array_fill(0, count($ids_ventas), '?'));

    $sql = "UPDATE tbl_activaciones_ventas
            SET
                estatus = 4,
                premio = ?,
                incentivo = ?,
                estatus_premio = 1,
                fecha_premio = GETDATE(),
                folio_canje = ?
            WHERE id IN ($placeholders)";

    $params = [
        $nombre_incentivo,
        $nombre_incentivo,
        $folio_canje
    ];

    foreach ($ids_ventas as $id) {
        $params[] = (int)$id;
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'update_ventas_canje',
            'errores' => sqlsrv_errors(),
            'params' => $params
        ];
    }

    return true;
}

function descontar_stock_incentivo($conn, $id_campana_incentivo)
{
    $sql = "UPDATE tbl_activaciones_sl_campanas_incentivos
            SET stock = stock - 1,
                fecha_actualizacion = GETDATE()
            WHERE id_campana_incentivo = ?
              AND stock > 0";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana_incentivo]);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'descontar_stock',
            'errores' => sqlsrv_errors()
        ];
    }

    return true;
}

function reenviar_venta_rechazada_a_pendiente($conn, $id_venta, $id_empleado, $id_activacion)
{
   $sql = "UPDATE tbl_activaciones_ventas
           SET
               estatus = 0,
               validado_por = NULL,
               fecha_validacion = NULL,
               fecha_validacion_termina = NULL,
               id_asignado_a = NULL,
               comentarios_rgus = NULL
           WHERE id = ?
             AND id_empleado = ?
             AND id_activacion = ?
             AND estatus = 2";
   $params = [
       (int)$id_venta,
       (int)$id_empleado,
       (int)$id_activacion
   ];
   $stmt = sqlsrv_query($conn, $sql, $params);
   if ($stmt === false) {
       return [
           'ok' => false,
           'fase' => 'reenviar_rechazada',
           'errores' => sqlsrv_errors(),
           'params' => $params
       ];
   }
   return true;
}

function existe_orden_servicio($conn, $orden_servicio)
{
    $sql = "SELECT TOP 1 id
            FROM tbl_activaciones_ventas
            WHERE LTRIM(RTRIM(orden_servicio)) = LTRIM(RTRIM(?))";
 
    $stmt = sqlsrv_query($conn, $sql, [$orden_servicio]);
 
    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'consulta_existencia_orden',
            'errores' => sqlsrv_errors()
        ];
    }
 
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
 
    return [
        'ok' => true,
        'existe' => $row ? true : false
    ];
}
 
function validar_orden_servicio_disponible($conn, $orden_servicio)
{
    $orden_servicio = trim((string)$orden_servicio);
 
    if ($orden_servicio === '') {
        return [
            'ok' => false,
            'mensaje' => 'La orden de servicio es obligatoria.'
        ];
    }
 
    $resultado = existe_orden_servicio($conn, $orden_servicio);
 
    if (!isset($resultado['ok']) || $resultado['ok'] !== true) {
        return [
            'ok' => false,
            'mensaje' => 'No fue posible validar la orden de servicio.',
            'debug' => $resultado
        ];
    }
 
    if (!empty($resultado['existe'])) {
        return [
            'ok' => false,
            'mensaje' => 'Orden de servicio ya existente/duplicada, captura una OS válida.'
        ];
    }
 
    return [
        'ok' => true,
        'mensaje' => 'La orden de servicio está disponible.'
    ];
}

function obtener_nombre_empleado_por_id($conn, $id_empleado)
{
    $sql = "SELECT nombre
            FROM tbl_empleados
            WHERE id_empleado = ?";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_empleado]);

    if ($stmt === false) {
        return '';
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    return ($row && isset($row['nombre'])) ? $row['nombre'] : '';
}

function obtener_datos_basicos_campana_para_canje($conn, $id_campana)
{
    $sql = "SELECT
                c.id_campana,
                p.programadores
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            WHERE c.id_campana = ?";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana]);

    if ($stmt === false) {
        return null;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    return $row ?: null;
}