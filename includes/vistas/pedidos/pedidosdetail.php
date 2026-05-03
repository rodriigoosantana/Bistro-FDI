<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\pedidos\GenerarDetallePedido;

require_once dirname(__DIR__, 3) . '/includes/config.php';

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esCocinero = (Aplicacion::getRolId() === Usuario::ROL_COCINERO);

if (!isset($_GET['id'])) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($_GET['id']));
if (!$pedidoDesglosado) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$volverUrl = RUTA_VISTAS . '/pedidos/pedidoslist.php';

// BORRADO (Solo gerente)
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
    PedidoService::eliminar($pedidoDesglosado->getId());
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

// CAMBIO DE ESTADO
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado' && isset($_POST['nuevo_estado'])) {
    $nuevoEstado = Estado::from(trim($_POST['nuevo_estado']));

    if ($nuevoEstado === Estado::Cocinando) {
        PedidoService::asignarCocinero($pedidoDesglosado->getId(), intval(Aplicacion::getUserId()));
    }

    // Cobro por camarero: el saldo de BistroCoins se aplica al pasar de Recibido a EnPreparacion
    if ($nuevoEstado === Estado::EnPreparacion && $pedidoDesglosado->getEstado() === Estado::Recibido) {
        PedidoService::aplicarBistrocoinsAlPagar($pedidoDesglosado->getId());
    }

    PedidoService::cambiarEstado($pedidoDesglosado->getId(), $nuevoEstado);
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . $pedidoDesglosado->getId());
    exit();
}

// TOGGLE PRODUCTO PREPARADO
if (isset($_POST['accion']) && $_POST['accion'] === 'toggle_producto' && isset($_POST['producto_id'])) {
    $productoId = intval($_POST['producto_id']);
    $pedidoId   = $pedidoDesglosado->getId();

    foreach ($pedidoDesglosado->getProductos() as $producto) {
        if ($producto->getId() === $productoId) {
            PedidoService::togglePreparadoProducto($productoId, $pedidoId, !$producto->isPreparado());
            break;
        }
    }

    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . $pedidoDesglosado->getId());
    exit();
}

$tituloPagina = 'Pedido #' . htmlspecialchars($pedidoDesglosado->getNumeroPedido());
$tituloHeader = 'Ver pedido';

$contenidoPrincipal = GenerarDetallePedido::generar($pedidoDesglosado, $volverUrl, $esGerente, $esCamarero, $esCocinero);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
