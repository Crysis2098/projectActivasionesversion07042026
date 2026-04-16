<?php

function obtener_metricas_validadores($conn, array $filtros = [])
{
    $registros = [];

    $fecha_inicio = isset($filtros['fecha_inicio']) ? trim((string)$filtros['fecha_inicio']) : date('Y-m-d');
    $fecha_fin = isset($filtros['fecha_fin']) ? trim((string)$filtros['fecha_fin']) : date('Y-m-d');

    $sql = "SELECT
                v.id_asignado_a AS id_validador,
                e.nombre AS nombre_validador,
                COUNT(*) AS ventas_atendidas,
                AVG(DATEDIFF(SECOND, v.fecha_validacion, v.fecha_validacion_termina) * 1.0) AS promedio_segundos
            FROM tbl_activaciones_ventas v
            INNER JOIN tbl_empleados e
                ON e.id_empleado = v.id_asignado_a
            WHERE v.id_asignado_a IS NOT NULL
              AND v.fecha_validacion IS NOT NULL
              AND v.fecha_validacion_termina IS NOT NULL
              AND CONVERT(date, v.fecha_validacion_termina) BETWEEN ? AND ?
            GROUP BY
                v.id_asignado_a,
                e.nombre
            ORDER BY
                e.nombre ASC";

    $params = [$fecha_inicio, $fecha_fin];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        return $registros;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $promedio_segundos = isset($row['promedio_segundos']) ? (float)$row['promedio_segundos'] : 0;

        $registros[] = [
            'id_validador' => (int)$row['id_validador'],
            'nombre_validador' => $row['nombre_validador'] ?: '',
            'ventas_atendidas' => (int)$row['ventas_atendidas'],
            'promedio_segundos' => $promedio_segundos,
            'promedio_formateado' => formatear_segundos_promedio_validacion($promedio_segundos)
        ];
    }

    return $registros;
}

function formatear_segundos_promedio_validacion($segundos)
{
    $segundos = (int)round((float)$segundos);

    if ($segundos < 0) {
        $segundos = 0;
    }

    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    $segundos_restantes = $segundos % 60;

    return str_pad((string)$horas, 2, '0', STR_PAD_LEFT) . ':'
        . str_pad((string)$minutos, 2, '0', STR_PAD_LEFT) . ':'
        . str_pad((string)$segundos_restantes, 2, '0', STR_PAD_LEFT);
}