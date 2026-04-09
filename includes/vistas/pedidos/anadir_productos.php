<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Pedido;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Pedido\Tipo;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Recompensa\RecompensaService;

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

$recompensas = RecompensaService::listarTodos();
$recompensasPorProducto = [];
foreach ($recompensas as $recompensa) {
    $recompensasPorProducto[intval($recompensa->getProductoId())] = intval($recompensa->getBistroCoinsNecesarias());
}

if (!isset($_SESSION['recompensas_canjeadas']) || !is_array($_SESSION['recompensas_canjeadas'])) {
    $_SESSION['recompensas_canjeadas'] = [];
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

$calcularCanje = function (array $carrito, array $canjes, array $recompensasProducto): array {
    $costeBistrocoins = 0;
    $descuentoCanje = 0.0;

    foreach ($carrito as $item) {
        $productoId = intval($item['productoId']);
        if (!empty($canjes[$productoId]) && isset($recompensasProducto[$productoId])) {
            $costeBistrocoins += intval($recompensasProducto[$productoId]) * intval($item['cantidad']);
            $descuentoCanje += floatval($item['precio']) * intval($item['cantidad']);
        }
    }

    return [$costeBistrocoins, $descuentoCanje];
};

$clienteIdPedidoActual = $idPedido ? intval($pedido->getClienteId()) : intval($_SESSION['userId']);
$usuarioSaldoActual = UsuarioService::buscarPorId($clienteIdPedidoActual);
$saldoBistrocoinsCliente = ($clienteIdPedidoActual === intval($_SESSION['userId']) && isset($_SESSION['saldo']))
    ? intval($_SESSION['saldo'])
    : ($usuarioSaldoActual ? intval($usuarioSaldoActual->getSaldoBistrocoins()) : 0);

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
                PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 0);
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
            if ($cantidadProducto <= 0) {
                PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 0);
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

        if ($cantidadProducto <= 0) {
            unset($_SESSION['recompensas_canjeadas'][$idProducto]);
        }
        $mensajeExito = "Cantidad actualizada.";
    } elseif ($accionSolicitada === 'delete') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        if ($idPedido) {
            PedidoService::eliminarProductoPedido($idPedido, $idProducto);
            PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 0);
        } else {
            unset($_SESSION['carrito_temp'][$idProducto]);
        }
        unset($_SESSION['recompensas_canjeadas'][$idProducto]);
        $mensajeExito = "Producto eliminado del pedido.";
    } elseif ($accionSolicitada === 'toggle_recompensa') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $canjear = intval($_POST['canjear'] ?? 0) === 1;

        $itemsCarritoActual = $idPedido
            ? PedidoService::buscarDesglosadoPorId($idPedido)->getProductos()
            : array_values($_SESSION['carrito_temp'] ?? []);

        $carritoNormalizado = $normalizarCarrito($itemsCarritoActual);
        $productoEnCarrito = false;
        foreach ($carritoNormalizado as $item) {
            if (intval($item['productoId']) === $idProducto) {
                $productoEnCarrito = true;
                break;
            }
        }

        if (!$productoEnCarrito) {
            $mensajeError = "El producto no está en el carrito.";
        } elseif (!isset($recompensasPorProducto[$idProducto])) {
            $mensajeError = "Este producto no tiene recompensa.";
        } elseif (!$canjear) {
            unset($_SESSION['recompensas_canjeadas'][$idProducto]);
            if ($idPedido) {
                PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 0);
            }
            $mensajeExito = "Canje eliminado.";
        } else {
            $canjesTentativos = $_SESSION['recompensas_canjeadas'];
            $canjesTentativos[$idProducto] = true;
            [$costeTentativo, ] = $calcularCanje($carritoNormalizado, $canjesTentativos, $recompensasPorProducto);

            if ($costeTentativo <= $saldoBistrocoinsCliente) {
                $_SESSION['recompensas_canjeadas'][$idProducto] = true;
                if ($idPedido) {
                    PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 1);
                }
                $mensajeExito = "Canje aplicado.";
            } else {
                if ($idPedido) {
                    PedidoService::actualizarProductoBitCoineado($idPedido, $idProducto, 0);
                }
                $mensajeError = "No tienes BistroCoins suficientes para ese canje.";
            }
        }
    } elseif ($accionSolicitada === 'confirmar') {
        $carritoSession = $_SESSION['carrito_temp'] ?? [];

        if ($idPedido) {
            // Pedido ya existente — calcular total y confirmar
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId($idPedido);
            if (count($pedidoDesglosado->getProductos()) > 0) {
                $clienteIdPedido = intval($pedido->getClienteId());
                $usuarioClientePedido = UsuarioService::buscarPorId($clienteIdPedido);
                $saldoCliente = $usuarioClientePedido ? intval($usuarioClientePedido->getSaldoBistrocoins()) : 0;

                $carritoNormalizado = $normalizarCarrito($pedidoDesglosado->getProductos());
                $canjesSeleccionados = $_SESSION['recompensas_canjeadas'] ?? [];
                [$costeCanjeBistrocoins, $descuentoCanje] = $calcularCanje($carritoNormalizado, $canjesSeleccionados, $recompensasPorProducto);
                if ($costeCanjeBistrocoins > $saldoCliente) {
                    foreach ($canjesSeleccionados as $productoIdCanje => $activo) {
                        if (!empty($activo)) {
                            PedidoService::actualizarProductoBitCoineado($idPedido, intval($productoIdCanje), 0);
                        }
                    }
                    $mensajeError = "No puedes confirmar: has seleccionado más BistroCoins de las que tienes disponibles.";
                } else {
                    foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                        $productoIdCanje = intval($productoEnPedido->getProductoId());
                        $bc = !empty($canjesSeleccionados[$productoIdCanje]) ? 1 : 0;
                        PedidoService::actualizarProductoBitCoineado($idPedido, $productoIdCanje, $bc);
                    }

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

                    $descuentoTotal = min($precioTotalAcumulado, $descuento + $descuentoCanje);
                    $importeFinalPagado = max(0.0, $precioTotalAcumulado - $descuentoTotal);
                    $bistrocoinsGanadas = intval(floor($importeFinalPagado));
                    $nuevoSaldoCliente = max(0, $saldoCliente - $costeCanjeBistrocoins + $bistrocoinsGanadas);

                    $pedido->setTotal($precioTotalAcumulado);
                    $pedido->setDescuento($descuentoTotal);
                    $pedido->setEstado(Estado::Recibido);

                    if (PedidoService::actualizar($pedido)) {
                        $saldoActualizado = UsuarioService::actualizarSaldoBistrocoins($clienteIdPedido, $nuevoSaldoCliente);

                        #registrar oferta usada y limpiar la sesión
                        if ($saldoActualizado && $ofertaID > 0 && $descuento > 0) {
                            OfertaService::registrarOfertaEnPedido($idPedido, $ofertaID);
                            unset($_SESSION['oferta_seleccionada']);
                        }

                        if (!$saldoActualizado) {
                            $mensajeError = "El pedido se confirmó, pero no se pudo actualizar el saldo de BistroCoins.";
                        } else {
                            if ($clienteIdPedido === intval($_SESSION['userId'])) {
                                $_SESSION['saldo'] = $nuevoSaldoCliente;
                            }
                            unset($_SESSION['recompensas_canjeadas']);
                            header('Location: ' . RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $idPedido);
                            exit();
                        }
                    } else {
                        $mensajeError = "Error al confirmar el pedido.";
                    }
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

            $clienteIdPedido = intval($_SESSION['userId']);
            $usuarioClientePedido = UsuarioService::buscarPorId($clienteIdPedido);
            $saldoCliente = $usuarioClientePedido ? intval($usuarioClientePedido->getSaldoBistrocoins()) : 0;

            $carritoNormalizado = $normalizarCarrito($carritoSession);
            $canjesSeleccionados = $_SESSION['recompensas_canjeadas'] ?? [];
            [$costeCanjeBistrocoins, $descuentoCanjeSesion] = $calcularCanje($carritoNormalizado, $canjesSeleccionados, $recompensasPorProducto);
            if ($costeCanjeBistrocoins > $saldoCliente) {
                $mensajeError = "No puedes confirmar: has seleccionado más BistroCoins de las que tienes disponibles.";
            } else {
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

                $descuentoTotalSesion = min($precioTotalAcumulado, $descuentoSesion + $descuentoCanjeSesion);
                $importeFinalPagadoSesion = max(0.0, $precioTotalAcumulado - $descuentoTotalSesion);
                $bistrocoinsGanadasSesion = intval(floor($importeFinalPagadoSesion));
                $nuevoSaldoCliente = max(0, $saldoCliente - $costeCanjeBistrocoins + $bistrocoinsGanadasSesion);

                $dto = new Pedido($numero_pedido, $fecha_creacion, Estado::Recibido, $tipo, $clienteIdPedido, null, $precioTotalAcumulado, null, $descuentoTotalSesion);
                $pedidoCreado = PedidoService::crear($dto);


                if ($pedidoCreado && $pedidoCreado->getId()) {
                    foreach ($carritoSession as $item) {
                        PedidoService::insertarProductoPedido($pedidoCreado->getId(), $item['productoId'], $item['cantidad'], $item['precio']);
                        $bc = !empty($canjesSeleccionados[intval($item['productoId'])]) ? 1 : 0;
                        PedidoService::actualizarProductoBitCoineado($pedidoCreado->getId(), intval($item['productoId']), $bc);
                    }

                    # registrar qué oferta se usó y limpiar sesión
                    if ($ofertaIDSesion > 0 && $descuentoSesion > 0) {
                        OfertaService::registrarOfertaEnPedido($pedidoCreado->getId(), $ofertaIDSesion);
                        unset($_SESSION['oferta_seleccionada']);
                    }

                    if (!UsuarioService::actualizarSaldoBistrocoins($clienteIdPedido, $nuevoSaldoCliente)) {
                        $mensajeError = "El pedido se creó, pero no se pudo actualizar el saldo de BistroCoins.";
                    } else {
                        $_SESSION['saldo'] = $nuevoSaldoCliente;
                        unset($_SESSION['recompensas_canjeadas']);
                        unset($_SESSION['carrito_temp']);
                        header('Location: ' . RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $pedidoCreado->getId());
                        exit();
                    }
                } else {
                    $mensajeError = "Error al crear el pedido.";
                }
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
                unset($_SESSION['recompensas_canjeadas']);
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
            unset($_SESSION['recompensas_canjeadas']);
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

$categoriaFiltro = isset($_GET['categoria']) && $_GET['categoria'] !== ''
    ? intval($_GET['categoria'])
    : null;

$categoriasConProductos = [];
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva()) continue;
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
    $parametrosBaseFiltro['id'] = $idPedido;
} else {
    $parametrosBaseFiltro['tipo'] = $tipoPedido;
}

$queryTodas = http_build_query($parametrosBaseFiltro);
$urlTodas = RUTA_VISTAS . '/pedidos/anadir_productos.php' . ($queryTodas !== '' ? '?' . $queryTodas : '');
$claseTodas = $categoriaFiltro === null ? 'btn btn-ver' : 'btn btn-volver';

$enlacesCategorias = '<a href="' . htmlspecialchars($urlTodas) . '" class="' . $claseTodas . '">Todas</a>';
foreach ($listaCategorias as $categoria) {
    if (!$categoria->isActiva() || !isset($categoriasConProductos[$categoria->getId()])) continue;

    $parametrosCategoria = $parametrosBaseFiltro;
    $parametrosCategoria['categoria'] = $categoria->getId();
    $urlCategoria = RUTA_VISTAS . '/pedidos/anadir_productos.php?' . http_build_query($parametrosCategoria);
    $claseBoton = $categoriaFiltro === $categoria->getId() ? 'btn btn-ver' : 'btn btn-volver';
    $nombreCategoria = htmlspecialchars($categoria->getNombre());

    $enlacesCategorias .= ' <a href="' . htmlspecialchars($urlCategoria) . '" class="' . $claseBoton . '">' . $nombreCategoria . '</a>';
}

$htmlNavCategorias = '<div class="nav-categorias">' . $enlacesCategorias . '</div>';

$carritoNormalizadoVista = $normalizarCarrito($productosCarrito);
[$costeCanjeActual, ] = $calcularCanje($carritoNormalizadoVista, $_SESSION['recompensas_canjeadas'], $recompensasPorProducto);
if ($costeCanjeActual > $saldoBistrocoinsCliente) {
    if ($idPedido) {
        foreach ($_SESSION['recompensas_canjeadas'] as $productoIdCanje => $activo) {
            if (!empty($activo)) {
                PedidoService::actualizarProductoBitCoineado($idPedido, intval($productoIdCanje), 0);
            }
        }
    }
    $_SESSION['recompensas_canjeadas'] = [];
}
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
    if ($categoriaFiltro !== null && $categoria->getId() !== $categoriaFiltro) continue;

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
    $descuentoCanjeVista = 0.0;
    $costeCanjeVistaBistrocoins = 0;

    foreach ($productosCarrito as $productoEnPedido) {
        // Compatibilidad: objeto (BD) o array (sesión)
        if (is_array($productoEnPedido)) {
            $idProducto     = $productoEnPedido['productoId'];
            $nombreProducto = htmlspecialchars($productoEnPedido['nombre']);
            $precioUnitario = number_format($productoEnPedido['precio'], 2, ',', '.');
            $cantidadActual = $productoEnPedido['cantidad'];
            $subtotalItem   = $productoEnPedido['precio'] * $cantidadActual;
            $productoCanjeado = !empty($_SESSION['recompensas_canjeadas'][$idProducto]);
        } else {
            $idProducto     = $productoEnPedido->getProductoId();
            $nombreProducto = htmlspecialchars($productoEnPedido->getNombre());
            $precioUnitario = number_format($productoEnPedido->getPrecio(), 2, ',', '.');
            $cantidadActual = $productoEnPedido->getCantidad();
            $subtotalItem   = $productoEnPedido->getPrecio() * $cantidadActual;
            $productoCanjeado = $productoEnPedido->isBistroCoineado();
        }

        $bistrocoinsRecompensaItem = $recompensasPorProducto[$idProducto] ?? null;
        $tieneRecompensa = $bistrocoinsRecompensaItem !== null;
        $costeCanjeItemBistrocoins = $tieneRecompensa ? intval($bistrocoinsRecompensaItem) * intval($cantidadActual) : 0;
        $recompensaSeleccionada = !empty($_SESSION['recompensas_canjeadas'][$idProducto]) || $productoCanjeado;

        $canjesSinProductoActual = $_SESSION['recompensas_canjeadas'];
        unset($canjesSinProductoActual[$idProducto]);
        [$costeSinProductoActual, ] = $calcularCanje($carritoNormalizadoVista, $canjesSinProductoActual, $recompensasPorProducto);
        $saldoRestanteParaEsteProducto = max(0, $saldoBistrocoinsCliente - $costeSinProductoActual);
        $canjeDisponible = $tieneRecompensa && $costeCanjeItemBistrocoins <= $saldoRestanteParaEsteProducto;

        if ($recompensaSeleccionada && $tieneRecompensa) {
            $descuentoCanjeVista += $subtotalItem;
            $costeCanjeVistaBistrocoins += $costeCanjeItemBistrocoins;
        }

        $htmlCanjeRecompensa = '';
        if ($tieneRecompensa) {
            if ($recompensaSeleccionada) {
                $textoBotonCanje = 'Quitar canje';
                $valorCanjear = 0;
                $disabledCanje = '';
                $claseBotonCanje = 'btn-borrar';
            } elseif ($canjeDisponible) {
                $textoBotonCanje = "Canjear {$costeCanjeItemBistrocoins} BistroCoins";
                $valorCanjear = 1;
                $disabledCanje = '';
                $claseBotonCanje = 'btn-ver';
            } else {
                $textoBotonCanje = "Sin saldo ({$costeCanjeItemBistrocoins} BistroCoins)";
                $valorCanjear = 1;
                $disabledCanje = 'disabled';
                $claseBotonCanje = 'btn-ver';
            }

            $htmlCanjeRecompensa = <<<HTML
            <form method="POST" class="form-toggle-recompensa" style="margin-left:8px;">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="toggle_recompensa" />
                <input type="hidden" name="canjear" value="{$valorCanjear}" />
                <button type="submit" class="btn {$claseBotonCanje}" style="font-size:0.75rem;padding:4px 8px;" {$disabledCanje}>{$textoBotonCanje}</button>
            </form>
HTML;
        }

        $totalPrecioCarrito += $subtotalItem;
        $subtotalItemFormateado = number_format($subtotalItem, 2, ',', '.');

        $htmlCarritoCompras .= <<<HTML
        <div class="carrito-item">
            <span class="item-nombre">{$nombreProducto}</span>
            <span class="item-precio">{$precioUnitario} €</span>
            {$htmlCanjeRecompensa}
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
    $descuentoTotalVista = $descuentoCanjeVista;
    if ($ofertaEsAplicable && $descuentoCalculado > 0) {
        $descuentoTotalVista += $descuentoCalculado;
    }

    if ($descuentoTotalVista > 0) {
        $descuentoTotalVista = min($totalPrecioCarrito, $descuentoTotalVista);
        $totalConDtoF = number_format($totalPrecioCarrito - $descuentoTotalVista, 2, ',', '.') . ' €';
        $descuentoF   = number_format($descuentoCalculado, 2, ',', '.') . ' €';
        $descuentoCanjeF = number_format($descuentoCanjeVista, 2, ',', '.') . ' €';
        $htmlTotalBloque = <<<TOT
        <div class="carrito-total">
            <div style="font-size:0.88rem;color:#64748b;">Total sin descuento: {$totalPrecioCarritoFormateado} €</div>
            <div style="font-size:0.88rem;color:#ef4444;">— Descuento oferta: {$descuentoF}</div>
            <div style="font-size:0.88rem;color:#0369a1;">— Canje recompensas: {$descuentoCanjeF} ({$costeCanjeVistaBistrocoins} BistroCoins)</div>
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
            {$htmlNavCategorias}
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
