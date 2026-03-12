<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Pedido/PagoService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

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
$esDueno = (intval($_SESSION['userId']) === $pedidoDesglosado->getClienteId());

if (!$esGerente && !$esCamarero && !$esDueno) {
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
            $pagoValido = true;
        } else {
            $mensajeError = $resultadoValidacion['error'];
        }
    } elseif ($metodoPago === 'camarero') {
        $pagoValido = true;
    }

    if ($pagoValido) {
        if (PedidoService::cambiarEstado($idPedido, Estado::EnPreparacion)) {
            header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
            exit();
        } else {
            $mensajeError = "Error al procesar el pago.";
        }
    }
}

// Datos del pedido
$numeroPedido = htmlspecialchars($pedidoDesglosado->getNumeroPedido());
$total        = number_format($pedidoDesglosado->getTotal(), 2, ',', '.');

// Tabla de productos del pedido
$productos         = $pedidoDesglosado->getProductos();
$filasProductos    = '';

if ($productos && count($productos) > 0) {
    foreach ($productos as $prod) {
        $pNombre    = htmlspecialchars($prod->getNombre());
        $pPrecio    = number_format($prod->getPrecio(), 2, ',', '.');
        $pCantidad  = (int)$prod->getCantidad();
        $pSubtotal  = number_format($prod->getPrecio() * $pCantidad, 2, ',', '.');

        $filasProductos .= <<<FILA
            <tr>
                <td>{$pNombre}</td>
                <td class="text-center">{$pCantidad}</td>
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
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        {$filasProductos}
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong>Total</strong></td>
            <td class="text-right"><strong>{$total} €</strong></td>
        </tr>
    </tfoot>
</table>
TABLA;

$tituloPagina = "Pagar Pedido #{$numeroPedido}";
$tituloHeader = 'Finalizar Pago';

$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : "";

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
                <input type="radio" id="pago_camarero" name="metodo_pago" value="camarero" onclick="alternarMetodoPago('camarero')">
                <label for="pago_camarero">Pagar al camarero</label>
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
?>
