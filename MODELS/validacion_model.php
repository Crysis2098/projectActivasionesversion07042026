<?php

function obtener_texto_indicador_validacion($id_indicador)
{
    switch ((int)$id_indicador) {
        case 3:
            return 'Suscriptor nuevo';
        case 4:
            return 'Nueva línea';
        case 6:
            return 'Nuevo complemento';
        default:
            return 'Valor';
    }
}

function obtener_ventas_validacion_del_dia($conn, $tipo_usuario, $callcenter_usuario)
{
    $resultado = [
        'pendientes' => [],
        'aprobadas' => [],
        'rechazadas' => []
    ];

    $sql = "SELECT
                v.id,
                v.id_empleado,
                v.id_supervisor,
                v.campania,
                v.modalidad,
                v.cuenta,
                v.orden_servicio,
                v.estatus,
                v.comentarios,
                v.premio,
                v.incentivo,
                v.fecha,
                v.validado_por,
                v.fecha_validacion,
                v.fecha_validacion_termina,
                v.id_activacion,
                v.callcenter,
                v.asignado_a,
                v.rgus,
                v.comentarios_rgus,
                v.folio_canje,
                v.id_asignado_a,

                e.nombre AS nombre_ejecutivo,
                s.nombre AS nombre_supervisor,
                vv.nombre AS nombre_validado_por,
                aa.nombre AS nombre_asignado_a,

                c.id_indicador,
                c.observaciones,
                p.programadores,
                cc.callcenter AS nombre_callcenter,

                CONVERT(varchar(10), v.fecha_subida, 120) AS fecha_captura,
                CONVERT(varchar(5), v.fecha_subida, 108) AS hora_captura,
                CONVERT(varchar(16), v.fecha_validacion, 120) AS fecha_validacion_texto,
                CONVERT(varchar(16), v.fecha_validacion_termina, 120) AS fecha_validacion_termina_texto
            FROM tbl_activaciones_ventas v
            LEFT JOIN tbl_empleados e
                ON e.id_empleado = v.id_empleado
            LEFT JOIN tbl_empleados s
                ON s.id_empleado = v.id_supervisor
            LEFT JOIN tbl_empleados vv
                ON vv.id_empleado = v.validado_por
            LEFT JOIN tbl_empleados aa
                ON aa.id_empleado = v.id_asignado_a
            LEFT JOIN tbl_activaciones_sl_campanas c
                ON c.id_campana = v.id_activacion
            LEFT JOIN tbl_activaciones_programadores p
                ON p.id = c.id_programador
            LEFT JOIN tbl_callcenter cc
                ON cc.id_callcenter = v.callcenter
            WHERE CONVERT(date, v.fecha_subida) = CONVERT(date, GETDATE())
              AND v.estatus IN (0,1,2)";

    $params = [];

    if ($tipo_usuario === 'administrador_callcenter') {
        $sql .= " AND v.callcenter = ?";
        $params[] = (int)$callcenter_usuario;
    }

    $sql .= " ORDER BY
                CASE v.estatus
                    WHEN 0 THEN 1
                    WHEN 1 THEN 2
                    WHEN 2 THEN 3
                    ELSE 4
                END,
                v.fecha_subida DESC,
                v.id DESC";

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return $resultado;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $registro = [
            'id' => (int)$row['id'],
            'id_empleado' => (int)$row['id_empleado'],
            'id_supervisor' => $row['id_supervisor'] !== null ? (int)$row['id_supervisor'] : null,
            'campania' => $row['campania'] ?: '',
            'modalidad' => $row['modalidad'] ?: '',
            'cuenta' => $row['cuenta'] ?: '',
            'orden_servicio' => $row['orden_servicio'] ?: '',
            'estatus' => (int)$row['estatus'],
            'comentarios_admin' => $row['comentarios'] ?: '',
            'incentivo' => $row['incentivo'] ?: '',
            'validado_por' => $row['validado_por'] !== null ? (int)$row['validado_por'] : null,
            'fecha_validacion' => $row['fecha_validacion_texto'] ?: '',
            'fecha_validacion_termina' => $row['fecha_validacion_termina_texto'] ?: '',
            'id_activacion' => $row['id_activacion'] !== null ? (int)$row['id_activacion'] : null,
            'callcenter' => $row['callcenter'] !== null ? (int)$row['callcenter'] : null,
            'nombre_callcenter' => $row['nombre_callcenter'] ?: '',
            'rgus' => $row['rgus'] !== null ? (int)$row['rgus'] : 0,
            'comentarios_validador' => $row['comentarios_rgus'] ?: '',
            'folio_canje' => $row['folio_canje'] ?: '',
            'id_asignado_a' => $row['id_asignado_a'] !== null ? (int)$row['id_asignado_a'] : null,
            'nombre_ejecutivo' => $row['nombre_ejecutivo'] ?: '',
            'nombre_supervisor' => $row['nombre_supervisor'] ?: '',
            'nombre_validado_por' => $row['nombre_validado_por'] ?: '',
            'nombre_asignado_a' => $row['nombre_asignado_a'] ?: '',
            'id_indicador' => $row['id_indicador'] !== null ? (int)$row['id_indicador'] : 0,
            'observaciones' => $row['observaciones'] ?: '',
            'texto_indicador' => obtener_texto_indicador_validacion($row['id_indicador']),
            'programador' => $row['programadores'] ?: $row['campania'],
            'fecha_captura' => $row['fecha_captura'] ?: '',
            'hora_captura' => $row['hora_captura'] ?: ''
        ];

        if ($registro['estatus'] === 0) {
            $resultado['pendientes'][] = $registro;
        } elseif ($registro['estatus'] === 1) {
            $resultado['aprobadas'][] = $registro;
        } elseif ($registro['estatus'] === 2) {
            $resultado['rechazadas'][] = $registro;
        }
    }

    return $resultado;
}

function asignar_venta_validacion($conn, $id_venta, $id_validador)
{
    $sql = "UPDATE tbl_activaciones_ventas
            SET
                id_asignado_a = ?,
                validado_por = ?,
                fecha_validacion = GETDATE()
            WHERE id = ?
              AND estatus IN (0,1,2)
              AND id_asignado_a IS NULL";

    $params = [
        (int)$id_validador,
        (int)$id_validador,
        (int)$id_venta
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'asignar_venta',
            'errores' => sqlsrv_errors()
        ];
    }

    return true;
}

function guardar_validacion_venta($conn, array $data)
{
    $sql = "UPDATE tbl_activaciones_ventas
            SET
                estatus = ?,
                comentarios_rgus = ?,
                validado_por = ?,
                fecha_validacion_termina = GETDATE()
            WHERE id = ?
              AND id_asignado_a = ?";

    $params = [
        (int)$data['estatus'],
        $data['comentarios_validador'],
        (int)$data['validado_por'],
        (int)$data['id_venta'],
        (int)$data['id_asignado_a']
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'guardar_validacion',
            'errores' => sqlsrv_errors()
        ];
    }

    return true;
}