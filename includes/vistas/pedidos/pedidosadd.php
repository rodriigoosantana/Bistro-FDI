<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Aplicacion;

require_once dirname(__DIR__, 3) . '/includes/config.php';

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$idPedido   = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
$tipoPedido = $_GET['tipo'] ?? $_POST['tipoPedido'] ?? null;
$pedido = null;

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

$normalizarCarrito = function (array $items): array {
    $carrito = [];
    foreach ($items as $item) {
        if (is_array($item)) {
            $carrito[] = [
                'productoId' => intval($item['productoId']),
                'cantidad' => intval($item['cantidad']),
                'precio' => floatval($item['precio'])
            ];
        } else {
            $carrito[] = [
                'productoId' => intval($item->getProductoId()),
                'cantidad' => intval($item->getCantidad()),
                'precio' => floatval($item->getPrecio())
            ];
        }
    }
    return $carrito;
};

$generarHtmlImagenProducto = function (int $productoId, string $nombreProducto): string {
    $imagenes = ProductoService::listarImagenes($productoId);

    if (!$imagenes) {
        return '<div class="tarjeta-sin-imagen"><em>Sin imagen</em></div>';
    }

    $primeraRuta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);

    if (count($imagenes) === 1) {
        return "<img class=\"tarjeta-img-unica\" src=\"{$primeraRuta}\" alt=\"{$nombreProducto}\">";
    }

    $rutas = array_map(fn($img) => htmlspecialchars(RUTA_APP . $img['ruta_imagen']), $imagenes);
    $dataImagenes = htmlspecialchars(json_encode($rutas));

    $dotsHtml = '';
    foreach ($imagenes as $i => $img) {
        $active = $i === 0 ? ' active' : '';
        $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
    }

    return '<div class="slider-wrap tarjeta-slider" data-imagenes="' . $dataImagenes . '" data-auto="true">'
        . '<img class="slider-img" src="' . $primeraRuta . '" alt="' . $nombreProducto . '">'
        . '<div class="slider-dots">' . $dotsHtml . '</div>'
        . '</div>';
};

$mensajeExito = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['accion'] ?? '') === 'set_cantidad')) {
    $idProducto = intval($_POST['productoId'] ?? 0);
    $cantidadProducto = max(0, intval($_POST['cantidad'] ?? 0));
    $productoExistente = ProductoService::buscarPorId($idProducto);

    if ($productoExistente) {
        if ($idPedido) {
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($idPedido));
            $yaEstaEnCarrito = false;
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
            $productosActuales = PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos();
            foreach ($productosActuales as $productoActual) {
                $totalUnidadesActual += intval($productoActual->getCantidad());
            }
        } else {
            foreach ($_SESSION['carrito_temp'] ?? [] as $itemCarrito) {
                $totalUnidadesActual += intval($itemCarrito['cantidad'] ?? 0);
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $mensajeError === '',
            'totalUnidades' => $totalUnidadesActual,
            'productoId' => $idProducto,
            'cantidad' => $cantidadProducto,
            'mensaje' => $mensajeError !== '' ? $mensajeError : $mensajeExito,
        ]);
        exit();
    }
}

$listaCategorias = CategoriaService::listarTodas();
$listaProductos  = ProductoService::listarTodos();
$productosCarrito = $idPedido
    ? PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos()
    : array_values($_SESSION['carrito_temp'] ?? []);

$categoriaFiltro = isset($_GET['categoria']) && $_GET['categoria'] !== ''
    ? intval($_GET['categoria'])
    : null;

$categoriasConProductos = [];
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva()) {
        continue;
    }
    foreach ($listaProductos as $producto) {
        if (
            $producto->getCategoriaId() === $categoria->getId()
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

$parametrosBaseFiltro = [];
if ($idPedido) {
    $parametrosBaseFiltro['id'] = intval($idPedido);
} else {
    $parametrosBaseFiltro['tipo'] = $tipoPedido;
}

$queryTodas = http_build_query($parametrosBaseFiltro);
$urlTodas = RUTA_VISTAS . '/pedidos/pedidosadd.php' . ($queryTodas !== '' ? '?' . $queryTodas : '');
$claseTodas = $categoriaFiltro === null ? 'btn btn-ver' : 'btn btn-volver';

$enlacesCategorias = '<a href="' . htmlspecialchars($urlTodas) . '" class="' . $claseTodas . '">Todas</a>';
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva() || !isset($categoriasConProductos[$categoria->getId()])) {
        continue;
    }

    $parametrosCategoria = $parametrosBaseFiltro;
    $parametrosCategoria['categoria'] = $categoria->getId();
    $urlCategoria = RUTA_VISTAS . '/pedidos/pedidosadd.php?' . http_build_query($parametrosCategoria);
    $claseBoton = $categoriaFiltro === $categoria->getId() ? 'btn btn-ver' : 'btn btn-volver';
    $nombreCategoria = htmlspecialchars($categoria->getNombre());

    $enlacesCategorias .= ' <a href="' . htmlspecialchars($urlCategoria) . '" class="' . $claseBoton . '">' . $nombreCategoria . '</a>';
}

