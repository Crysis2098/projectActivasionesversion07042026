<?php
function consultar_registros_campanas($conn, array $filtros, $tipo_usuario, $callcenter_usuario)
{
   $registros = [];
   $sql = "SELECT
v.id,
v.id_empleado,
e.usuario_telco as telco,
v.callcenter,
cc.callcenter AS nombre_callcenter,
e.sub_perfil as idsubarea, s.sub_perfil as subarea,
v.campania,
v.modalidad,
v.cuenta,
v.orden_servicio,
v.estatus,
v.incentivo,
v.folio_canje,
v.rgus,
CONVERT(varchar(10), v.fecha_subida, 120) AS fecha_captura,
CONVERT(varchar(5), v.fecha_subida, 108) AS horario_captura,
p.id AS id_programador,
               p.programadores,
               e.nombre AS nombre_ejecutivo
           FROM tbl_activaciones_ventas v
           LEFT JOIN tbl_callcenter cc
               ON cc.id_callcenter = v.callcenter
           LEFT JOIN tbl_activaciones_sl_campanas c
               ON c.id_campana = v.id_activacion
           LEFT JOIN tbl_activaciones_programadores p
               ON p.id = c.id_programador
           LEFT JOIN tbl_empleados e
               ON e.id_empleado = v.id_empleado
            LEFT JOIN tbl_perfil_sub as s ON e.sub_perfil = s.id
           WHERE 1 = 1";
   $params = [];
   if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
       $sql .= " AND CONVERT(date, v.fecha_subida) BETWEEN ? AND ?";
       $params[] = $filtros['fecha_inicio'];
       $params[] = $filtros['fecha_fin'];
   }
   if ($tipo_usuario === 'administrador_callcenter') {
       $sql .= " AND v.callcenter = ?";
       $params[] = (int)$callcenter_usuario;
   } else {
       if (!empty($filtros['callcenter']) && $filtros['callcenter'] !== 'todos') {
           $sql .= " AND v.callcenter = ?";
           $params[] = (int)$filtros['callcenter'];
       }
   }
   if (!empty($filtros['programador']) && $filtros['programador'] !== 'todos') {
       $sql .= " AND p.id = ?";
       $params[] = (int)$filtros['programador'];
   }
   if (!empty($filtros['estatus']) && $filtros['estatus'] !== 'todos') {
       $sql .= " AND v.estatus = ?";
       $params[] = (int)$filtros['estatus'];
   }
   if (!empty($filtros['revisar_por']) && $filtros['revisar_por'] === 'ganadores') {
       $sql .= " AND v.estatus = 4";
   }
   $sql .= " ORDER BY v.fecha_subida DESC, v.id DESC";
   $stmt = sqlsrv_query($conn, $sql, $params);
   if ($stmt === false) {
       return $registros;
   }
   while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
       $jerarquia = obtenerJerarquia((int)$row['id_empleado'], $conn);
       $registros[] = [
           'id' => (int)$row['id'],
           'callcenter' => $row['nombre_callcenter'] ?: '',
           'gerente' => isset($jerarquia['gerente']['nombre']) ? $jerarquia['gerente']['nombre'] : '',
           'jefe' => isset($jerarquia['jefe']['nombre']) ? $jerarquia['jefe']['nombre'] : '',
           'supervisor' => isset($jerarquia['supervisor']['nombre']) ? $jerarquia['supervisor']['nombre'] : '',
           'ejecutivo' => $row['nombre_ejecutivo'] ?: '',
           'telco' => $row['telco'] ?: $row['campania'],
           'subarea' => $row['subarea'] ?: $row['campania'],
           'fecha' => $row['fecha_captura'] ?: '',
           'horario_captura' => $row['horario_captura'] ?: '',
           'cuenta' => $row['cuenta'] ?: '',
           'orden_servicio' => $row['orden_servicio'] ?: '',
           'valor' => $row['rgus'] !== null ? (string)$row['rgus'] : '',
           'incentivo' => $row['incentivo'] ?: '',
           'modalidad' => $row['modalidad'] ?: '',
           'folio_canje' => $row['folio_canje'] ?: '',
           'estatus' => (int)$row['estatus']
       ];
   }
   return $registros;
}