document.addEventListener('DOMContentLoaded', function () {
   const toastContainer = document.getElementById('toast_container');
   function escaparHtml(texto) {
       const div = document.createElement('div');
       div.textContent = texto ?? '';
       return div.innerHTML;
   }
   function mostrarToast(mensaje, tipo = 'info', titulo = '') {
       if (!toastContainer) return;
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
   document.addEventListener('change', async function (event) {
       const checkbox = event.target.closest('.check-entrega-premio');
       if (!checkbox) return;
       const folioCanje = checkbox.getAttribute('data-folio-canje');
       const idAdmin = checkbox.getAttribute('data-id-admin');
       const entregado = checkbox.checked ? 1 : 0;
       const payload = new FormData();
       payload.append('folio_canje', folioCanje);
       payload.append('id_admin', idAdmin);
       payload.append('entregado', entregado);
       try {
           const response = await fetch('ACTIVACIONES_SL/AJAX/premios/actualizar_entrega.php', {
               method: 'POST',
               body: payload
           });
           if (!response.ok) {
               throw new Error('No se pudo actualizar la entrega.');
           }
           const data = await response.json();
           if (data.ok) {
               const textoEstado = checkbox.closest('td').querySelector('.texto-entrega-estado');
               if (textoEstado) {
                   textoEstado.textContent = entregado === 1 ? 'Entregado' : 'Pendiente';
               }
               mostrarToast(
                   data.mensaje || 'Entrega actualizada correctamente.',
                   'success',
                   'Checklist actualizado'
               );
           } else {
               checkbox.checked = !checkbox.checked;
               mostrarToast(
                   data.mensaje || 'No fue posible actualizar la entrega.',
                   'error',
                   'Error'
               );
           }
       } catch (error) {
           checkbox.checked = !checkbox.checked;
           mostrarToast(
               error.message || 'Ocurrió un error inesperado.',
               'error',
               'Error del sistema'
           );
       }
   });
});