<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\pedidos\GenerarAddProductos;

require_once dirname(__DIR__, 3) . '/includes/config.php';

// --- Auth ---
if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esAjax     = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$idPedido   = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
$tipoPedido = $_GET['tipo'] ?? $_POST['tipoPedido'] ?? null;
$pedido     = null;

if ($idPedido) {
    $pedido = PedidoService::buscarPorId($idPedido);
    if (!$pedido) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
        exit();
    }
    $esDueno = (intval(Aplicacion::getUserId()) === $pedido->getClienteId());
    if (!$esGerente && !$esCamarero && !$esDueno) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }
    if ($pedido->getEstado() !== Estado::Nuevo) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . intval($idPedido));
        exit();
    }
} elseif (!$tipoPedido) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidosnew.php');
    exit();
}

$mensajeExito = '';
$mensajeError = '';

// --- POST: set_cantidad ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['accion'] ?? '') === 'set_cantidad')) {
    $idProducto        = intval($_POST['productoId'] ?? 0);
    $cantidadProducto  = max(0, intval($_POST['cantidad'] ?? 0));
    $productoExistente = ProductoService::buscarPorId($idProducto);

    if ($productoExistente) {
        if ($idPedido) {
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($idPedido));
            $yaEstaEnCarrito  = false;
            foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                if ($productoEnPedido->getProductoId() === $idProducto) {
                    $yaEstaEnCarrito = true;
                    if ($cantidadProducto > 0) {
                        PedidoService::actualizarProductoPedido(intval($idPedido), $idProducto, $cantidadProducto);
                    } else {
                        PedidoService::eliminarProductoPedido(intval($idPedido), $idProducto);
                        PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
                    }
                    break;
                }
            }
            if (!$yaEstaEnCarrito && $cantidadProducto > 0) {
                PedidoService::insertarProductoPedido(intval($idPedido), $idProducto, $cantidadProducto, $productoExistente->getPrecioFinal());
                PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
            }
        } else {
            if (!isset($_SESSION['carrito_temp'])) {
                $_SESSION['carrito_temp'] = [];
            }
            if ($cantidadProducto > 0) {
                $_SESSION['carrito_temp'][$idProducto] = [
                    'productoId' => $idProducto,
                    'nombre'     => $productoExistente->getNombre(),
                    'precio'     => $productoExistente->getPrecioFinal(),
                    'cantidad'   => $cantidadProducto,
                ];
            } else {
                unset($_SESSION['carrito_temp'][$idProducto]);
            }
        }

        if ($cantidadProducto <= 0) {
            unset($_SESSION['recompensas_canjeadas'][$idProducto]);
        }
        $mensajeExito = 'Cantidad actualizada.';
    } else {
        $mensajeError = 'Producto no válido.';
    }

    if ($esAjax) {
        $totalUnidadesActual = 0;
        if ($idPedido) {
            foreach (PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos() as $p) {
                $totalUnidadesActual += intval($p->getCantidad());
            }
        } else {
            foreach ($_SESSION['carrito_temp'] ?? [] as $item) {
                $totalUnidadesActual += intval($item['cantidad'] ?? 0);
            }
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'            => $mensajeError === '',
            'totalUnidades' => $totalUnidadesActual,
            'productoId'    => $idProducto,
            'cantidad'      => $cantidadProducto,
            'mensaje'       => $mensajeError !== '' ? $mensajeError : $mensajeExito,
        ]);
        exit();
    }
}

// --- Data preparation ---
$listaCategorias  = CategoriaService::listarTodas();
$listaProductos   = ProductoService::listarTodos();
$productosCarrito = $idPedido
    ? PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos()
    : array_values($_SESSION['carrito_temp'] ?? []);

$categoriaFiltro = isset($_GET['categoria']) && $_GET['categoria'] !== ''
    ? intval($_GET['categoria'])
    : null;

$categoriasConProductos = [];
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva()) continue;
    foreach ($listaProductos as $producto) {
        if ($producto->getCategoriaId() === $categoria->getId()
            && $producto->isDisponible()
            && $producto->isActivo()
        ) {
            $categoriasConProductos[$categoria->getId()] = true;
            break;
        }
    }
}

if ($categoriaFiltro !== null && !isset($categoriasConProductos[$categoriaFiltro])) {
    $categoriaFiltro = null;
}

$parametrosBaseFiltro = $idPedido ? ['id' => intval($idPedido)] : ['tipo' => $tipoPedido];

$hiddenPedidoOTipo = $idPedido
    ? '<input type="hidden" name="pedidoId" value="' . intval($idPedido) . '" />'
    : '<input type="hidden" name="tipoPedido" value="' . htmlspecialchars($tipoPedido ?? '') . '" />';

$cantidadesEnCarrito  = [];
$totalUnidadesCarrito = 0;
foreach ($productosCarrito as $item) {
    $pid = is_array($item) ? intval($item['productoId']) : intval($item->getProductoId());
    $qty = is_array($item) ? intval($item['cantidad'])   : intval($item->getCantidad());
    $cantidadesEnCarrito[$pid]  = $qty;
    $totalUnidadesCarrito      += $qty;
}

$tituloPagina = 'Añadir Productos';
$tituloHeader = 'Añadir Productos';
$tituloPedido = $idPedido ? 'Pedido #' . intval($pedido->getNumeroPedido()) : 'Nuevo pedido';
$urlMiCarrito = RUTA_VISTAS . '/pedidos/miCarrito.php?'
    . ($idPedido ? 'id=' . intval($idPedido) : 'tipo=' . urlencode($tipoPedido ?? 'local'));

$contenidoPrincipal = GenerarAddProductos::generar(
    listaCategorias:        $listaCategorias,
    listaProductos:         $listaProductos,
    cantidadesEnCarrito:    $cantidadesEnCarrito,
    totalUnidadesCarrito:   $totalUnidadesCarrito,
    categoriaFiltro:        $categoriaFiltro,
    categoriasConProductos: $categoriasConProductos,
    parametrosBaseFiltro:   $parametrosBaseFiltro,
    hiddenPedidoOTipo:      $hiddenPedidoOTipo,
    tituloPedido:           $tituloPedido,
    urlMiCarrito:           $urlMiCarrito,
    mensajeExito:           $mensajeExito,
    mensajeError:           $mensajeError
);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
