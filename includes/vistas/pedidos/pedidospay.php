<?php


use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\PagoService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Aplicacion; 

require_once dirname(__DIR__, 3) . '/includes/config.php';

if (!Aplicacion::estaLogueado()) {
  header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
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
$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esCocinero = (Aplicacion::getRolId() === Usuario::ROL_COCINERO);
$esDueno = (intval(Aplicacion::getUserId()) === $pedidoDesglosado->getClienteId());

if (!$esGerente && !$esCamarero && !$esCocinero && !$esDueno) {
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
$totalBruto = $pedidoDesglosado->getTotal(); # bruto, sin descuento
$totalBrutoF = number_format($totalBruto, 2, ',', '.');
$totalPagarF = number_format($pedidoDesglosado->getTotalConDescuento(), 2, ',', '.');

$descuento  = $pedidoDesglosado->getDescuento();
$descuentoF = number_format($descuento, 2, ',', '.');
$totalFinal = number_format($pedidoDesglosado->getTotalConDescuento(), 2, ',', '.');

$ofertasPedido = OfertaService::listarOfertasDePedido($pedidoDesglosado->getId());

# reconstruir el carrito del pedido para el desglose
$carritoPedido = [];
foreach ($pedidoDesglosado->getProductos() as $pp) {
    $carritoPedido[] = ['producto_id' => $pp->getProductoId(), 'cantidad' => $pp->getCantidad()];
}
$idsOfertas = array_map(fn($o) => $o->getId(), $ofertasPedido);
$desglose = OfertaService::calcularDescuentoMultipleDesglosado($idsOfertas, $carritoPedido);

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
            <td class="text-right"><strong>{$totalBrutoF} €</strong></td>
        </tr>
    </tfoot>
</table>
TABLA;

# bloque de descuento si aplica
$htmlDescuentoPago = '';
if (!empty($ofertasPedido)) {
    $htmlDescuentoPago .= '<div class="resumen-descuentos" style="margin-top:10px;">';
    $htmlDescuentoPago .= '<strong>Ofertas aplicadas:</strong>';
    foreach ($ofertasPedido as $of) {
        $info = $desglose['porOferta'][$of->getId()] ?? ['veces' => 0, 'descuento' => 0];
        if ($info['veces'] === 0) continue;
        $nom = htmlspecialchars($of->getNombre());
        $vecesTxt = $info['veces'] > 1 ? ' (×' . $info['veces'] . ')' : '';
        $dtoF = number_format($info['descuento'], 2, ',', '.');
        $htmlDescuentoPago .= "<div class=\"descuento\">— {$nom}{$vecesTxt}: −{$dtoF} €</div>";
    }
    $htmlDescuentoPago .= '</div>';
}

# si hubiera canje (descuento total > suma de ofertas), añadir línea
$descuentoCanje = $pedidoDesglosado->getDescuento() - $desglose['total'];
if ($descuentoCanje > 0.01) {
    $canjeF = number_format($descuentoCanje, 2, ',', '.');
    $htmlDescuentoPago .= "<div class=\"descuento\">— Canje BistroCoins: −{$canjeF} €</div>";
}
$tablaProductos .= $htmlDescuentoPago;
$tablaProductos .= "<div class=\"total-pagar\" style=\"margin-top:8px;font-weight:bold;font-size:1.1rem;\">Total a pagar: {$totalPagarF} €</div>";

$tituloPagina = "Pagar Pedido #{$numeroPedido}";
$tituloHeader = 'Finalizar Pago';

$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : "";

$opcionCamarero = <<<HTML
<label class="pago-opcion" for="pago_camarero">
    <div class="pago-opcion-body">
        <span class="pago-opcion-titulo">Pagar al camarero</span>
        <span class="pago-opcion-desc">Lo abonas en sala cuando te atienda el personal.</span>
    </div>
    <span class="pago-opcion-badge">En sala</span>
    <input type="radio" id="pago_camarero" name="metodo_pago" value="camarero" onclick="alternarMetodoPago('camarero')">
</label>
HTML;

$urlJsPedidos = RUTA_JS . '/pedidos.js';

$contenidoPrincipal = <<<EOS
    <section id="contenido" class="pago-wrapper">
        <h2>Resumen de tu pedido</h2>
        {$htmlNotificacionError}
        
        <div class="resumen-pedido">
            {$tablaProductos}
        </div>

        <div class="pago-bloque">
            <h3>Selecciona el metodo de pago</h3>
            <form id="formPago" method="POST" action="" class="form-pago">
                <div class="pago-opciones">
                    <label class="pago-opcion" for="pago_tarjeta">
                        <div class="pago-opcion-body">
                            <span class="pago-opcion-titulo">Pagar con tarjeta</span>
                            <span class="pago-opcion-desc">Completa la tarjeta y se procesa al momento.</span>
                        </div>
                        <span class="pago-opcion-badge">Online</span>
                        <input type="radio" id="pago_tarjeta" name="metodo_pago" value="tarjeta" checked onclick="alternarMetodoPago('tarjeta')">
                    </label>

                    {$opcionCamarero}
                </div>

                <div id="campo_tarjeta" class="campo-pago">
                    <label for="numero_tarjeta">Numero de tarjeta:</label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 1234 1234 1234">
                </div>
            </form>

            <div class="botones-pago">
                <button type="submit" form="formPago" class="btn btn-nuevo">Pagar</button>
                <form method="POST" action="miCarrito.php">
                    <input type="hidden" name="pedidoId" value="{$idPedido}" />
                    <input type="hidden" name="accion" value="reabrir" />
                    <button type="submit" class="btn btn-volver">Volver al carrito</button>
                </form>
            </div>
        </div>
    </section>
<script src="{$urlJsPedidos}"></script>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
