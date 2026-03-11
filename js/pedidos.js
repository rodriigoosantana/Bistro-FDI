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
