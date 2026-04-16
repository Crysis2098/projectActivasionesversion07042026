<?php

function obtener_programadores_activos($conn)
{
    $programadores = [];

    $sql = "SELECT id, programadores, imagen
            FROM tbl_activaciones_programadores
            WHERE ISNULL(estatus, 1) = 1
            ORDER BY programadores ASC";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        return $programadores;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programadores[] = [
            'id' => (int)$row['id'],
            'programadores' => $row['programadores'],
            'imagen' => isset($row['imagen']) ? $row['imagen'] : null,
        ];
    }

    return $programadores;
}

function obtener_indicadores_activos($conn)
{
    $indicadores = [];

    $sql = "SELECT id, indicadores
            FROM tbl_activaciones_indicadores
            WHERE estatus = 1
            ORDER BY indicadores ASC";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        return $indicadores;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $indicadores[] = [
            'id' => (int)$row['id'],
            'indicadores' => $row['indicadores'],
        ];
    }

    return $indicadores;
}

function insertar_campana($conn, array $data)
{
    $sql = "INSERT INTO tbl_activaciones_sl_campanas (
                incentivo_para,
                id_programador,
                id_indicador,
                fecha,
                hora_inicio,
                hora_fin,
                callcenter,
                estatus,
                observaciones,
                creado_por,
                fecha_creacion,
                actualizado_por,
                fecha_actualizacion
            )
            OUTPUT INSERTED.id_campana
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, GETDATE(), NULL, NULL
            )";

    $params = [
        (int)$data['incentivo_para'],
        (int)$data['id_programador'],
        (int)$data['id_indicador'],
        $data['fecha'],
        $data['hora_inicio'],
        $data['hora_fin'],
        (int)$data['callcenter'],
        $data['observaciones'],
        (int)$data['creado_por'],
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'insert_campana',
            'errores' => sqlsrv_errors(),
            'params' => $params
        ];
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$row || !isset($row['id_campana']) || $row['id_campana'] === null) {
        return [
            'ok' => false,
            'fase' => 'output_inserted',
            'row' => $row
        ];
    }

    return (int)$row['id_campana'];
}

function insertar_incentivo_campana($conn, array $data)
{
    $sql = "INSERT INTO tbl_activaciones_sl_campanas_incentivos (
                id_campana,
                nombre_incentivo,
                cantidad_solicitada,
                stock,
                imagen,
                orden_visual,
                estatus,
                fecha_creacion,
                fecha_actualizacion
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, 1, GETDATE(), NULL
            )";

    $params = [
        (int)$data['id_campana'],
        trim($data['nombre_incentivo']),
        (int)$data['cantidad_solicitada'],
        (int)$data['stock'],
        $data['imagen'],
        (int)$data['orden_visual'],
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    return ($stmt !== false);
}

function obtener_campana_por_id($conn, $id_campana)
{
    $sql = "SELECT
                c.id_campana,
                c.incentivo_para,
                c.id_programador,
                p.programadores,
                c.id_indicador,
                i.indicadores,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.callcenter,
                c.estatus,
                c.observaciones,
                c.creado_por,
                c.fecha_creacion,
                c.actualizado_por,
                c.fecha_actualizacion
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            INNER JOIN tbl_activaciones_indicadores i
                ON i.id = c.id_indicador
            WHERE c.id_campana = ?";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana]);

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
        $row['hora_inicio'] = $row['hora_inicio']->format('H:i:s');
    }

    if ($row['hora_fin'] instanceof DateTime) {
        $row['hora_fin'] = $row['hora_fin']->format('H:i:s');
    }

    if ($row['fecha_creacion'] instanceof DateTime) {
        $row['fecha_creacion'] = $row['fecha_creacion']->format('Y-m-d H:i:s');
    }

    if ($row['fecha_actualizacion'] instanceof DateTime) {
        $row['fecha_actualizacion'] = $row['fecha_actualizacion']->format('Y-m-d H:i:s');
    }

    return $row;
}

function obtener_incentivos_por_campana($conn, $id_campana)
{
    $incentivos = [];

    $sql = "SELECT
                id_campana_incentivo,
                id_campana,
                nombre_incentivo,
                cantidad_solicitada,
                stock,
                imagen,
                orden_visual,
                estatus,
                fecha_creacion,
                fecha_actualizacion
            FROM tbl_activaciones_sl_campanas_incentivos
            WHERE id_campana = ?
            ORDER BY orden_visual ASC, id_campana_incentivo ASC";

    $stmt = sqlsrv_query($conn, $sql, [(int)$id_campana]);

    if ($stmt === false) {
        return $incentivos;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['fecha_creacion'] instanceof DateTime) {
            $row['fecha_creacion'] = $row['fecha_creacion']->format('Y-m-d H:i:s');
        }

        if ($row['fecha_actualizacion'] instanceof DateTime) {
            $row['fecha_actualizacion'] = $row['fecha_actualizacion']->format('Y-m-d H:i:s');
        }

        $incentivos[] = $row;
    }

    return $incentivos;
}

