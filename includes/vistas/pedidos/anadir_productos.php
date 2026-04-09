<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Pedido;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Pedido\Tipo;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Oferta\OfertaService;

require_once dirname(__DIR__, 3) . '/includes/config.php';

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
    } elseif ($accionSolicitada === 'update') {
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
    } elseif ($accionSolicitada === 'delete') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        if ($idPedido) {
            PedidoService::eliminarProductoPedido($idPedido, $idProducto);
        } else {
            unset($_SESSION['carrito_temp'][$idProducto]);
        }
        $mensajeExito = "Producto eliminado del pedido.";
    } elseif ($accionSolicitada === 'confirmar') {
        $carritoSession = $_SESSION['carrito_temp'] ?? [];

        if ($idPedido) {
            // Pedido ya existente — calcular total y confirmar
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
            if (count($pedidoDesglosado->getProductos()) > 0) {
                $precioTotalAcumulado = 0;
                foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                    $precioTotalAcumulado += $productoEnPedido->getPrecio() * $productoEnPedido->getCantidad();
                }

                $descuento = 0.0;
                $ofertaID = intval($_SESSION['oferta_seleccionada'] ?? 0);
                if ($ofertaID > 0) {
                    $carrito = [];
                    foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                        $carrito[] = [
                            'producto_id' => $productoEnPedido->getProductoId(),
                            'cantidad' => $productoEnPedido->getCantidad()
                        ];
                    }
                    if (OfertaService::esAplicable($ofertaID, $carrito)) {
                        $descuento = OfertaService::calcularDescuento($ofertaID, $carrito);
                    }
                }

                $pedido->setTotal($precioTotalAcumulado);
                $pedido->setDescuento($descuento);
                $pedido->setEstado(Estado::Recibido);

                if (PedidoService::actualizar($pedido)) {
                    #registrar oferta usada y limpiar la sesión
                    if ($ofertaID > 0 && $descuento > 0) {
                        OfertaService::registrarOfertaEnPedido($idPedido, $ofertaID);
                        unset($_SESSION['oferta_seleccionada']);
                    }

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

            $descuentoSesion = 0.0;
            $ofertaIDSesion = intval($_SESSION['oferta_seleccionada'] ?? 0);

            if ($ofertaIDSesion > 0) {
                $carritoSesion = [];
                foreach ($carritoSession as $item) {
                    $carritoSesion[] = [
                        'producto_id' => $item['productoId'],
                        'cantidad' => $item['cantidad']
                    ];
                }
                if (OfertaService::esAplicable($ofertaIDSesion, $carritoSesion)) {
                    $descuentoSesion = OfertaService::calcularDescuento($ofertaIDSesion, $carritoSesion);
                }
            }

            $dto = new Pedido($numero_pedido, $fecha_creacion, Estado::Recibido, $tipo, intval($_SESSION['userId']), null, $precioTotalAcumulado, null, $descuentoSesion);
            $pedidoCreado = PedidoService::crear($dto);


            if ($pedidoCreado && $pedidoCreado->getId()) {
                foreach ($carritoSession as $item) {
                    PedidoService::insertarProductoPedido($pedidoCreado->getId(), $item['productoId'], $item['cantidad'], $item['precio']);
                }

                # registrar qué oferta se usó y limpiar sesión
                if ($ofertaIDSesion > 0 && $descuentoSesion > 0) {
                    OfertaService::registrarOfertaEnPedido($pedidoCreado->getId(), $ofertaIdSesion);
                    unset($_SESSION['oferta_seleccionada']);
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

    # Acción de seleccionar oferta y limpiar oferta
    elseif ($accionSolicitada === 'oferta_seleccionar') {
        # guarda en sesión la oferta que el cliente quiere activar
        $ofertaId = intval($_POST['ofertaId'] ?? 0);
        if ($ofertaId > 0) {
            $_SESSION['oferta_seleccionada'] = $ofertaId;
        }
    } elseif ($accionSolicitada === 'oferta_limpiar') {
        # elimina la oferta seleccionada de la sesión
        unset($_SESSION['oferta_seleccionada']);
    }
    # -----------------------------------------------

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

# Funcionalidad de ofertas: cargar ofertas activas y calcular si la seleccionada aplica al carrito actual
# cargar ofertas activas y calcular si la seleccionada aplica al carrito actual
$ofertasActivas = OfertaService::listarActivas();
$ofertaIdSeleccionada = intval($_SESSION['oferta_seleccionada'] ?? 0);
$ofertaSeleccionada = $ofertaIdSeleccionada > 0 ? OfertaService::buscarPorId($ofertaIdSeleccionada) : null;

# construir carrito en formato uniforme para pasarlo a OfertaService
$carritoParaOferta = [];
foreach ($productosCarrito as $item) {
    if (is_array($item)) {
        $carritoParaOferta[] = ['producto_id' => $item['productoId'], 'cantidad' => $item['cantidad']];
    } else {
        $carritoParaOferta[] = ['producto_id' => $item->getProductoId(), 'cantidad' => $item->getCantidad()];
    }
}

# comprobar si la oferta seleccionada es aplicable y cuánto descuenta
$ofertaEsAplicable = false;
$descuentoCalculado = 0.0;
if ($ofertaSeleccionada && !empty($carritoParaOferta)) {
    $ofertaEsAplicable = OfertaService::esAplicable($ofertaIdSeleccionada, $carritoParaOferta);
    if ($ofertaEsAplicable) {
        $descuentoCalculado = OfertaService::calcularDescuento($ofertaIdSeleccionada, $carritoParaOferta);
    }
}

#---------------------------------------------------------------

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
    # sección de ofertas: lista de activas + estado de la seleccionada
    $htmlOfertasSeccion = '';
    if (!empty($ofertasActivas)) {
        $htmlOfertasSeccion .= '<div class="ofertas-carrito"><h4>Ofertas disponibles</h4>';

        # si hay una oferta seleccionada, mostrar su estado
        if ($ofertaSeleccionada) {
            $nomOfer = htmlspecialchars($ofertaSeleccionada->getNombre());
            $pctOfer = number_format($ofertaSeleccionada->getDescuento() * 100, 1, ',', '.') . '%';
            if ($ofertaEsAplicable) {
                $descuentoF = number_format($descuentoCalculado, 2, ',', '.') . ' €';
                $htmlOfertasSeccion .= "<div class=\"oferta-activa oferta-ok\">✔ <strong>{$nomOfer}</strong> — descuento: {$descuentoF}</div>";
            } else {
                $htmlOfertasSeccion .= "<div class=\"oferta-activa oferta-ko\">✗ <strong>{$nomOfer}</strong> — el carrito no cumple los requisitos</div>";
            }
            $htmlOfertasSeccion .= <<<LIMPIAR
            <form method="POST">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="accion" value="oferta_limpiar">
                <button type="submit" class="btn btn-borrar" style="font-size:0.8rem;padding:4px 10px;margin-top:6px;">Quitar oferta</button>
            </form>
        LIMPIAR;
        } else {
            # mostrar lista de ofertas disponibles para seleccionar
            foreach ($ofertasActivas as $of) {
                $nomOf = htmlspecialchars($of->getNombre());
                $descOf = htmlspecialchars($of->getDescripcion());
                $pctOf  = number_format($of->getDescuento() * 100, 1, ',', '.') . '%';
                $htmlOfertasSeccion .= <<<ITEM
                <div class="oferta-disponible">
                    <div>
                        <strong>{$nomOf}</strong> — {$pctOf} dto.<br>
                        <small>{$descOf}</small>
                    </div>
                    <form method="POST">
                        {$hiddenPedidoOTipo}
                        <input type="hidden" name="accion" value="oferta_seleccionar">
                        <input type="hidden" name="ofertaId" value="{$of->getId()}">
                        <button type="submit" class="btn btn-ver" style="font-size:0.8rem;padding:4px 10px;">Activar</button>
                    </form>
                </div>
            ITEM;
            }
        }
        $htmlOfertasSeccion .= '</div>';
    }

    # mostrar total con y sin descuento si aplica
    $htmlTotalBloque = '';
    if ($ofertaEsAplicable && $descuentoCalculado > 0) {
        $totalConDtoF = number_format($totalPrecioCarrito - $descuentoCalculado, 2, ',', '.') . ' €';
        $descuentoF   = number_format($descuentoCalculado, 2, ',', '.') . ' €';
        $htmlTotalBloque = <<<TOT
        <div class="carrito-total">
            <div style="font-size:0.88rem;color:#64748b;">Total sin descuento: {$totalPrecioCarritoFormateado} €</div>
            <div style="font-size:0.88rem;color:#ef4444;">— Descuento: {$descuentoF}</div>
            <strong>Total: {$totalConDtoF}</strong>
        </div>
    TOT;
    } else {
        $htmlTotalBloque = <<<TOT
        <div class="carrito-total">
            <strong>Total: {$totalPrecioCarritoFormateado} €</strong>
        </div>
    TOT;
    }

    $htmlCarritoCompras .= $htmlOfertasSeccion . $htmlTotalBloque . <<<HTML
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
