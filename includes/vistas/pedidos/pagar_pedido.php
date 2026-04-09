<?php


use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\PagoService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Oferta\OfertaService;

require_once dirname(__DIR__, 3) . '/includes/config.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header('Location: ' . RUTA_VISTAS . '/login.php');
  exit();
}

// Se requiere id para ver un pedido
if (!isset($_GET['id'])) {
  header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
  exit();
}

$idPedido = intval($_GET['id']);
$pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
if (!$pedidoDesglosado) {
  header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
  exit();
}

// Verificar que el pedido pertenece al usuario o es gerente/camarero
$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);
$esCocinero = ($_SESSION['rolId'] === Usuario::ROL_COCINERO);
$esDueno = (intval($_SESSION['userId']) === $pedidoDesglosado->getClienteId());

if (!$esGerente && !$esCamarero && !esCocinero && !$esDueno) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

// Solo se puede pagar si esta en estado 'recibido'
if ($pedidoDesglosado->getEstado() !== Estado::Recibido) {
  header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
  exit();
}

$mensajeError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metodo_pago'])) {
  $metodoPago = $_POST['metodo_pago'];
  $pagoValido = false;

    if ($metodoPago === 'tarjeta') {
        $numeroTarjeta = $_POST['numero_tarjeta'] ?? '';
        $resultadoValidacion = PagoService::validarTarjeta($numeroTarjeta);

        if ($resultadoValidacion['valido']) {
            if (PedidoService::cambiarEstado($idPedido, Estado::EnPreparacion)) {
                header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
                exit();
            } else {
                $mensajeError = "Error al procesar el pago.";
            }
        } else {
            $mensajeError = $resultadoValidacion['error'];
        }
    } elseif ($metodoPago === 'camarero') {
        // El pedido se queda en Recibido, el camarero lo cobrará en persona
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
        exit();
    }
}

// Datos del pedido
$numeroPedido = htmlspecialchars($pedidoDesglosado->getNumeroPedido());
$totalPagar   = number_format($pedidoDesglosado->getTotalConDescuento(), 2, ',', '.');

$descuento  = $pedidoDesglosado->getDescuento();
$descuentoF = number_format($descuento, 2, ',', '.');
$totalFinal = number_format($pedidoDesglosado->getTotalConDescuento(), 2, ',', '.');

$ofertasPedido = OfertaService::listarOfertasDePedido($pedidoDesglosado->getId());
$nombreOferta  = !empty($ofertasPedido) ? htmlspecialchars($ofertasPedido[0]->getNombre()) : null;

// Tabla de productos del pedido
$productos         = $pedidoDesglosado->getProductos();
$filasProductos    = '';

if ($productos && count($productos) > 0) {
  foreach ($productos as $prod) {
    $pNombre    = htmlspecialchars($prod->getNombre());
    $pCantidad  = (int)$prod->getCantidad();
    $esCanjeado = $prod->isBistroCoineado();
    $pPrecioValor = $esCanjeado ? 0.0 : $prod->getPrecio();
    $pPrecio    = number_format($pPrecioValor, 2, ',', '.');
    $pSubtotalValor = $esCanjeado ? 0.0 : ($prod->getPrecio() * $pCantidad);
    $pSubtotal  = number_format($pSubtotalValor, 2, ',', '.');
    $badgeCanje = $esCanjeado ? ' <small class="marca-recompensa">(Recompensa)</small>' : '';

    $filasProductos .= <<<FILA
            <tr>
                <td>{$pNombre}{$badgeCanje}</td>
                <td class="text-center">{$pCantidad}</td>
                <td class="text-right">{$pPrecio} €</td>
                <td class="text-right">{$pSubtotal} €</td>
            </tr>
FILA;
  }
}

$tablaProductos = <<<TABLA
<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-right">Precio ud.</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        {$filasProductos}
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td class="text-right"><strong>{$totalPagar} €</strong></td>
        </tr>
    </tfoot>
</table>
TABLA;

# bloque de descuento si aplica
$htmlDescuentoPago = '';
if ($descuento > 0) {
    $htmlOferta = $nombreOferta ? " ({$nombreOferta})" : '';
    $htmlDescuentoPago = <<<DTO
    <div class="oferta-resumen-precio" style="margin-top:10px;">
        <span class="descuento">— Descuento: {$descuentoF} €{$htmlOferta}</span>
        <span class="precio-con"><strong>Total a pagar: {$totalFinal} €</strong></span>
    </div>
DTO;
}
$tablaProductos .= $htmlDescuentoPago;

$tituloPagina = "Pagar Pedido #{$numeroPedido}";
$tituloHeader = 'Finalizar Pago';

$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : "";

$opcionCamarero = (!$esCamarero && !$esCocinero) ? <<<HTML
<div class="opcion-pago">
    <input type="radio" id="pago_camarero" name="metodo_pago" value="camarero" onclick="alternarMetodoPago('camarero')">
    <label for="pago_camarero">Pagar al camarero</label>
</div>
HTML : '';

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Resumen de tu pedido</h2>
        {$htmlNotificacionError}
        
        <div class="resumen-pedido">
            {$tablaProductos}
        </div>

        <h3>Selecciona el metodo de pago</h3>
        <form id="formPago" method="POST" action="" class="form-pago">
            <div class="opcion-pago">
                <input type="radio" id="pago_tarjeta" name="metodo_pago" value="tarjeta" checked onclick="alternarMetodoPago('tarjeta')">
                <label for="pago_tarjeta">Pagar con tarjeta</label>
            </div>
            
            <div id="campo_tarjeta" class="campo-pago">
                <label for="numero_tarjeta">Numero de tarjeta:</label>
                <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="">
            </div>

            <div class="opcion-pago">
            {$opcionCamarero}
           </div>
        </form>

        <div class="botones-pago">
            <button type="submit" form="formPago" class="btn btn-nuevo">Pagar</button>
            <form method="POST" action="anadir_productos.php">
                <input type="hidden" name="pedidoId" value="{$idPedido}" />
                <input type="hidden" name="accion" value="reabrir" />
                <button type="submit" class="btn btn-volver">Volver al carrito</button>
            </form>
        </div>
    </section>
    <script src="../../js/pedidos.js"></script>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