function obtener_campanas_activas($conn, $callcenter = null)
{
    $campanas = [];

    $sql = "SELECT
                c.id_campana,
                c.incentivo_para,
                c.id_programador,
                p.programadores,
                c.id_indicador,
                i.indicadores,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.callcenter,
                c.estatus,
                c.observaciones,
                c.creado_por,
                c.fecha_creacion
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            INNER JOIN tbl_activaciones_indicadores i
                ON i.id = c.id_indicador
            WHERE c.estatus = 1";

    $params = [];

    if ($callcenter !== null && $callcenter !== '') {
        $sql .= " AND c.callcenter = ?";
        $params[] = (int)$callcenter;
    }

    $sql .= " ORDER BY c.fecha DESC, c.hora_inicio DESC, c.id_campana DESC";

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return $campanas;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['fecha'] instanceof DateTime) {
            $row['fecha'] = $row['fecha']->format('Y-m-d');
        }

        if ($row['hora_inicio'] instanceof DateTime) {
            $row['hora_inicio'] = $row['hora_inicio']->format('H:i:s');
        }

        if ($row['hora_fin'] instanceof DateTime) {
            $row['hora_fin'] = $row['hora_fin']->format('H:i:s');
        }

        if ($row['fecha_creacion'] instanceof DateTime) {
            $row['fecha_creacion'] = $row['fecha_creacion']->format('Y-m-d H:i:s');
        }

        $campanas[] = $row;
    }

    return $campanas;
}

function obtener_callcenters_disponibles($conn)
{
    $callcenters = [];

    $sql = "SELECT id_callcenter, callcenter, visible
            FROM tbl_callcenter
            WHERE id_callcenter IN (1,2,4,20,28)
            ORDER BY callcenter ASC";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        return $callcenters;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $callcenters[] = [
            'id_callcenter' => (int)$row['id_callcenter'],
            'callcenter' => $row['callcenter'],
            'visible' => isset($row['visible']) ? (int)$row['visible'] : 0,
        ];
    }

    return $callcenters;
}

function obtener_campanas_del_dia($conn, $callcenter = null, $es_admin_global = false)
{
    $campanas = [];

    $sql = "SELECT
                c.id_campana,
                c.incentivo_para,
                c.id_programador,
                p.programadores,
                c.id_indicador,
                i.indicadores,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.callcenter,
                cc.callcenter AS nombre_callcenter,
                c.estatus,
                c.observaciones,
                c.creado_por,
                c.fecha_creacion
            FROM tbl_activaciones_sl_campanas c
            INNER JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            INNER JOIN tbl_activaciones_indicadores i
                ON i.id = c.id_indicador
            INNER JOIN tbl_callcenter cc
                ON cc.id_callcenter = c.callcenter
            WHERE c.estatus = 1
              AND c.fecha = CONVERT(date, GETDATE())";

    $params = [];

    if (!$es_admin_global && $callcenter !== null && $callcenter !== '') {
        $sql .= " AND c.callcenter = ?";
        $params[] = (int)$callcenter;
    }

    $sql .= " ORDER BY c.hora_inicio ASC, c.id_campana DESC";

    $stmt = sqlsrv_query($conn, $sql, $params);

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

        $row['incentivos'] = obtener_incentivos_por_campana($conn, $row['id_campana']);
        $campanas[] = $row;
    }

    return $campanas;
}

function actualizar_campana($conn, array $data)
{
    $sql = "UPDATE tbl_activaciones_sl_campanas
            SET
                incentivo_para = ?,
                id_programador = ?,
                id_indicador = ?,
                fecha = ?,
                hora_inicio = ?,
                hora_fin = ?,
                actualizado_por = ?,
                fecha_actualizacion = GETDATE()
            WHERE id_campana = ?";

    $params = [
        (int)$data['incentivo_para'],
        (int)$data['id_programador'],
        (int)$data['id_indicador'],
        $data['fecha'],
        $data['hora_inicio'],
        $data['hora_fin'],
        (int)$data['actualizado_por'],
        (int)$data['id_campana'],
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'update_campana',
            'errores' => sqlsrv_errors(),
            'params' => $params
        ];
    }

    return true;
}

function actualizar_incentivo_campana($conn, $id_incentivo, $nombre, $cantidad, $stock)
{
    $sql = "UPDATE tbl_activaciones_sl_campanas_incentivos
            SET
                nombre_incentivo = ?,
                cantidad_solicitada = ?,
                stock = ?,
                fecha_actualizacion = GETDATE()
            WHERE id_campana_incentivo = ?";

    $params = [
        trim((string)$nombre),
        (int)$cantidad,
        (int)$stock,
        (int)$id_incentivo
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'update_incentivo',
            'errores' => sqlsrv_errors(),
            'params' => $params
        ];
    }

    return true;
}