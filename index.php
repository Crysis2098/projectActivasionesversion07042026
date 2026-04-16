<?php
//echo "Estoy trabajando en: " . __DIR__;
//echo '<br>';

/* SOLO PRUEBAS LOCALES
$id_empleado = 5274;
$callcenter = 1;
$id_perfil = 15;*/

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once'CONEXION_CON_UTF8.PHP';
include('detectar_plantilla.php');
include('FUNCIONES/usuarios.php');
include('FUNCIONES/roles.php');
include('FUNCIONES/helpers.php');

//echo $id_empleado;
/*
    Variables globales heredadas del sistema principal
*/
/*$id_empleado;
$callcenter;
$id_perfil;*/

/*
    Arrays de permisos desde BD
*/
$perfiles_administradores = obtener_perfiles_administradores($conn);
$perfiles_administradores_otros_recintos = obtener_perfiles_administradores_otros_recintos($conn);
$perfiles_validacion = obtener_perfiles_validacion($conn);
$perfiles_ejecutivos = obtener_perfiles_ejecutivos($conn);

/*
    Tipo de usuario dentro del módulo
*/


$tipo_usuario = obtener_tipo_usuario_activaciones(
    $id_empleado,
    $perfiles_administradores,
    $perfiles_administradores_otros_recintos,
    $perfiles_validacion,
    $perfiles_ejecutivos
);

/*
    Temporal: solo visible para ti mientras no está en producción
*/
/*if ($id_empleado != 5274 && $id_empleado != 4376 && $id_empleado !=10407) {
    include('VIEWS/LAYOUTS/header.php');
    include('VIEWS/LAYOUTS/acceso_denegado.php');
    echo '</body></html>';
    sqlsrv_close($conn);
    exit;
}*/

/*
    Vista actual
*/
$vista = isset($_GET['vista']) ? trim($_GET['vista']) : '';

if ($vista === '') {
    switch ($tipo_usuario) {
        case 'administrador_global':
        /*case 'administrador_callcenter':*/
            $vista = 'crear_campana';
            break;

        case 'validador':
        case 'administrador_callcenter':
            $vista = 'validacion';
            break;

        case 'ejecutivo':
            $vista = 'activaciones_disponibles';
            break;

        default:
            $vista = 'sin_acceso';
            break;
    }
}


$menu_botones = obtener_menu_por_tipo($tipo_usuario);

include('VIEWS/LAYOUTS/header.php');

if ($tipo_usuario === 'sin_acceso') {
   include('VIEWS/LAYOUTS/acceso_denegado.php');
   echo '</body></html>';
   exit;
}
/*
   🔒 BLOQUEO POR URL MANIPULADA
*/
if (!tiene_permiso_vista($tipo_usuario, $vista)) {
   include('VIEWS/LAYOUTS/acceso_denegado.php');
   echo '</body></html>';
   exit;
}

if ($tipo_usuario === 'sin_acceso') {
    include('VIEWS/LAYOUTS/acceso_denegado.php');
    echo '</body></html>';
    sqlsrv_close($conn);
    exit;
}

include('VIEWS/LAYOUTS/menu_principal.php');

echo '<br><br>';

switch ($vista) {

case 'crear_campana':
    include('MODELS/campanas_model.php');

    $programadores = obtener_programadores_activos($conn);
    $indicadores = obtener_indicadores_activos($conn);
    $callcenters_disponibles = obtener_callcenters_disponibles($conn);

    $es_admin_global = ($tipo_usuario === 'administrador_global');
    $campanas_del_dia = obtener_campanas_del_dia($conn, $callcenter, $es_admin_global);

    include('VIEWS/CAMPANAS/crear.php');
    break;

case 'consultar_campanas':
   include('MODELS/campanas_model.php');
   $programadores = obtener_programadores_activos($conn);
   $callcenters_disponibles = obtener_callcenters_disponibles($conn);
   include('VIEWS/CAMPANAS/consultar.php');
   break;

case 'validacion':
    require_once('MODELS/validacion_model.php');

    $ventas_validacion = obtener_ventas_validacion_del_dia($conn, $tipo_usuario, $callcenter);

    include('VIEWS/VALIDACION/validar_ventas.php');
    break;

    case 'crear_programador':
        echo '<div style="padding:30px;font-family:Poppins,sans-serif;">Vista de crear programador en construcción.</div>';
        break;

    case 'crear_indicador':
        echo '<div style="padding:30px;font-family:Poppins,sans-serif;">Vista de crear indicador en construcción.</div>';
        break;

    case 'activaciones_disponibles':
    include('MODELS/ventas_model.php');

    $campanas_disponibles = obtener_campanas_disponibles_ejecutivo($conn, $callcenter, $id_empleado);

    include('VIEWS/EJECUTIVO/activaciones_disponibles.php');
    break;

case 'captura_ventas':
    include('MODELS/ventas_model.php');

    $id_campana = isset($_GET['id_campana']) ? (int)$_GET['id_campana'] : 0;
    $modalidad = isset($_GET['modalidad']) ? trim($_GET['modalidad']) : '';

    $campana_seleccionada = obtener_campana_disponible_por_id($conn, $id_campana, $callcenter);

$texto_mostrar = 'Sin tipo indicador:';
if ($campana_seleccionada) {
   $texto_mostrar = obtener_texto_rgus_por_indicador($campana_seleccionada['id_indicador']);
}    

    $resumen_ventas_hoy = [
        'realizadas' => 0,
        'aceptadas' => 0,
        'rechazadas' => 0,
        'pendientes' => 0,
        'canjeadas' => 0
    ];

    $ventas_hoy = [];
    $ventas_aprobadas_disponibles = 0;
    $incentivos_canjeables = [];

    if ($campana_seleccionada) {
        $resumen_ventas_hoy = obtener_resumen_ventas_hoy_ejecutivo($conn, $id_empleado, $id_campana);
        $ventas_hoy = obtener_ventas_hoy_ejecutivo($conn, $id_empleado, $id_campana);
        $ventas_aprobadas_disponibles = contar_ventas_aprobadas_disponibles_para_canje($conn, $id_empleado, $id_campana);
        $incentivos_canjeables = obtener_incentivos_canjeables_por_campana($conn, $id_campana);
    }

    include('VIEWS/EJECUTIVO/captura_ventas.php');
    break;

case 'entrega_premios':
    require_once('MODELS/entrega_premios_model.php');

    $filtros_entrega = [
        'fecha_inicio' => isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : date('Y-m-d'),
        'fecha_fin' => isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : date('Y-m-d'),
        'estatus_entrega' => isset($_GET['estatus_entrega']) ? trim($_GET['estatus_entrega']) : 'todos'
    ];

    $canjes_entrega = obtener_canjes_para_entrega($conn, $tipo_usuario, $callcenter, $filtros_entrega);

    include('VIEWS/CAMPANAS/entrega_premios.php');
    break;

    case 'metricas_validacion':
    require_once('MODELS/metricas_validacion_model.php');

    $filtros_metricas = [
        'fecha_inicio' => isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : date('Y-m-d'),
        'fecha_fin' => isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : date('Y-m-d')
    ];

    $metricas_validadores = obtener_metricas_validadores($conn, $filtros_metricas);

    include('VIEWS/VALIDACION/metricas_validacion.php');
    break;

    default:
        echo '<div style="padding:30px;font-family:Poppins,sans-serif;">La vista solicitada no existe.</div>';
        break;
}

echo '</body></html>';

//sqlsrv_close($conn);
?>