<?php
//include("CONEXION.php");


function obtenerJerarquia($idEjecutivo, $conn) {
    $jerarquia = [];

    // Paso 1: obtener datos del ejecutivo
    $sql = "SELECT id_empleado, nombre, id_perfil 
            FROM tbl_empleados 
            WHERE id_empleado = ?";
    $stmt = sqlsrv_query($conn, $sql, [$idEjecutivo]);
    if ($stmt === false) die(print_r(sqlsrv_errors(), true));
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$row) return $jerarquia;

    $jerarquia['ejecutivo'] = [
        'id' => $row['id_empleado'],
        'nombre' => $row['nombre'],
        'id_perfil' => $row['id_perfil']
    ];

    // Paso 2: buscar superior inmediato en tbl_empleados_super
    $idSuperior = obtenerSuperior($idEjecutivo, $conn);

    while ($idSuperior) {
        // Obtener datos del superior
        $sqlSup = "SELECT id_empleado, nombre, id_perfil 
                   FROM tbl_empleados 
                   WHERE id_empleado = ?";
        $stmtSup = sqlsrv_query($conn, $sqlSup, [$idSuperior]);
        if ($stmtSup === false) die(print_r(sqlsrv_errors(), true));
        $supRow = sqlsrv_fetch_array($stmtSup, SQLSRV_FETCH_ASSOC);
        if (!$supRow) break;

        // Evaluar perfil
        if ($supRow['id_perfil'] == 2) {
            $jerarquia['supervisor'] = [
                'id' => $supRow['id_empleado'],
                'nombre' => $supRow['nombre'],
                'id_perfil' => $supRow['id_perfil']
            ];
            $idSuperior = obtenerSuperior($supRow['id_empleado'], $conn); // buscar jefe
        } elseif ($supRow['id_perfil'] == 3) {
            $jerarquia['jefe'] = [
                'id' => $supRow['id_empleado'],
                'nombre' => $supRow['nombre'],
                'id_perfil' => $supRow['id_perfil']
            ];
            $idSuperior = obtenerSuperior($supRow['id_empleado'], $conn); // buscar gerente
        } elseif ($supRow['id_perfil'] == 7 || $supRow['id_perfil'] == 8) {
            $jerarquia['gerente'] = [
                'id' => $supRow['id_empleado'],
                'nombre' => $supRow['nombre'],
                'id_perfil' => $supRow['id_perfil']
            ];
            break; // ya llegamos al gerente
        } else {
            break; // jerarquía rota
        }
    }

    return $jerarquia;
}

/**
 * Función auxiliar para obtener el superior inmediato desde tbl_empleados_super
 */
function obtenerSuperior($idEmpleado, $conn) {
    $sql = "SELECT id_super 
            FROM tbl_empleados_super 
            WHERE id_empleado = ?";
    $stmt = sqlsrv_query($conn, $sql, [$idEmpleado]);
    if ($stmt === false) die(print_r(sqlsrv_errors(), true));
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ? $row['id_super'] : null;
}
