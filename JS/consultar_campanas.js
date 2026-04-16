document.addEventListener('DOMContentLoaded', function () {
   const form = document.getElementById('form_filtros_consulta');
   const tbody = document.getElementById('tabla_consulta_body');
   const toastContainer = document.getElementById('toast_container');
   const btnExportar = document.getElementById('btn_exportar_consulta');
   if (!form || !tbody) {
       return;
   }
   function escaparHtml(texto) {
       const div = document.createElement('div');
       div.textContent = texto ?? '';
       return div.innerHTML;
   }
   function mostrarToast(mensaje, tipo = 'info', titulo = '') {
       if (!toastContainer) {
           return;
       }
       let icono = 'ℹ';
       let tituloFinal = titulo || 'Información';
       if (tipo === 'success') {
           icono = '✔';
           tituloFinal = titulo || 'Éxito';
       } else if (tipo === 'error') {
           icono = '✖';
           tituloFinal = titulo || 'Error';
       }
       const toast = document.createElement('div');
       toast.className = `toast toast-${tipo}`;
       toast.innerHTML = `
<div class="toast-icon">${icono}</div>
<div class="toast-content">
<p class="toast-title">${escaparHtml(tituloFinal)}</p>
<p class="toast-message">${escaparHtml(mensaje)}</p>
</div>
<button type="button" class="toast-close" aria-label="Cerrar">×</button>
       `;
       toastContainer.appendChild(toast);
       const cerrarToast = () => {
           toast.classList.add('toast-hide');
           setTimeout(() => {
               if (toast.parentNode) {
                   toast.parentNode.removeChild(toast);
               }
           }, 220);
       };
       const btnClose = toast.querySelector('.toast-close');
       if (btnClose) {
           btnClose.addEventListener('click', cerrarToast);
       }
       setTimeout(cerrarToast, 4000);
   }
   function obtenerEstatus(estatus) {
       const valor = parseInt(estatus, 10);
       switch (valor) {
           case 0:
               return { texto: 'Pendiente', clase: 'estatus-pendiente' };
           case 1:
               return { texto: 'Aprobado', clase: 'estatus-aprobado' };
           case 2:
               return { texto: 'Rechazado', clase: 'estatus-rechazado' };
           case 4:
               return { texto: 'Canjeado', clase: 'estatus-canjeado' };
           default:
               return { texto: 'Desconocido', clase: 'estatus-pendiente' };
       }
   }
   function renderTabla(data) {
       if (!Array.isArray(data) || data.length === 0) {
           tbody.innerHTML = `
<tr>
<td colspan="16" class="tabla-vacia-celda">
                       No se encontraron resultados con los filtros seleccionados.
</td>
</tr>
           `;
           return;
       }
       let html = '';
       data.forEach((row) => {
           const estatus = obtenerEstatus(row.estatus);
           html += `
<tr>
<td>${escaparHtml(row.callcenter)}</td>
<td>${escaparHtml(row.gerente)}</td>
<td>${escaparHtml(row.jefe)}</td>
<td>${escaparHtml(row.supervisor)}</td>
<td>${escaparHtml(row.ejecutivo)}</td>
<td>${escaparHtml(row.telco)}</td>
<td>${escaparHtml(row.subarea)}</td>
<td>${escaparHtml(row.fecha)}</td>
<td>${escaparHtml(row.horario_captura)}</td>
<td>${escaparHtml(row.cuenta)}</td>
<td>${escaparHtml(row.orden_servicio)}</td>
<td>${escaparHtml(row.valor)}</td>
<td>${escaparHtml(row.incentivo)}</td>
<td>${escaparHtml(row.modalidad)}</td>
<td>${escaparHtml(row.folio_canje)}</td>
<td>
<span class="estatus-badge ${estatus.clase}">
                           ${escaparHtml(estatus.texto)}
</span>
</td>
</tr>
           `;
       });
       tbody.innerHTML = html;
   }
   form.addEventListener('submit', async function (event) {
       event.preventDefault();
       const fechaInicio = document.getElementById('fecha_inicio');
       const fechaFin = document.getElementById('fecha_fin');
       if (!fechaInicio.value || !fechaFin.value) {
           mostrarToast('Debes seleccionar fecha inicio y fecha fin.', 'error', 'Filtros incompletos');
           return;
       }
       if (fechaInicio.value > fechaFin.value) {
           mostrarToast('La fecha inicio no puede ser mayor a la fecha fin.', 'error', 'Rango inválido');
           return;
       }
       const formData = new FormData(form);
       try {
           const response = await fetch('ACTIVACIONES_SL/AJAX/campanas/consultar.php', {
               method: 'POST',
               body: formData
           });
           if (!response.ok) {
               throw new Error('No se pudo completar la consulta.');
           }
           const data = await response.json();
           if (data.ok) {
               renderTabla(data.data || []);
           } else {
               mostrarToast(data.mensaje || 'No fue posible consultar.', 'error', 'Consulta fallida');
           }
       } catch (error) {
           mostrarToast(error.message || 'Ocurrió un error inesperado.', 'error', 'Error del sistema');
       }
   });
   if (btnExportar) {
       btnExportar.addEventListener('click', function () {
           mostrarToast('La exportación se puede conectar después de terminar la vista.', 'info', 'Pendiente');
       });
   }
});