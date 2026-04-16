<?php

function obtener_canjes_para_entrega($conn, $tipo_usuario, $callcenter_usuario, array $filtros = [])
{
    $registros = [];

    $fecha_inicio = isset($filtros['fecha_inicio']) ? trim((string)$filtros['fecha_inicio']) : date('Y-m-d');
    $fecha_fin = isset($filtros['fecha_fin']) ? trim((string)$filtros['fecha_fin']) : date('Y-m-d');
    $estatus_entrega = isset($filtros['estatus_entrega']) ? trim((string)$filtros['estatus_entrega']) : 'todos';

    $sql = "SELECT
                v.folio_canje,
                v.id_empleado,
                e.nombre AS nombre_empleado,
                v.incentivo,
                v.callcenter,
                cc.callcenter AS nombre_callcenter,
                MAX(v.entrega_premio_estatus) AS entrega_premio_estatus,
                MAX(v.fecha_entrega_premio) AS fecha_entrega_premio,
                MAX(v.entregado_por) AS entregado_por,
                COUNT(*) AS ventas_usadas,
                MAX(v.fecha_premio) AS fecha_canje
            FROM tbl_activaciones_ventas v
            INNER JOIN tbl_empleados e
                ON e.id_empleado = v.id_empleado
            LEFT JOIN tbl_callcenter cc
                ON cc.id_callcenter = v.callcenter
            WHERE v.folio_canje IS NOT NULL
              AND LTRIM(RTRIM(v.folio_canje)) <> ''
              AND v.estatus = 4";

    $params = [];

    if ($fecha_inicio !== '' && $fecha_fin !== '') {
        $sql .= " AND CONVERT(date, v.fecha_premio) BETWEEN ? AND ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
    }

    if ($tipo_usuario === 'administrador_callcenter') {
        $sql .= " AND v.callcenter = ?";
        $params[] = (int)$callcenter_usuario;
    }

    if ($estatus_entrega === 'pendientes') {
        $sql .= " AND ISNULL(v.entrega_premio_estatus, 0) = 0";
    } elseif ($estatus_entrega === 'entregados') {
        $sql .= " AND ISNULL(v.entrega_premio_estatus, 0) = 1";
    }

    $sql .= " GROUP BY
                v.folio_canje,
                v.id_empleado,
                e.nombre,
                v.incentivo,
                v.callcenter,
                cc.callcenter
              ORDER BY
                MAX(v.fecha_premio) DESC,
                v.folio_canje DESC";

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return $registros;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $fechaEntrega = '';
        if ($row['fecha_entrega_premio'] instanceof DateTime) {
            $fechaEntrega = $row['fecha_entrega_premio']->format('Y-m-d H:i:s');
        }

        $fechaCanje = '';
        if ($row['fecha_canje'] instanceof DateTime) {
            $fechaCanje = $row['fecha_canje']->format('Y-m-d H:i:s');
        }

        $registros[] = [
            'folio_canje' => $row['folio_canje'] ?: '',
            'id_empleado' => (int)$row['id_empleado'],
            'nombre_empleado' => $row['nombre_empleado'] ?: '',
            'incentivo' => $row['incentivo'] ?: '',
            'callcenter' => $row['nombre_callcenter'] ?: '',
            'entrega_premio_estatus' => (int)$row['entrega_premio_estatus'],
            'fecha_entrega_premio' => $fechaEntrega,
            'entregado_por' => $row['entregado_por'] !== null ? (int)$row['entregado_por'] : null,
            'ventas_usadas' => (int)$row['ventas_usadas'],
            'fecha_canje' => $fechaCanje
        ];
    }

    return $registros;
}

function actualizar_entrega_premio_por_folio($conn, $folio_canje, $nuevo_estatus, $id_admin)
{
    if (!in_array((int)$nuevo_estatus, [0, 1], true)) {
        return [
            'ok' => false,
            'fase' => 'validacion_estatus'
        ];
    }

    if ((int)$nuevo_estatus === 1) {
        $sql = "UPDATE tbl_activaciones_ventas
                SET
                    entrega_premio_estatus = 1,
                    fecha_entrega_premio = GETDATE(),
                    entregado_por = ?
                WHERE folio_canje = ?";
        $params = [(int)$id_admin, $folio_canje];
    } else {
        $sql = "UPDATE tbl_activaciones_ventas
                SET
                    entrega_premio_estatus = 0,
                    fecha_entrega_premio = NULL,
                    entregado_por = NULL
                WHERE folio_canje = ?";
        $params = [$folio_canje];
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return [
            'ok' => false,
            'fase' => 'update_entrega',
            'errores' => sqlsrv_errors()
        ];
    }

    return true;
}