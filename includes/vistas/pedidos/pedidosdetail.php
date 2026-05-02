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
$esPersonal = $esGerente || $esCamarero || $esCocinero;

if (!isset($_GET['id'])) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($_GET['id']));
if (!$pedidoDesglosado) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

# control de acceso a nivel de pedido: el cliente solo ve los suyos; el personal ve todos
$esDuenoPedido = (intval(Aplicacion::getUserId()) === $pedidoDesglosado->getClienteId());
if (!$esPersonal && !$esDuenoPedido) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

$volverUrl = RUTA_VISTAS . '/pedidos/pedidoslist.php';

// BORRADO (Solo gerente)
# BORRADO: solo gerente
if (isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
    if (!$esGerente) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }
    PedidoService::eliminar($pedidoDesglosado->getId());
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

// CAMBIO DE ESTADO
# CAMBIO DE ESTADO: comprueba rol y que la transición está permitida
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado' && isset($_POST['nuevo_estado'])) {
    if (!$esPersonal) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }

    # validar que el valor recibido es un Estado real
    try {
        $nuevoEstado = Estado::from(trim($_POST['nuevo_estado']));
    } catch (\ValueError $e) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . $pedidoDesglosado->getId());
        exit();
    }

    # autorizar la transición concreta segun rol y estado actual
    # estas reglas espejan GenerarDetallePedido::generarBotonesAccion
    $estadoActual = $pedidoDesglosado->getEstado();
    $puedeTransicionar = false;

    if ($esPersonal) {
        if ($estadoActual === Estado::Nuevo         && $nuevoEstado === Estado::Cancelado)     $puedeTransicionar = true;
        if ($estadoActual === Estado::Recibido      && $nuevoEstado === Estado::EnPreparacion) $puedeTransicionar = true;
        if ($estadoActual === Estado::ListoCocina   && $nuevoEstado === Estado::Terminado)     $puedeTransicionar = true;
        if ($estadoActual === Estado::Terminado     && $nuevoEstado === Estado::Entregado)     $puedeTransicionar = true;
    }
    if ($esGerente || $esCocinero) {
        if ($estadoActual === Estado::EnPreparacion && $nuevoEstado === Estado::Cocinando)    $puedeTransicionar = true;
        if ($estadoActual === Estado::Cocinando     && $nuevoEstado === Estado::ListoCocina)  $puedeTransicionar = true;
    }

    if (!$puedeTransicionar) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }

    if ($nuevoEstado === Estado::Cocinando) {
        PedidoService::asignarCocinero($pedidoDesglosado->getId(), intval(Aplicacion::getUserId()));
    }

    PedidoService::cambiarEstado($pedidoDesglosado->getId(), $nuevoEstado);
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . $pedidoDesglosado->getId());
    exit();
}

// TOGGLE PRODUCTO PREPARADO
if (isset($_POST['accion']) && $_POST['accion'] === 'toggle_producto' && isset($_POST['producto_id'])) {
    if (!($esGerente || $esCocinero) || $pedidoDesglosado->getEstado() !== Estado::Cocinando) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }

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
