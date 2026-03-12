<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Pedido/Pedido.php';

// Seguridad y Autorización
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);

$idPedido  = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
$tipoPedido = $_GET['tipo'] ?? $_POST['tipoPedido'] ?? null;
$pedido = null;

if ($idPedido) {
    // Pedido ya existente (reabrir o editar)
    $pedido = PedidoService::buscarPorId($idPedido);
    if (!$pedido) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
        exit();
    }

    // Manejo de Acción Reabrir
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reabrir') {
        if ($pedido->getEstado() === Estado::Recibido) {
            PedidoService::cambiarEstado($idPedido, Estado::Nuevo);
            $pedido->setEstado(Estado::Nuevo);
        }
    }

    $esDueno = (intval($_SESSION['userId']) === $pedido->getClienteId());
    if (!$esGerente && !$esCamarero && !$esDueno) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit();
    }

    if ($pedido->getEstado() !== Estado::Nuevo) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/verPedidoDesglosado.php?id=' . $idPedido);
        exit();
    }
} elseif ($tipoPedido) {
    // Pedido nuevo aún no creado en BD — modo carrito temporal vacío
    // El pedido se creará al confirmar
} else {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
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
            if ($idPedido) {
                // Pedido existente en BD
                $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
                $yaEstaEnCarrito = false;
                foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                    if ($productoEnPedido->getProductoId() === $idProducto) {
                        $yaEstaEnCarrito = true;
                        PedidoService::actualizarProductoPedido($idPedido, $idProducto, $productoEnPedido->getCantidad() + $cantidadProducto);
                        break;
                    }
                }
                if (!$yaEstaEnCarrito) {
                    PedidoService::insertarProductoPedido($idPedido, $idProducto, $cantidadProducto, $productoExistente->getPrecioFinal());
                }
            } else {
                // Carrito en sesión
                if (!isset($_SESSION['carrito_temp'])) $_SESSION['carrito_temp'] = [];
                if (isset($_SESSION['carrito_temp'][$idProducto])) {
                    $_SESSION['carrito_temp'][$idProducto]['cantidad'] += $cantidadProducto;
                } else {
                    $_SESSION['carrito_temp'][$idProducto] = [
                        'productoId' => $idProducto,
                        'nombre'     => $productoExistente->getNombre(),
                        'precio'     => $productoExistente->getPrecioFinal(),
                        'cantidad'   => $cantidadProducto,
                    ];
                }
            }
            $mensajeExito = "Producto añadido al pedido.";
        }
    } 
    elseif ($accionSolicitada === 'update') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $cantidadProducto = intval($_POST['cantidad'] ?? 0);
        if ($idPedido) {
            if ($cantidadProducto > 0) {
                PedidoService::actualizarProductoPedido($idPedido, $idProducto, $cantidadProducto);
            } else {
                PedidoService::eliminarProductoPedido($idPedido, $idProducto);
            }
        } else {
            if ($cantidadProducto > 0) {
                if (isset($_SESSION['carrito_temp'][$idProducto])) {
                    $_SESSION['carrito_temp'][$idProducto]['cantidad'] = $cantidadProducto;
                }
            } else {
                unset($_SESSION['carrito_temp'][$idProducto]);
            }
        }
        $mensajeExito = "Cantidad actualizada.";
    } 
    elseif ($accionSolicitada === 'delete') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        if ($idPedido) {
            PedidoService::eliminarProductoPedido($idPedido, $idProducto);
        } else {
            unset($_SESSION['carrito_temp'][$idProducto]);
        }
        $mensajeExito = "Producto eliminado del pedido.";
    }
    elseif ($accionSolicitada === 'confirmar') {
        $carritoSession = $_SESSION['carrito_temp'] ?? [];

        if ($idPedido) {
            // Pedido ya existente — calcular total y confirmar
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
            if (count($pedidoDesglosado->getProductos()) > 0) {
                $precioTotalAcumulado = 0;
                foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                    $precioTotalAcumulado += $productoEnPedido->getPrecio() * $productoEnPedido->getCantidad();
                }
                $pedido->setTotal($precioTotalAcumulado);
                $pedido->setEstado(Estado::Recibido);
                if (PedidoService::actualizar($pedido)) {
                    header('Location: ' . RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $idPedido);
                    exit();
                } else {
                    $mensajeError = "Error al confirmar el pedido.";
                }
            } else {
                $mensajeError = "El pedido no tiene productos.";
            }
        } elseif (!empty($carritoSession) && $tipoPedido) {
            // Pedido nuevo — crear en BD ahora con los productos del carrito en sesión
            $tipo = Tipo::from($tipoPedido);
            $fecha_creacion = new DateTime('now');
            $ultimo_pedido_hoy = PedidoService::obtenerUltimoPedidoDelDia($fecha_creacion);
            $numero_pedido = $ultimo_pedido_hoy ? $ultimo_pedido_hoy->getNumeroPedido() + 1 : 1;

            $precioTotalAcumulado = 0;
            foreach ($carritoSession as $item) {
                $precioTotalAcumulado += $item['precio'] * $item['cantidad'];
            }

            $dto = new Pedido($numero_pedido, $fecha_creacion, Estado::Recibido, $tipo, intval($_SESSION['userId']), null, $precioTotalAcumulado);
            $pedidoCreado = PedidoService::crear($dto);

            if ($pedidoCreado && $pedidoCreado->getId()) {
                foreach ($carritoSession as $item) {
                    PedidoService::insertarProductoPedido($pedidoCreado->getId(), $item['productoId'], $item['cantidad'], $item['precio']);
                }
                unset($_SESSION['carrito_temp']);
                header('Location: ' . RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $pedidoCreado->getId());
                exit();
            } else {
                $mensajeError = "Error al crear el pedido.";
            }
        } else {
            $mensajeError = "El carrito está vacío.";
        }
    }
    elseif ($accionSolicitada === 'cancelar') {
        if ($idPedido) {
            if (PedidoService::eliminar($idPedido)) {
                header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
                exit();
            } else {
                $mensajeError = "Failed to cancel or delete the order from the database.";
            }
        } else {
            if (isset($_SESSION['carrito_temp'])) {
                $_SESSION['carrito_temp'] = [];
                unset($_SESSION['carrito_temp']);
            }
            header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
            exit();
        }
    }
}

