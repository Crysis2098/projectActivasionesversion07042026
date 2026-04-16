document.addEventListener('DOMContentLoaded', function () {
    const toastContainer = document.getElementById('toast_container');

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

    async function asignarVenta(idVenta, idValidador) {
        const formData = new FormData();
        formData.append('id_venta', idVenta);
        formData.append('id_validador', idValidador);

        const response = await fetch('ACTIVACIONES_SL/AJAX/validacion/asignar.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('No se pudo completar la asignación.');
        }

        return response.json();
    }

    async function guardarValidacion(form) {
        const formData = new FormData(form);

        const response = await fetch('ACTIVACIONES_SL/AJAX/validacion/guardar.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('No se pudo guardar la validación.');
        }

        return response.json();
    }

    document.addEventListener('click', async function (event) {
        const btnValidar = event.target.closest('.btn-validar-venta');
        if (!btnValidar) {
            return;
        }

        const idVenta = btnValidar.getAttribute('data-id-venta');
        const filaFormulario = document.getElementById('fila-validacion-' + idVenta);
        const idAsignado = parseInt(btnValidar.getAttribute('data-id-asignado') || '0', 10);
        const formulario = filaFormulario ? filaFormulario.querySelector('form') : null;
        const idValidadorActual = formulario ? parseInt(formulario.querySelector('input[name="id_validador_actual"]').value || '0', 10) : 0;

        if (!filaFormulario || !formulario || !idValidadorActual) {
            mostrarToast('No se pudo preparar el formulario de validación.', 'error', 'Error');
            return;
        }

        if (filaFormulario.style.display === 'none' || filaFormulario.style.display === '') {
            if (!idAsignado) {
                const confirmar = window.confirm('¿Seguro que deseas asignarte esta venta? Una vez seleccionada ya no podrá reasignarse.');
                if (!confirmar) {
                    return;
                }

                try {
                    const data = await asignarVenta(idVenta, idValidadorActual);

                    if (!data.ok) {
                        mostrarToast(data.mensaje || 'No fue posible asignarte esta venta.', 'error', 'Asignación fallida');
                        return;
                    }

                    const hiddenAsignado = formulario.querySelector('input[name="id_asignado_a"]');
                    if (hiddenAsignado) {
                        hiddenAsignado.value = String(idValidadorActual);
                    }

                    btnValidar.setAttribute('data-id-asignado', String(idValidadorActual));
                    mostrarToast(data.mensaje || 'La venta se te asignó correctamente.', 'success', 'Asignada');
                } catch (error) {
                    mostrarToast(error.message || 'Ocurrió un error inesperado.', 'error', 'Error del sistema');
                    return;
                }
            }

            document.querySelectorAll('.fila-formulario-validacion').forEach(function (fila) {
                if (fila !== filaFormulario) {
                    fila.style.display = 'none';
                }
            });

            filaFormulario.style.display = 'table-row';
        } else {
            filaFormulario.style.display = 'none';
        }
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.form-validacion-venta');
        if (!form) {
            return;
        }

        event.preventDefault();

        const estatus = form.querySelector('select[name="estatus"]');
        const idAsignado = form.querySelector('input[name="id_asignado_a"]');

        if (!estatus || !estatus.value) {
            mostrarToast('Debes seleccionar un estado.', 'error', 'Formulario incompleto');
            return;
        }

        if (!idAsignado || !idAsignado.value) {
            mostrarToast('La venta no está asignada a un validador.', 'error', 'Asignación requerida');
            return;
        }

        try {
            const data = await guardarValidacion(form);

            if (!data.ok) {
                mostrarToast(data.mensaje || 'No fue posible guardar la validación.', 'error', 'Error al guardar');
                return;
            }

            mostrarToast(data.mensaje || 'La validación se guardó correctamente.', 'success', 'Validación guardada');

            setTimeout(function () {
                window.location.reload();
            }, 1000);
        } catch (error) {
            mostrarToast(error.message || 'Ocurrió un error inesperado.', 'error', 'Error del sistema');
        }
    });
});