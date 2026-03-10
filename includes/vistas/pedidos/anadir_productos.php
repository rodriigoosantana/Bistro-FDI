<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

// Seguridad y Autorización
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$idPedido = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
if (!$idPedido) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$pedido = PedidoService::buscarPorId($idPedido);
if (!$pedido) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

// Manejo de Acción Reabrir (antes del chequeo de estado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reabrir') {
    if ($pedido->getEstado() === Estado::Recibido) {
        PedidoService::cambiarEstado($idPedido, Estado::Nuevo);
        $pedido->setEstado(Estado::Nuevo);
    }
}

// Solo el cliente del pedido, un camarero o un gerente pueden modificarlo
$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);
$esDueno    = ($_SESSION['userId'] === $pedido->getClienteId());

if (!$esGerente && !$esCamarero && !$esDueno) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

// Solo se pueden añadir productos si el pedido está en estado 'nuevo'
if ($pedido->getEstado() !== Estado::Nuevo) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/verPedidoDesglosado.php?id=' . $idPedido);
    exit();
}

$mensajeExito = "";
$mensajeError = "";

// Manejo de Acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionSolicitada = $_POST['accion'] ?? '';

    if ($accionSolicitada === 'add') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $cantidadProducto = intval($_POST['cantidad'] ?? 1);
        $productoExistente = ProductoService::buscarPorId($idProducto);

        if ($productoExistente && $cantidadProducto > 0) {
            // Comprobar si ya existe en el pedido
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
            $yaEstaEnCarrito = false;
            foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                if ($productoEnPedido->getProductoId() === $idProducto) {
                    $yaEstaEnCarrito = true;
                    $nuevaCantidadTotal = $productoEnPedido->getCantidad() + $cantidadProducto;
                    PedidoService::actualizarProductoPedido($idPedido, $idProducto, $nuevaCantidadTotal);
                    break;
                }
            }
            if (!$yaEstaEnCarrito) {
                PedidoService::insertarProductoPedido($idPedido, $idProducto, $cantidadProducto, $productoExistente->getPrecioFinal());
            }
            $mensajeExito = "Producto añadido al pedido.";
        }
    } 
    elseif ($accionSolicitada === 'update') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $cantidadProducto = intval($_POST['cantidad'] ?? 0);
        if ($cantidadProducto > 0) {
            PedidoService::actualizarProductoPedido($idPedido, $idProducto, $cantidadProducto);
        } else {
            PedidoService::eliminarProductoPedido($idPedido, $idProducto);
        }
        $mensajeExito = "Cantidad actualizada.";
    } 
    elseif ($accionSolicitada === 'delete') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        PedidoService::eliminarProductoPedido($idPedido, $idProducto);
        $mensajeExito = "Producto eliminado del pedido.";
    }
    elseif ($accionSolicitada === 'confirmar') {
        $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
        if (count($pedidoDesglosado->getProductos()) > 0) {
            // Calcular total final
            $precioTotalAcumulado = 0;
            foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                $precioTotalAcumulado += $productoEnPedido->getPrecio() * $productoEnPedido->getCantidad();
            }
            $pedido->setTotal($precioTotalAcumulado);
            $pedido->setEstado(Estado::Recibido); // Confirmado
            if (PedidoService::actualizar($pedido)) {
                header('Location: ' . RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $idPedido);
                exit();
            } else {
                $mensajeError = "Error al confirmar el pedido.";
            }
        } else {
            $mensajeError = "El pedido no tiene productos.";
        }
    }
    elseif ($accionSolicitada === 'cancelar') {
        if (PedidoService::cambiarEstado($idPedido, Estado::Cancelado)) {
            header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=historial');
            exit();
        } else {
            $mensajeError = "Error al cancelar el pedido.";
        }
    }
}

// Carga de Datos para la Vista
$listaCategorias = CategoriaService::listarTodas();
$listaProductos  = ProductoService::listarTodos();
$pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);