$htmlNavCategorias = '<div class="nav-categorias">' . $enlacesCategorias . '</div>';

$hiddenPedidoOTipo = $idPedido
    ? '<input type="hidden" name="pedidoId" value="' . intval($idPedido) . '" />'
    : '<input type="hidden" name="tipoPedido" value="' . htmlspecialchars($tipoPedido ?? '') . '" />';

$carritoNormalizado = $normalizarCarrito($productosCarrito);
$cantidadesEnCarrito = [];
$totalUnidadesCarrito = 0;
foreach ($carritoNormalizado as $itemCarrito) {
    $productoIdCarrito = intval($itemCarrito['productoId']);
    $cantidadCarrito = intval($itemCarrito['cantidad']);
    $cantidadesEnCarrito[$productoIdCarrito] = $cantidadCarrito;
    $totalUnidadesCarrito += $cantidadCarrito;
}

$htmlNavegadorProductos = '';
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva()) {
        continue;
    }
    if ($categoriaFiltro !== null && $categoria->getId() !== $categoriaFiltro) {
        continue;
    }

    $htmlProductosCategoria = '';
    foreach ($listaProductos as $producto) {
        if ($producto->getCategoriaId() === $categoria->getId() && $producto->isDisponible() && $producto->isActivo()) {
            $idProducto = $producto->getId();
            $nombreProducto = htmlspecialchars($producto->getNombre());
            $precioFormateado = number_format($producto->getPrecioFinal(), 2, ',', '.') . ' €';
            $htmlImagenProducto = $generarHtmlImagenProducto($idProducto, $nombreProducto);
            $urlVerProducto = htmlspecialchars(RUTA_VISTAS . '/productos/productosdetail.php?id=' . $idProducto);
            $cantidadActual = intval($cantidadesEnCarrito[$idProducto] ?? 0);
            $mostrarBotonAdd = $cantidadActual <= 0 ? '' : ' is-hidden';
            $mostrarInputCantidad = $cantidadActual > 0 ? '' : ' is-hidden';
            $valorInputCantidad = $cantidadActual;

            $htmlProductosCategoria .= <<<HTML
            <div class="producto-card">
                <div class="tarjeta-imagen">{$htmlImagenProducto}</div>
                <div class="producto-info">
                    <strong>{$nombreProducto}</strong>
                    <span class="precio">{$precioFormateado}</span>
                </div>
                <a href="{$urlVerProducto}" class="btn btn-ver">Ver</a>
                <form method="POST" class="form-add-cart">
                    {$hiddenPedidoOTipo}
                    <input type="hidden" name="productoId" value="{$idProducto}" />
                    <input type="hidden" name="accion" value="set_cantidad" />
                    <button type="submit" class="btn btn-nuevo btn-add-cart{$mostrarBotonAdd}">Anadir al carrito</button>
                    <input type="number" name="cantidad" value="{$valorInputCantidad}" min="0" class="input-mini input-cantidad-cart{$mostrarInputCantidad}" />
                </form>
            </div>
HTML;
        }
    }

    if ($htmlProductosCategoria !== '') {
        $nombreCategoria = htmlspecialchars($categoria->getNombre());
        $htmlNavegadorProductos .= <<<HTML
        <div class="categoria-section">
            <h3>{$nombreCategoria}</h3>
            <div class="productos-grid">
                {$htmlProductosCategoria}
            </div>
        </div>
HTML;
    }
}

$tituloPagina = 'Añadir Productos';
$tituloHeader = 'Añadir Productos';

$tituloPedido = $idPedido ? 'Pedido #' . intval($pedido->getNumeroPedido()) : 'Nuevo pedido';
$htmlNotificacionExito = $mensajeExito ? "<p class='msg-success'>{$mensajeExito}</p>" : '';
$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : '';

$urlMiCarrito = RUTA_VISTAS . '/pedidos/miCarrito.php?'
    . ($idPedido ? 'id=' . intval($idPedido) : 'tipo=' . urlencode($tipoPedido ?? 'local'));
$urlJsPedidos = RUTA_JS . '/pedidos.js';

$contenidoPrincipal = <<<EOS
<section id="pedido-shopping">
    <div class="header-shopping">
        <h2>{$tituloPedido}</h2>
        <a href="{$urlMiCarrito}" class="btn btn-ver" id="btn-mi-carrito" data-total-unidades="{$totalUnidadesCarrito}">Ir a mi carrito ({$totalUnidadesCarrito})</a>
    </div>
    {$htmlNotificacionExito}
    {$htmlNotificacionError}

    <div class="product-browser">
        {$htmlNavCategorias}
        {$htmlNavegadorProductos}
    </div>
</section>
<script src="{$urlJsPedidos}"></script>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
