<?php

function obtener_tipo_usuario_activaciones(
    $id_empleado,
    $perfiles_administradores,
    $perfiles_administradores_otros_recintos,
    $perfiles_validacion,
    $perfiles_ejecutivos
) {
    if (in_array((int)$id_empleado, $perfiles_administradores, true)) {
        return 'administrador_global';
    }

    if (in_array((int)$id_empleado, $perfiles_administradores_otros_recintos, true)) {
        return 'administrador_callcenter';
    }

    if (in_array((int)$id_empleado, $perfiles_validacion, true)) {
        return 'validador';
    }

    if (in_array((int)$id_empleado, $perfiles_ejecutivos, true)) {
        return 'ejecutivo';
    }

    return 'sin_acceso';
}

function obtener_menu_por_tipo($tipo_usuario)
{
    $base = '?check=6641311242';

    switch ($tipo_usuario) {
        case 'administrador_global':
            return [
                ['label' => 'Crear Campaña',      'url' => $base . '&vista=crear_campana'],
                ['label' => 'Validación',         'url' => $base . '&vista=validacion'],
                ['label' => 'Crear Programador',  'url' => $base . '&vista=crear_programador'],
                ['label' => 'Crear Indicador',    'url' => $base . '&vista=crear_indicador'],
                ['label' => 'Consultar Campañas', 'url' => $base . '&vista=consultar_campanas'],
                ['label' => 'Checklist Premios',  'url' => $base . '&vista=entrega_premios'],
                ['label' => 'Metricas BO',        'url' => $base . '&vista=metricas_validacion']
            ];

        case 'administrador_callcenter':
            return [
                /*['label' => 'Crear Campaña',      'url' => $base . '&vista=crear_campana'],*/
                ['label' => 'Validación',         'url' => $base . '&vista=validacion'],
                /*['label' => 'Crear Programador',  'url' => $base . '&vista=crear_programador'],
                ['label' => 'Crear Indicador',    'url' => $base . '&vista=crear_indicador'],*/
                ['label' => 'Consultar Campañas', 'url' => $base . '&vista=consultar_campanas'],
            ];

        case 'validador':
            return [
                ['label' => 'Consultar Campañas', 'url' => $base . '&vista=consultar_campanas'],
                ['label' => 'Validación',         'url' => $base . '&vista=validacion'],
            ];

        case 'ejecutivo':
            return [
                ['label' => 'Activaciones Disponibles', 'url' => $base . '&vista=activaciones_disponibles'],
                ['label' => 'Captura de Ventas',        'url' => $base . '&vista=captura_ventas'],
                /*['label' => 'Historial de Premios',     'url' => $base . '&vista=historial_premios'],*/
            ];

        default:
            return [];
    }
}

function tiene_permiso_vista($tipo_usuario, $vista)
{
   $permisos = [
       'administrador_global' => [
           'crear_campana',
           'consultar_campanas',
           'validacion',
           'crear_programador',
           'crear_indicador',
           'entrega_premios',
           'metricas_validacion'
       ],
       'administrador_callcenter' => [
           /*'crear_campana',*/
           'consultar_campanas',
           'validacion'
       ],
       'validador' => [
           'validacion',
           'consultar_campanas'
       ],
       'ejecutivo' => [
           'activaciones_disponibles',
           'captura_ventas',
           'historial_premios'
       ]
   ];
   if ($vista === 'sin_acceso') {
       return true;
   }
   if (!isset($permisos[$tipo_usuario])) {
       return false;
   }
   return in_array($vista, $permisos[$tipo_usuario], true);
}