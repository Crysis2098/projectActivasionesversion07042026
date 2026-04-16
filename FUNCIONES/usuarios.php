<?php



function obtener_ids_desde_consulta($conn, $sql, $params = [])

{

    $ids = [];



    $stmt = sqlsrv_query($conn, $sql, $params);



    if ($stmt === false) {

        return $ids;

    }



    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        if (isset($row['id_empleado'])) {

            $ids[] = (int)$row['id_empleado'];

        }

    }



    return $ids;

}



function obtener_perfiles_administradores($conn)

{

    $sql = "SELECT id_empleado

            FROM tbl_empleados

            WHERE id_empleado IN (546, 8804, 6046, 1602, 5274)";



    return obtener_ids_desde_consulta($conn, $sql);

}



function obtener_perfiles_administradores_otros_recintos($conn)

{

    $sql = "SELECT id_empleado

            FROM tbl_empleados

            WHERE id_empleado IN (10407, 7322, 4451, 2593)";



    return obtener_ids_desde_consulta($conn, $sql);

}



function obtener_perfiles_validacion($conn)

{

    $sql = "SELECT id_empleado

            FROM tbl_empleados

            WHERE id_perfil = 6 OR id_area = 8";



    return obtener_ids_desde_consulta($conn, $sql);

}



function obtener_perfiles_ejecutivos($conn)

{

    $sql = "SELECT id_empleado

            FROM tbl_empleados

            WHERE id_perfil = 1";



    return obtener_ids_desde_consulta($conn, $sql);

}