// Generación de HTML: Navegador de Productos
$htmlNavegadorProductos = "";
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva()) continue;
    
    $htmlProductosCategoria = "";
    foreach ($listaProductos as $producto) {
        if ($producto->getCategoriaId() === $categoria->getId() && $producto->isDisponible() && $producto->isActivo()) {
            $idProducto = $producto->getId();
            $nombreProducto = htmlspecialchars($producto->getNombre());
            $precioFormateado = number_format($producto->getPrecioFinal(), 2, ',', '.') . " €";
            
            $htmlProductosCategoria .= <<<HTML
            <div class="producto-card">
                <div class="producto-info">
                    <strong>{$nombreProducto}</strong>
                    <span class="precio">{$precioFormateado}</span>
                </div>
                <form method="POST" class="form-add-cart">
                    <input type="hidden" name="pedidoId" value="{$idPedido}" />
                    <input type="hidden" name="productoId" value="{$idProducto}" />
                    <input type="hidden" name="accion" value="add" />
                    <input type="number" name="cantidad" value="1" min="1" class="input-mini" />
                    <button type="submit" class="btn btn-nuevo">Añadir</button>
                </form>
            </div>
HTML;
        }
    }

    if ($htmlProductosCategoria !== "") {
        $htmlNavegadorProductos .= <<<HTML
        <div class="categoria-section">
            <h3>{$categoria->getNombre()}</h3>
            <div class="productos-grid">
                {$htmlProductosCategoria}
            </div>
        </div>
HTML;
    }
}

// Generación de HTML: Carrito
$htmlCarritoCompras = "";
$totalPrecioCarrito = 0;
if (count($pedidoDesglosado->getProductos()) > 0) {
    foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
        $idProducto = $productoEnPedido->getProductoId();
        $nombreProducto = htmlspecialchars($productoEnPedido->getNombre());
        $precioUnitario = number_format($productoEnPedido->getPrecio(), 2, ',', '.');
        $cantidadActual = $productoEnPedido->getCantidad();
        $subtotalItem = $productoEnPedido->getPrecio() * $cantidadActual;
        $totalPrecioCarrito += $subtotalItem;
        $subtotalItemFormateado = number_format($subtotalItem, 2, ',', '.');

        $htmlCarritoCompras .= <<<HTML
        <div class="carrito-item">
            <span class="item-nombre">{$nombreProducto}</span>
            <span class="item-precio">{$precioUnitario} €</span>
            <form method="POST" class="form-update-cart">
                <input type="hidden" name="pedidoId" value="{$idPedido}" />
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="update" />
                <input type="number" name="cantidad" value="{$cantidadActual}" min="0" class="input-mini" onchange="this.form.submit()" />
            </form>
            <span class="item-subtotal">{$subtotalItemFormateado} €</span>
            <form method="POST" class="form-delete-cart">
                <input type="hidden" name="pedidoId" value="{$idPedido}" />
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="delete" />
                <button type="submit" class="btn-icon-delete" title="Eliminar">×</button>
            </form>
        </div>
HTML;
    }
    $totalPrecioCarritoFormateado = number_format($totalPrecioCarrito, 2, ',', '.');
    $htmlCarritoCompras .= <<<HTML
    <div class="carrito-total">
        <strong>Total: {$totalPrecioCarritoFormateado} €</strong>
    </div>
    <form method="POST" class="form-confirmar-pedido">
        <input type="hidden" name="pedidoId" value="{$idPedido}" />
        <input type="hidden" name="accion" value="confirmar" />
        <button type="submit" class="btn btn-nuevo btn-block">Confirmar Pedido</button>
    </form>
HTML;
} else {
    $htmlCarritoCompras = "<p>El carrito está vacío.</p>";
}

// Preparación de la Vista Final
$tituloPagina = 'Añadir Productos';
$tituloHeader = 'Añadir Productos';

$htmlNotificacionExito = $mensajeExito ? "<p class='msg-success'>{$mensajeExito}</p>" : "";
$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : "";

$contenidoPrincipal = <<<EOS
<section id="pedido-shopping">
    <div class="header-shopping">
        <h2>Pedido #{$pedido->getNumeroPedido()}</h2>
        <form method="POST" action="" onsubmit="return confirmarCancelacionPedido()">
            <input type="hidden" name="pedidoId" value="{$idPedido}" />
            <input type="hidden" name="accion" value="cancelar" />
            <button type="submit" class="btn btn-borrar">Cancelar</button>
        </form>
    </div>
    {$htmlNotificacionExito}
    {$htmlNotificacionError}

    <div class="shopping-layout">
        <div class="product-browser">
            {$htmlNavegadorProductos}
        </div>
        
        <aside class="cart-summary">
            <h3>Tu Pedido</h3>
            <div class="carrito-contenedor">
                {$htmlCarritoCompras}
            </div>
        </aside>
    </div>
</section>
<script src="../../js/pedidos.js"></script>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
?>
