document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_captura_venta');
    const respuestaFormulario = document.getElementById('respuesta_formulario');
    const toastContainer = document.getElementById('toast_container');

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function mostrarToast(mensaje, tipo = 'info', titulo = '') {
        if (!toastContainer) {
            return;
        }

        let icono = 'ℹ';
        let tituloFinal = titulo;

        if (tipo === 'success') {
            icono = '✔';
            tituloFinal = tituloFinal || 'Éxito';
        } else if (tipo === 'error') {
            icono = '✖';
            tituloFinal = tituloFinal || 'Error';
        } else {
            tituloFinal = tituloFinal || 'Información';
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

    function mostrarRespuesta(mensaje) {
        if (!respuestaFormulario) {
            return;
        }

        respuestaFormulario.innerHTML = `
            <div style="
                background:#ffebee;
                border:1px solid #c62828;
                color:#b71c1c;
                padding:12px 16px;
                border-radius:8px;
                font-family:Poppins,sans-serif;
                font-size:14px;
                margin-top:10px;
            ">
                ${escaparHtml(mensaje)}
            </div>
        `;
    }

    function limpiarRespuesta() {
        if (respuestaFormulario) {
            respuestaFormulario.innerHTML = '';
        }
    }

    function validarFormulario() {
        const cuenta = document.getElementById('cuenta');
        const ordenServicio = document.getElementById('orden_servicio');
        const rgus = document.getElementById('rgus');

        if (!cuenta || !cuenta.value.trim()) {
            mostrarRespuesta('La cuenta es obligatoria.');
            return false;
        }

        if (!ordenServicio || !ordenServicio.value.trim()) {
            mostrarRespuesta('La orden de servicio es obligatoria.');
            return false;
        }

        if (rgus && rgus.value !== '' && Number(rgus.value) < 0) {
            mostrarRespuesta('RGUs no puede ser menor a cero.');
            return false;
        }

        return true;
    }

    async function recargarCapturaDesdeFormulario(formData) {
        const check = formData.get('check') || '6641311242';
        const idActivacion = formData.get('id_activacion') || '';
        const modalidad = formData.get('modalidad') || '';

        window.location.href =
            '?check=' + encodeURIComponent(check) +
            '&vista=captura_ventas' +
            '&id_campana=' + encodeURIComponent(idActivacion) +
            '&modalidad=' + encodeURIComponent(modalidad);
    }

    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            limpiarRespuesta();

            if (!validarFormulario()) {
                return;
            }

            const formData = new FormData(form);

            try {
                const response = await fetch('ACTIVACIONES_SL/AJAX/ventas/crear.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('No se pudo completar la solicitud.');
                }

                const data = await response.json();

                if (data.ok) {
                    mostrarToast(
                        data.mensaje || 'Venta registrada correctamente.',
                        'success',
                        'Venta registrada'
                    );

                    setTimeout(function () {
                        recargarCapturaDesdeFormulario(formData);
                    }, 1200);
                } else {
                    mostrarToast(
                        data.mensaje || 'No fue posible guardar la venta.',
                        'error',
                        'No se pudo guardar'
                    );
                }
            } catch (error) {
                mostrarToast(
                    error.message || 'Ocurrió un error inesperado.',
                    'error',
                    'Error del sistema'
                );
            }
        });
    }

    document.addEventListener('click', async function (event) {
        const btnCanjear = event.target.closest('.btn-canjear-premio');
        if (!btnCanjear) {
            return;
        }

        if (!form) {
            return;
        }

        const idCampana = btnCanjear.getAttribute('data-id-campana');
        const idCampanaIncentivo = btnCanjear.getAttribute('data-id-incentivo');
        const nombreIncentivo = btnCanjear.getAttribute('data-nombre-incentivo') || 'este incentivo';
        const nombreModalidad = btnCanjear.getAttribute('data-modalidad');

        const confirmar = window.confirm('¿Deseas canjear el incentivo "' + nombreIncentivo + '"?');
        if (!confirmar) {
            return;
        }

        const baseData = new FormData(form);
        const payload = new FormData();

        payload.append('id_campana', idCampana);
        payload.append('id_campana_incentivo', idCampanaIncentivo);
        payload.append('modalidad', nombreModalidad);
        payload.append('id_empleado', baseData.get('id_empleado'));

        try {
            const response = await fetch('ACTIVACIONES_SL/AJAX/ventas/canjear.php', {
                method: 'POST',
                body: payload
            });

            if (!response.ok) {
                throw new Error('No se pudo completar el canje.');
            }

            const data = await response.json();

            if (data.ok) {
                mostrarToast(
                    (data.mensaje || 'Canje realizado correctamente.') + (data.folio_canje ? ' Folio: ' + data.folio_canje : ''),
                    'success',
                    'Canje realizado'
                );

                setTimeout(function () {
                    recargarCapturaDesdeFormulario(baseData);
                }, 1200);
            } else {
                mostrarToast(
                    data.mensaje || 'No fue posible realizar el canje.',
                    'error',
                    'Canje no realizado'
                );
            }
        } catch (error) {
            mostrarToast(
                error.message || 'Ocurrió un error inesperado al realizar el canje.',
                'error',
                'Error del sistema'
            );
        }
    });
    document.addEventListener('click', async function (event) {
       const btnReenviar = event.target.closest('.btn-reenviar-pendiente');
       if (!btnReenviar) {
           return;
       }
       const idVenta = btnReenviar.getAttribute('data-id-venta');
       const idEmpleado = btnReenviar.getAttribute('data-id-empleado');
       const idActivacion = btnReenviar.getAttribute('data-id-activacion');
       const confirmar = window.confirm('¿Deseas reenviar esta venta rechazada a pendientes para que vuelva a validarse?');
       if (!confirmar) {
           return;
       }
       const payload = new FormData();
       payload.append('id_venta', idVenta);
       payload.append('id_empleado', idEmpleado);
       payload.append('id_activacion', idActivacion);
       try {
           const response = await fetch('ACTIVACIONES_SL/AJAX/ventas/reenviar_pendiente.php', {
               method: 'POST',
               body: payload
           });
           if (!response.ok) {
               throw new Error('No se pudo completar la solicitud.');
           }
           const data = await response.json();
           if (data.ok) {
               mostrarToast(
                   data.mensaje || 'La venta volvió a pendientes correctamente.',
                   'success',
                   'Venta reenviada'
               );
               setTimeout(function () {
                   window.location.reload();
               }, 1200);
           } else {
               mostrarToast(
                   data.mensaje || 'No fue posible reenviar la venta.',
                   'error',
                   'Error al reenviar'
               );
           }
       } catch (error) {
           mostrarToast(
               error.message || 'Ocurrió un error inesperado.',
               'error',
               'Error del sistema'
           );
       }
   });
});

function validateInput(input) {
    // Lógica para Orden de Servicio
    if (input.id === "orden_servicio") {
        if (!input.value.match(/^1-\d{0,12}$/)) {
            input.value = "1-" + (input.value.match(/\d{0,12}/) || [""])[0];
        }
    } 
    
    // Lógica para Cuenta (Solo números)
    else if (input.id === "cuenta") {
        // El replace(/[^0-9]/g, '') elimina todo lo que NO sea un número
        input.value = input.value.replace(/[^0-9]/g, '');
    }
}