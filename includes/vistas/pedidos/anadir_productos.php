<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

// ── Seguridad y Autorización ──────────────────────────────────────────────────
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$pedidoId = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
if (!$pedidoId) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$pedido = PedidoService::buscarPorId($pedidoId);
if (!$pedido) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
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
    header('Location: ' . RUTA_VISTAS . '/pedidos/verPedidoDesglosado.php?id=' . $pedidoId);
    exit();
}

$mensaje = "";
$error = "";

// ── Manejo de Acciones ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'add') {
        $prodId = intval($_POST['productoId'] ?? 0);
        $cant   = intval($_POST['cantidad'] ?? 1);
        $prod   = ProductoService::buscarPorId($prodId);

        if ($prod && $cant > 0) {
            // Comprobar si ya existe en el pedido
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($pedidoId);
            $existe = false;
            foreach ($pedidoDesglosado->getProductos() as $pInP) {
                if ($pInP->getProductoId() === $prodId) {
                    $existe = true;
                    $nuevaCant = $pInP->getCantidad() + $cant;
                    PedidoService::actualizarProductoPedido($pedidoId, $prodId, $nuevaCant);
                    break;
                }
            }
            if (!$existe) {
                PedidoService::insertarProductoPedido($pedidoId, $prodId, $cant, $prod->getPrecioFinal());
            }
            $mensaje = "Producto añadido al pedido.";
        }
    } 
    elseif ($accion === 'update') {
        $prodId = intval($_POST['productoId'] ?? 0);
        $cant   = intval($_POST['cantidad'] ?? 0);
        if ($cant > 0) {
            PedidoService::actualizarProductoPedido($pedidoId, $prodId, $cant);
        } else {
            PedidoService::eliminarProductoPedido($pedidoId, $prodId);
        }
        $mensaje = "Cantidad actualizada.";
    } 
    elseif ($accion === 'delete') {
        $prodId = intval($_POST['productoId'] ?? 0);
        PedidoService::eliminarProductoPedido($pedidoId, $prodId);
        $mensaje = "Producto eliminado del pedido.";
    }
    elseif ($accion === 'confirmar') {
        $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($pedidoId);
        if (count($pedidoDesglosado->getProductos()) > 0) {
            // Calcular total final
            $total = 0;
            foreach ($pedidoDesglosado->getProductos() as $pInP) {
                $total += $pInP->getPrecio() * $pInP->getCantidad();
            }
            $pedido->setTotal($total);
            $pedido->setEstado(Estado::Recibido); // Confirmado
            if (PedidoService::actualizar($pedido)) {
                header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
                exit();
            } else {
                $error = "Error al confirmar el pedido.";
            }
        } else {
            $error = "El pedido no tiene productos.";
        }
    }
}

// ── Carga de Datos para la Vista ─────────────────────────────────────────────
$categorias = CategoriaService::listarTodas();
$productos  = ProductoService::listarTodos();
$pedidoDesglosado = PedidoService::buscarDesglosadoPorId($pedidoId);

// ── Generación de HTML: Navegador de Productos ──────────────────────────────
$htmlNavegador = "";
foreach ($categorias as $cat) {
    if (!$cat->isActiva()) continue;
    
    $htmlProdsCat = "";
    foreach ($productos as $prod) {
        if ($prod->getCategoriaId() === $cat->getId() && $prod->isDisponible() && $prod->isActivo()) {
            $prodId = $prod->getId();
            $nombre = htmlspecialchars($prod->getNombre());
            $precio = number_format($prod->getPrecioFinal(), 2, ',', '.') . " €";
            
            $htmlProdsCat .= <<<HTML
            <div class="producto-card">
                <div class="producto-info">
                    <strong>{$nombre}</strong>
                    <span class="precio">{$precio}</span>
                </div>
                <form method="POST" class="form-add-cart">
                    <input type="hidden" name="pedidoId" value="{$pedidoId}" />
                    <input type="hidden" name="productoId" value="{$prodId}" />
                    <input type="hidden" name="accion" value="add" />
                    <input type="number" name="cantidad" value="1" min="1" class="input-mini" />
                    <button type="submit" class="btn btn-nuevo">Añadir</button>
                </form>
            </div>
HTML;
        }
    }

    if ($htmlProdsCat !== "") {
        $htmlNavegador .= <<<HTML
        <div class="categoria-section">
            <h3>{$cat->getNombre()}</h3>
            <div class="productos-grid">
                {$htmlProdsCat}
            </div>
        </div>
HTML;
    }
}

// ── Generación de HTML: Carrito ──────────────────────────────────────────────
$htmlCarrito = "";
$totalCarrito = 0;
if (count($pedidoDesglosado->getProductos()) > 0) {
    foreach ($pedidoDesglosado->getProductos() as $pInP) {
        $prodId = $pInP->getProductoId();
        $nombre = htmlspecialchars($pInP->getNombre());
        $precio = number_format($pInP->getPrecio(), 2, ',', '.');
        $cant   = $pInP->getCantidad();
        $sub    = $pInP->getPrecio() * $cant;
        $totalCarrito += $sub;
        $subStr = number_format($sub, 2, ',', '.');

        $htmlCarrito .= <<<HTML
        <div class="carrito-item">
            <span class="item-nombre">{$nombre}</span>
            <span class="item-precio">{$precio} €</span>
            <form method="POST" class="form-update-cart">
                <input type="hidden" name="pedidoId" value="{$pedidoId}" />
                <input type="hidden" name="productoId" value="{$prodId}" />
                <input type="hidden" name="accion" value="update" />
                <input type="number" name="cantidad" value="{$cant}" min="0" class="input-mini" onchange="this.form.submit()" />
            </form>
            <span class="item-subtotal">{$subStr} €</span>
            <form method="POST" class="form-delete-cart">
                <input type="hidden" name="pedidoId" value="{$pedidoId}" />
                <input type="hidden" name="productoId" value="{$prodId}" />
                <input type="hidden" name="accion" value="delete" />
                <button type="submit" class="btn-icon-delete" title="Eliminar">×</button>
            </form>
        </div>
HTML;
    }
    $totalStr = number_format($totalCarrito, 2, ',', '.');
    $htmlCarrito .= <<<HTML
    <div class="carrito-total">
        <strong>Total: {$totalStr} €</strong>
    </div>
    <form method="POST" class="form-confirmar-pedido">
        <input type="hidden" name="pedidoId" value="{$pedidoId}" />
        <input type="hidden" name="accion" value="confirmar" />
        <button type="submit" class="btn btn-nuevo btn-block">Confirmar Pedido</button>
    </form>
HTML;
} else {
    $htmlCarrito = "<p>El carrito está vacío.</p>";
}

// ── Preparación de la Vista Final ────────────────────────────────────────────
$tituloPagina = 'Añadir Productos';
$tituloHeader = 'Añadir Productos';

$htmlMensaje = $mensaje ? "<p class='msg-success'>{$mensaje}</p>" : "";
$htmlError   = $error ? "<p class='msg-error'>{$error}</p>" : "";

$contenidoPrincipal = <<<EOS
<section id="pedido-shopping">
    <h2>Pedido #{$pedido->getNumeroPedido()}</h2>
    {$htmlMensaje}
    {$htmlError}

    <div class="shopping-layout">
        <div class="product-browser">
            {$htmlNavegador}
        </div>
        
        <aside class="cart-summary">
            <h3>Tu Pedido</h3>
            <div class="carrito-contenedor">
                {$htmlCarrito}
            </div>
        </aside>
    </div>
</section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
?>
