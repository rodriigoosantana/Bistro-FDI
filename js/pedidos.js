/**
 * Confirma la cancelacion de un pedido.
 * @returns {boolean} True si el usuario confirma, false en caso contrario.
 */
function confirmarCancelacionPedido() {
    return confirm('¿Estas seguro de que quieres cancelar este pedido?');
}

/**
 * Muestra u oculta el campo de tarjeta segun el metodo de pago seleccionado.
 * @param {string} metodo El metodo seleccionado ('tarjeta' o 'camarero').
 */
function alternarMetodoPago(metodo) {
    const campoTarjeta = document.getElementById('campo_tarjeta');
    if (campoTarjeta) {
        campoTarjeta.style.display = (metodo === 'tarjeta') ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const botonMiCarrito = document.getElementById('btn-mi-carrito');

    const actualizarTextoBotonCarrito = function (totalUnidades) {
        if (!botonMiCarrito || typeof totalUnidades !== 'number') {
            return;
        }
        botonMiCarrito.dataset.totalUnidades = String(totalUnidades);
        botonMiCarrito.textContent = 'Ir a mi carrito (' + totalUnidades + ')';
    };

    document.querySelectorAll('.form-add-cart').forEach(function (formulario) {
        if (formulario.dataset.ajaxBound === '1') {
            return;
        }
        formulario.dataset.ajaxBound = '1';

        const inputCantidad = formulario.querySelector('input[name="cantidad"]');
        const botonAdd = formulario.querySelector('.btn-add-cart');
        const mostrarControlesAdd = function (cantidad) {
            if (!inputCantidad || !botonAdd) {
                return;
            }
            if (cantidad > 0) {
                botonAdd.classList.add('is-hidden');
                inputCantidad.classList.remove('is-hidden');
            } else {
                botonAdd.classList.remove('is-hidden');
                inputCantidad.classList.add('is-hidden');
            }
        };

        if (!inputCantidad) {
            return;
        }

        const enviarCantidad = function () {
            const formData = new FormData(formulario);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (respuesta) {
                    if (!respuesta.ok) {
                        throw new Error('No se pudo actualizar la cantidad');
                    }
                    return respuesta.json();
                })
                .then(function (data) {
                    if (!data.ok) {
                        throw new Error(data.mensaje || 'No se pudo actualizar la cantidad');
                    }

                    const cantidad = Number(data.cantidad);
                    inputCantidad.value = String(cantidad > 0 ? cantidad : 1);
                    mostrarControlesAdd(cantidad);
                    actualizarTextoBotonCarrito(Number(data.totalUnidades));
                })
                .catch(function () {
                    formulario.submit();
                });
        };

        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            inputCantidad.value = '1';
            enviarCantidad();
        });

        inputCantidad.addEventListener('change', function () {
            enviarCantidad();
        });

        mostrarControlesAdd(Number(inputCantidad.value));
    });

    const enlazarFormulariosCarrito = function () {
        const formulariosCarrito = document.querySelectorAll('.form-update-cart');
        formulariosCarrito.forEach(function (formulario) {
            if (formulario.dataset.ajaxBound === '1') {
                return;
            }
            formulario.dataset.ajaxBound = '1';

            const inputCantidad = formulario.querySelector('input[name="cantidad"]');
            if (!inputCantidad) {
                return;
            }

            formulario.addEventListener('submit', function (evento) {
                evento.preventDefault();
            });

            inputCantidad.addEventListener('change', function () {
                const formData = new FormData(formulario);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (respuesta) {
                        if (!respuesta.ok) {
                            throw new Error('No se pudo actualizar el carrito');
                        }
                        return respuesta.text();
                    })
                    .then(function (html) {
                        const parser = new DOMParser();
                        const documento = parser.parseFromString(html, 'text/html');
                        const nuevoResumen = documento.querySelector('.cart-summary');
                        const resumenActual = document.querySelector('.cart-summary');

                        if (!nuevoResumen || !resumenActual) {
                            throw new Error('No se pudo refrescar el carrito');
                        }

                        resumenActual.replaceWith(nuevoResumen);
                        enlazarFormulariosCarrito();
                    })
                    .catch(function () {
                        formulario.submit();
                    });
            });
        });
    };

    enlazarFormulariosCarrito();
});