// Carga de Datos para la Vista
$listaCategorias = CategoriaService::listarTodas();
$listaProductos  = ProductoService::listarTodos();
$productosCarrito = $idPedido
    ? PedidoService::buscarDesglosadoPorId($idPedido)->getProductos()
    : array_values($_SESSION['carrito_temp'] ?? []);

// Campo oculto reutilizable en formularios
$hiddenPedidoOTipo = $idPedido
    ? "<input type=\"hidden\" name=\"pedidoId\" value=\"{$idPedido}\" />"
    : "<input type=\"hidden\" name=\"tipoPedido\" value=\"" . htmlspecialchars($tipoPedido ?? '') . "\" />";

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
                    {$hiddenPedidoOTipo}
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

if (count($productosCarrito) > 0) {
    foreach ($productosCarrito as $productoEnPedido) {
        // Compatibilidad: objeto (BD) o array (sesión)
        if (is_array($productoEnPedido)) {
            $idProducto     = $productoEnPedido['productoId'];
            $nombreProducto = htmlspecialchars($productoEnPedido['nombre']);
            $precioUnitario = number_format($productoEnPedido['precio'], 2, ',', '.');
            $cantidadActual = $productoEnPedido['cantidad'];
            $subtotalItem   = $productoEnPedido['precio'] * $cantidadActual;
        } else {
            $idProducto     = $productoEnPedido->getProductoId();
            $nombreProducto = htmlspecialchars($productoEnPedido->getNombre());
            $precioUnitario = number_format($productoEnPedido->getPrecio(), 2, ',', '.');
            $cantidadActual = $productoEnPedido->getCantidad();
            $subtotalItem   = $productoEnPedido->getPrecio() * $cantidadActual;
        }
        $totalPrecioCarrito += $subtotalItem;
        $subtotalItemFormateado = number_format($subtotalItem, 2, ',', '.');

        $htmlCarritoCompras .= <<<HTML
        <div class="carrito-item">
            <span class="item-nombre">{$nombreProducto}</span>
            <span class="item-precio">{$precioUnitario} €</span>
            <form method="POST" class="form-update-cart">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="update" />
                <input type="number" name="cantidad" value="{$cantidadActual}" min="0" class="input-mini" onchange="this.form.submit()" />
            </form>
            <span class="item-subtotal">{$subtotalItemFormateado} €</span>
            <form method="POST" class="form-delete-cart">
                {$hiddenPedidoOTipo}
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
        {$hiddenPedidoOTipo}
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

$tituloPedido = $idPedido ? "Pedido #{$pedido->getNumeroPedido()}" : "Nuevo pedido";
$btnCancelar = <<<BTN
<form method="POST" action="" onsubmit="return confirmarCancelacionPedido()">
    <input type="hidden" name="accion" value="cancelar" />
    <input type="hidden" name="pedidoId" value="{$idPedido}" />
    <button type="submit" class="btn btn-borrar">Cancelar</button>
</form>
BTN;

$contenidoPrincipal = <<<EOS
<section id="pedido-shopping">
    <div class="header-shopping">
        <h2>{$tituloPedido}</h2>
        {$btnCancelar}
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
