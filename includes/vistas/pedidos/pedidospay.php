<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\PagoService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\pedidos\GenerarPedidoPay;

require_once dirname(__DIR__, 3) . '/includes/config.php';

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$idPedido         = intval($_GET['id']);
$pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
if (!$pedidoDesglosado) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esCocinero = (Aplicacion::getRolId() === Usuario::ROL_COCINERO);
$esDueno    = (intval(Aplicacion::getUserId()) === $pedidoDesglosado->getClienteId());

if (!$esGerente && !$esCamarero && !$esCocinero && !$esDueno) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

if ($pedidoDesglosado->getEstado() !== Estado::Recibido) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metodo_pago'])) {
    $metodoPago = $_POST['metodo_pago'];

    if ($metodoPago === 'tarjeta') {
        $numeroTarjeta       = $_POST['numero_tarjeta'] ?? '';
        $resultadoValidacion = PagoService::validarTarjeta($numeroTarjeta);

        if ($resultadoValidacion['valido']) {
            if (PedidoService::cambiarEstado($idPedido, Estado::EnPreparacion)) {
                header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
                exit();
            } else {
                $mensajeError = 'Error al procesar el pago.';
            }
        } else {
            $mensajeError = $resultadoValidacion['error'];
        }
    } elseif ($metodoPago === 'camarero') {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
        exit();
    }
}

$ofertasPedido = OfertaService::listarOfertasDePedido($pedidoDesglosado->getId());

$carritoPedido = [];
foreach ($pedidoDesglosado->getProductos() as $pp) {
    $carritoPedido[] = ['producto_id' => $pp->getProductoId(), 'cantidad' => $pp->getCantidad()];
}
$idsOfertas = array_map(fn($o) => $o->getId(), $ofertasPedido);
$desglose   = OfertaService::calcularDescuentoMultipleDesglosado($idsOfertas, $carritoPedido);

$numeroPedido       = htmlspecialchars($pedidoDesglosado->getNumeroPedido());
$tituloPagina       = "Pagar Pedido #{$numeroPedido}";
$tituloHeader       = 'Finalizar Pago';
$contenidoPrincipal = GenerarPedidoPay::generar($pedidoDesglosado, $ofertasPedido, $desglose, $mensajeError);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
