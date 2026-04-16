<?php
function obtener_texto_rgus_por_indicador($id_indicador)
{
   switch ((int)$id_indicador) {
       case 3:
           return 'Suscriptor Nuevo:';
       case 4:
           return 'Nueva Línea:';
       case 6:
           return 'Nuevo complemento:';
       default:
           return 'Sin indicador seleccionado:';
   }
}