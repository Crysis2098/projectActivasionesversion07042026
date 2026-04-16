document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_crear_campana');
    const contenedorIncentivos = document.getElementById('contenedor_incentivos');
    const btnAgregarIncentivo = document.getElementById('btn_agregar_incentivo');
    const respuestaFormulario = document.getElementById('respuesta_formulario');
    const btnGuardarCampana = document.getElementById('btn_guardar_campana');
    const toastContainer = document.getElementById('toast_container');

    if (!form || !contenedorIncentivos || !btnAgregarIncentivo) {
        return;
    }

    let contadorFilas = contenedorIncentivos.querySelectorAll('.fila_incentivo').length;

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

    function mostrarRespuesta(mensaje, tipo) {
        if (!respuestaFormulario) {
            return;
        }

        const esExito = tipo === 'success';
        const background = esExito ? '#e8f5e9' : '#ffebee';
        const border = esExito ? '#2e7d32' : '#c62828';
        const color = esExito ? '#1b5e20' : '#b71c1c';

        respuestaFormulario.innerHTML = `
            <div style="
                background:${background};
                border:1px solid ${border};
                color:${color};
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

    function actualizarEstadoBotonesEliminar() {
        const filas = contenedorIncentivos.querySelectorAll('.fila_incentivo');
        const deshabilitar = filas.length === 1;

        filas.forEach((fila) => {
            const btnEliminar = fila.querySelector('.btn_eliminar_incentivo');
            if (btnEliminar) {
                btnEliminar.disabled = deshabilitar;
            }
        });
    }

    function crearFilaIncentivo(indice) {
        const fila = document.createElement('div');
        fila.className = 'fila_incentivo';
        fila.setAttribute('data-index', String(indice));

        fila.innerHTML = `
            <div class="campo_incentivo">
                <label for="nombre_incentivo_${indice}">Incentivo</label>
                <input
                    type="text"
                    name="nombre_incentivo[]"
                    id="nombre_incentivo_${indice}"
                    class="caja-inputs"
                    placeholder="Nombre del incentivo"
                    required
                >
            </div>

            <div class="campo_incentivo">
                <label for="cantidad_solicitada_${indice}">Cantidad solicitada</label>
                <input
                    type="number"
                    name="cantidad_solicitada[]"
                    id="cantidad_solicitada_${indice}"
                    class="caja-inputs"
                    min="0"
                    step="1"
                    placeholder="0"
                    required
                >
            </div>

            <div class="campo_incentivo">
                <label for="stock_${indice}">Stock</label>
                <input
                    type="number"
                    name="stock[]"
                    id="stock_${indice}"
                    class="caja-inputs"
                    min="0"
                    step="1"
                    placeholder="0"
                    required
                >
            </div>

            <div class="campo_incentivo">
                <label for="imagen_${indice}">Imagen</label>
                <input
                    type="file"
                    name="imagen[]"
                    id="imagen_${indice}"
                    class="caja-inputs"
                    accept=".jpg,.jpeg,.png,.webp"
                >
            </div>

            <div class="campo_incentivo acciones_incentivo">
                <label>&nbsp;</label>
                <button type="button" class="btn_eliminar_incentivo">Eliminar</button>
            </div>
        `;

        return fila;
    }

    function agregarFilaIncentivo() {
        contadorFilas += 1;
        const nuevaFila = crearFilaIncentivo(contadorFilas);
        contenedorIncentivos.appendChild(nuevaFila);
        actualizarEstadoBotonesEliminar();
    }

    function eliminarFilaIncentivo(boton) {
        const fila = boton.closest('.fila_incentivo');
        if (!fila) {
            return;
        }

        const totalFilas = contenedorIncentivos.querySelectorAll('.fila_incentivo').length;
        if (totalFilas <= 1) {
            return;
        }

        fila.remove();
        actualizarEstadoBotonesEliminar();
    }

    function validarFormulario() {
        const incentivoPara = document.getElementById('incentivo_para');
        const idIndicador = document.getElementById('id_indicador');
        const idProgramador = document.getElementById('id_programador');
        const fecha = document.getElementById('fecha');
        const horaInicio = document.getElementById('hora_inicio');
        const horaFin = document.getElementById('hora_fin');
        const callcenterSelect = document.getElementById('callcenter_select');
        const callcenterHidden = document.getElementById('callcenter_hidden');

        if (callcenterSelect) {
            if (!callcenterSelect.value) {
                mostrarRespuesta('Selecciona un callcenter.', 'error');
                return false;
            }

            if (callcenterHidden) {
                callcenterHidden.value = callcenterSelect.value;
            }
        }

        if (!incentivoPara || !incentivoPara.value) {
            mostrarRespuesta('Selecciona el campo "Incentivo para".', 'error');
            return false;
        }

        if (!idIndicador || !idIndicador.value) {
            mostrarRespuesta('Selecciona un indicador.', 'error');
            return false;
        }

        if (!idProgramador || !idProgramador.value) {
            mostrarRespuesta('Selecciona un programador.', 'error');
            return false;
        }

        if (!fecha || !fecha.value) {
            mostrarRespuesta('Selecciona una fecha.', 'error');
            return false;
        }

        if (!horaInicio || !horaInicio.value || !horaFin || !horaFin.value) {
            mostrarRespuesta('Captura la hora de inicio y la hora final.', 'error');
            return false;
        }

        if (horaInicio.value >= horaFin.value) {
            mostrarRespuesta('La hora de inicio debe ser menor que la hora final.', 'error');
            return false;
        }

        const filas = contenedorIncentivos.querySelectorAll('.fila_incentivo');
        if (filas.length === 0) {
            mostrarRespuesta('Debes agregar al menos un incentivo.', 'error');
            return false;
        }

        let hayIncentivoValido = false;

        for (let i = 0; i < filas.length; i++) {
            const fila = filas[i];
            const nombre = fila.querySelector('input[name="nombre_incentivo[]"]');
            const cantidad = fila.querySelector('input[name="cantidad_solicitada[]"]');
            const stock = fila.querySelector('input[name="stock[]"]');

            const nombreValor = nombre ? nombre.value.trim() : '';
            const cantidadValor = cantidad ? cantidad.value.trim() : '';
            const stockValor = stock ? stock.value.trim() : '';

            if (nombreValor !== '' || cantidadValor !== '' || stockValor !== '') {
                hayIncentivoValido = true;

                if (nombreValor === '') {
                    mostrarRespuesta(`El incentivo #${i + 1} debe tener nombre.`, 'error');
                    return false;
                }

                if (cantidadValor === '' || Number(cantidadValor) < 0) {
                    mostrarRespuesta(`La cantidad del incentivo #${i + 1} no es válida.`, 'error');
                    return false;
                }

                if (stockValor === '' || Number(stockValor) < 0) {
                    mostrarRespuesta(`El stock del incentivo #${i + 1} no es válido.`, 'error');
                    return false;
                }
            }
        }

        if (!hayIncentivoValido) {
            mostrarRespuesta('Debes capturar al menos un incentivo válido.', 'error');
            return false;
        }

        return true;
    }

    function deshabilitarFormulario(estado) {
        if (btnGuardarCampana) {
            btnGuardarCampana.disabled = estado;
            btnGuardarCampana.textContent = estado ? 'Guardando...' : 'Guardar campaña';
        }

        btnAgregarIncentivo.disabled = estado;

        const botonesEliminar = contenedorIncentivos.querySelectorAll('.btn_eliminar_incentivo');
        botonesEliminar.forEach((btn) => {
            btn.disabled = estado || contenedorIncentivos.querySelectorAll('.fila_incentivo').length === 1;
        });
    }

    async function enviarFormulario(event) {
        event.preventDefault();
        limpiarRespuesta();

        if (!validarFormulario()) {
            return;
        }

        const formData = new FormData(form);

        try {
            deshabilitarFormulario(true);

            const response = await fetch('ACTIVACIONES_SL/AJAX/campanas/crear.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('No se pudo completar la solicitud.');
            }

            const data = await response.json();

if (data.ok) {
    limpiarRespuesta();

    mostrarToast(
        data.mensaje || 'Campaña registrada correctamente.',
        'success',
        'Campaña creada'
    );

    setTimeout(function () {
        window.location.reload();
    }, 1200);
}else {
                mostrarToast(data.mensaje || 'No fue posible guardar la campaña.', 'error', 'No se pudo guardar');
            }
        } catch (error) {
            mostrarToast(error.message || 'Ocurrió un error inesperado.', 'error', 'Error del sistema');
        } finally {
            deshabilitarFormulario(false);
        }
    }

    btnAgregarIncentivo.addEventListener('click', function () {
        limpiarRespuesta();
        agregarFilaIncentivo();
    });

    contenedorIncentivos.addEventListener('click', function (event) {
        const botonEliminar = event.target.closest('.btn_eliminar_incentivo');
        if (!botonEliminar) {
            return;
        }

        limpiarRespuesta();
        eliminarFilaIncentivo(botonEliminar);
    });

    form.addEventListener('submit', enviarFormulario);

    form.addEventListener('reset', function () {
        limpiarRespuesta();

        setTimeout(function () {
            contenedorIncentivos.innerHTML = '';
            contadorFilas = 1;
            contenedorIncentivos.appendChild(crearFilaIncentivo(1));
            actualizarEstadoBotonesEliminar();
        }, 0);
    });

    actualizarEstadoBotonesEliminar();

    document.addEventListener('click', async function (event) {
        const btnEditar = event.target.closest('.btn-editar-campana');
        const btnCancelar = event.target.closest('.btn-cancelar-edicion');
        const btnGuardar = event.target.closest('.btn-guardar-campana');

        if (btnEditar) {
            const fila = btnEditar.closest('.tabla-campanas-row');
            if (!fila) return;

            fila.querySelectorAll('.modo-texto').forEach(el => {
                el.style.display = 'none';
            });

            fila.querySelectorAll('.modo-edicion').forEach(el => {
                el.style.display = '';
            });

            fila.querySelectorAll('.item-incentivo .modo-texto').forEach(el => {
    el.style.display = 'none';
});

fila.querySelectorAll('.item-incentivo .modo-edicion').forEach(el => {
    el.style.display = 'block';
});

            const accionesTexto = fila.querySelector('.acciones-modo-texto');
            const accionesEdicion = fila.querySelector('.acciones-modo-edicion');

            if (accionesTexto) accionesTexto.style.display = 'none';
            if (accionesEdicion) accionesEdicion.style.display = 'flex';
        }

        if (btnCancelar) {
            window.location.reload();
        }

        if (btnGuardar) {
            const fila = btnGuardar.closest('.tabla-campanas-row');
            if (!fila) return;

            const idCampana = fila.getAttribute('data-id-campana');

            const payload = new FormData();
            payload.append('id_campana', idCampana);

            fila.querySelectorAll('.campo-editable').forEach(campo => {
                payload.append(campo.getAttribute('data-field'), campo.value);
            });

            fila.querySelectorAll('.item-incentivo').forEach(incentivo => {

    const id = incentivo.getAttribute('data-id-incentivo');

    incentivo.querySelectorAll('.campo-editable-incentivo').forEach(campo => {

        payload.append(
            'incentivos[' + id + '][' + campo.getAttribute('data-field') + ']',
            campo.value
        );

    });

});

            try {
                const response = await fetch('ACTIVACIONES_SL/AJAX/campanas/editar.php', {
                    method: 'POST',
                    body: payload
                });

                if (!response.ok) {
                    throw new Error('No se pudo completar la actualización.');
                }

                const data = await response.json();

                if (data.ok) {
                    mostrarToast(
                        data.mensaje || 'Campaña actualizada correctamente.',
                        'success',
                        'Campaña actualizada'
                    );

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);
                } else {
                    mostrarToast(
                        data.mensaje || 'No fue posible actualizar la campaña.',
                        'error',
                        'Error al actualizar'
                    );
                }
            } catch (error) {
                mostrarToast(
                    error.message || 'Ocurrió un error al guardar los cambios.',
                    'error',
                    'Error del sistema'
                );
            }
        }
    });
});