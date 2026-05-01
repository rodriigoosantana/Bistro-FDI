<?php

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Pedido;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Pedido\Tipo;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\Aplicacion;

require_once dirname(__DIR__, 3) . '/includes/config.php';

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);

$idPedido  = $_GET['id'] ?? $_POST['pedidoId'] ?? null;
$tipoPedido = $_GET['tipo'] ?? $_POST['tipoPedido'] ?? null;
$pedido = null;

if ($idPedido) {
    $pedido = PedidoService::buscarPorId(intval($idPedido));
    if (!$pedido) {
        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reabrir') {
        if ($pedido->getEstado() === Estado::Recibido) {
            PedidoService::cambiarEstado(intval($idPedido), Estado::Nuevo);
            $pedido->setEstado(Estado::Nuevo);
        }
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

$clienteIdPedidoActual = $idPedido ? intval($pedido->getClienteId()) : intval(Aplicacion::getUserId());
$usuarioSaldoActual = UsuarioService::buscarPorId($clienteIdPedidoActual);
$saldoBistrocoinsCliente = ($clienteIdPedidoActual === intval(Aplicacion::getUserId()) && isset($_SESSION['saldo']))
    ? intval($_SESSION['saldo'])
    : ($usuarioSaldoActual ? intval($usuarioSaldoActual->getSaldoBistrocoins()) : 0);

$productosCarrito = $idPedido
    ? PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos()
    : array_values($_SESSION['carrito_temp'] ?? []);

# formato uniforme para OfertaService: [['producto_id' => x, 'cantidad' => y], ...]
$carritoParaOferta = [];
foreach ($productosCarrito as $item) {
    if (is_array($item)) {
        $carritoParaOferta[] = ['producto_id' => $item['productoId'], 'cantidad' => $item['cantidad']];
    } else {
        $carritoParaOferta[] = ['producto_id' => $item->getProductoId(), 'cantidad' => $item->getCantidad()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionSolicitada = $_POST['accion'] ?? '';

    if ($accionSolicitada === 'update') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $cantidadProducto = intval($_POST['cantidad'] ?? 0);
        if ($idPedido) {
            if ($cantidadProducto > 0) {
                PedidoService::actualizarProductoPedido(intval($idPedido), $idProducto, $cantidadProducto);
            } else {
                PedidoService::eliminarProductoPedido(intval($idPedido), $idProducto);
            }
            if ($cantidadProducto <= 0) {
                PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
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
        $mensajeExito = 'Cantidad actualizada.';
    } elseif ($accionSolicitada === 'delete') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        if ($idPedido) {
            PedidoService::eliminarProductoPedido(intval($idPedido), $idProducto);
            PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
        } else {
            unset($_SESSION['carrito_temp'][$idProducto]);
        }
        unset($_SESSION['recompensas_canjeadas'][$idProducto]);
        $mensajeExito = 'Producto eliminado del pedido.';
    } elseif ($accionSolicitada === 'toggle_recompensa') {
        $idProducto = intval($_POST['productoId'] ?? 0);
        $canjear = intval($_POST['canjear'] ?? 0) === 1;

        $itemsCarritoActual = $idPedido
            ? PedidoService::buscarDesglosadoPorId(intval($idPedido))->getProductos()
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
            $mensajeError = 'El producto no está en el carrito.';
        } elseif (!isset($recompensasPorProducto[$idProducto])) {
            $mensajeError = 'Este producto no tiene recompensa.';
        } elseif (!$canjear) {
            unset($_SESSION['recompensas_canjeadas'][$idProducto]);
            if ($idPedido) {
                PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
            }
            $mensajeExito = 'Canje eliminado.';
        } else {
            $canjesTentativos = $_SESSION['recompensas_canjeadas'];
            $canjesTentativos[$idProducto] = true;
            [$costeTentativo,] = $calcularCanje($carritoNormalizado, $canjesTentativos, $recompensasPorProducto);

            if ($costeTentativo <= $saldoBistrocoinsCliente) {
                $_SESSION['recompensas_canjeadas'][$idProducto] = true;
                if ($idPedido) {
                    PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 1);
                }
                $mensajeExito = 'Canje aplicado.';
            } else {
                if ($idPedido) {
                    PedidoService::actualizarProductoBitCoineado(intval($idPedido), $idProducto, 0);
                }
                $mensajeError = 'No tienes BistroCoins suficientes para ese canje.';
            }
        }
    } elseif ($accionSolicitada === 'confirmar') {
        $carritoSession = $_SESSION['carrito_temp'] ?? [];

        if ($idPedido) {
            $pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($idPedido));
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
                            PedidoService::actualizarProductoBitCoineado(intval($idPedido), intval($productoIdCanje), 0);
                        }
                    }
                    $mensajeError = 'No puedes confirmar: has seleccionado más BistroCoins de las que tienes disponibles.';
                } else {
                    foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                        $productoIdCanje = intval($productoEnPedido->getProductoId());
                        $bc = !empty($canjesSeleccionados[$productoIdCanje]) ? 1 : 0;
                        PedidoService::actualizarProductoBitCoineado(intval($idPedido), $productoIdCanje, $bc);
                    }

                    $precioTotalAcumulado = 0;
                    foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                        $precioTotalAcumulado += $productoEnPedido->getPrecio() * $productoEnPedido->getCantidad();
                    }

                    $descuento = 0.0;
                    $ofertasIds = $_SESSION['ofertas_seleccionadas'] ?? [];
                    if (!empty($ofertasIds)) {
                        $carrito = [];
                        foreach ($pedidoDesglosado->getProductos() as $productoEnPedido) {
                            $carrito[] = ['producto_id' => $productoEnPedido->getProductoId(), 'cantidad' => $productoEnPedido->getCantidad()];
                        }
                        $descuento = OfertaService::calcularDescuentoMultiple($ofertasIds, $carrito);
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

                        if ($saldoActualizado && !empty($ofertasIds) && $descuento > 0) {
                            OfertaService::registrarOfertasEnPedido(intval($idPedido), $ofertasIds);
                            $_SESSION['ofertas_seleccionadas'] = [];
                        }

                        if (!$saldoActualizado) {
                            $mensajeError = 'El pedido se confirmó, pero no se pudo actualizar el saldo de BistroCoins.';
                        } else {
                            if ($clienteIdPedido === intval(Aplicacion::getUserId())) {
                                $_SESSION['saldo'] = $nuevoSaldoCliente;
                            }
                            unset($_SESSION['recompensas_canjeadas']);
                            header('Location: ' . RUTA_VISTAS . '/pedidos/pedidospay.php?id=' . intval($idPedido));
                            exit();
                        }
                    } else {
                        $mensajeError = 'Error al confirmar el pedido.';
                    }
                }
            } else {
                $mensajeError = 'El pedido no tiene productos.';
            }
        } elseif (!empty($carritoSession) && $tipoPedido) {
            $tipo = Tipo::from($tipoPedido);
            $fecha_creacion = new DateTime('now');
            $ultimo_pedido_hoy = PedidoService::obtenerUltimoPedidoDelDia($fecha_creacion);
            $numero_pedido = $ultimo_pedido_hoy ? $ultimo_pedido_hoy->getNumeroPedido() + 1 : 1;

            $clienteIdPedido = intval(Aplicacion::getUserId());
            $usuarioClientePedido = UsuarioService::buscarPorId($clienteIdPedido);
            $saldoCliente = $usuarioClientePedido ? intval($usuarioClientePedido->getSaldoBistrocoins()) : 0;

            $carritoNormalizado = $normalizarCarrito($carritoSession);
            $canjesSeleccionados = $_SESSION['recompensas_canjeadas'] ?? [];
            [$costeCanjeBistrocoins, $descuentoCanjeSesion] = $calcularCanje($carritoNormalizado, $canjesSeleccionados, $recompensasPorProducto);
            if ($costeCanjeBistrocoins > $saldoCliente) {
                $mensajeError = 'No puedes confirmar: has seleccionado más BistroCoins de las que tienes disponibles.';
            } else {
                $precioTotalAcumulado = 0;
                foreach ($carritoSession as $item) {
                    $precioTotalAcumulado += $item['precio'] * $item['cantidad'];
                }

                $descuentoSesion = 0.0;
                $ofertasIdsSesion = $_SESSION['ofertas_seleccionadas'] ?? [];
                if (!empty($ofertasIdsSesion)) {
                    $carritoSesion = [];
                    foreach ($carritoSession as $item) {
                        $carritoSesion[] = ['producto_id' => $item['productoId'], 'cantidad' => $item['cantidad']];
                    }
                    $descuentoSesion = OfertaService::calcularDescuentoMultiple($ofertasIdsSesion, $carritoSesion);
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

                    if (!empty($ofertasIdsSesion) && $descuentoSesion > 0) {
                        OfertaService::registrarOfertasEnPedido($pedidoCreado->getId(), $ofertasIdsSesion);
                        $_SESSION['ofertas_seleccionadas'] = [];
                    }

                    if (!UsuarioService::actualizarSaldoBistrocoins($clienteIdPedido, $nuevoSaldoCliente)) {
                        $mensajeError = 'El pedido se creó, pero no se pudo actualizar el saldo de BistroCoins.';
                    } else {
                        $_SESSION['saldo'] = $nuevoSaldoCliente;
                        unset($_SESSION['recompensas_canjeadas']);
                        unset($_SESSION['carrito_temp']);
                        header('Location: ' . RUTA_VISTAS . '/pedidos/pedidospay.php?id=' . $pedidoCreado->getId());
                        exit();
                    }
                } else {
                    $mensajeError = 'Error al crear el pedido.';
                }
            }
        } else {
            $mensajeError = 'El carrito está vacío.';
        }
    } elseif ($accionSolicitada === 'oferta_seleccionar') {
        $ofertaId = intval($_POST['ofertaId'] ?? 0);
        if ($ofertaId <= 0) {
            $mensajeError = 'Oferta no válida.';
        } else {
            if (!isset($_SESSION['ofertas_seleccionadas']) || !is_array($_SESSION['ofertas_seleccionadas'])) {
                $_SESSION['ofertas_seleccionadas'] = [];
            }
            if (in_array($ofertaId, $_SESSION['ofertas_seleccionadas'])) {
                $mensajeError = 'Esta oferta ya está activada.';
            } elseif (!OfertaService::puedeAplicarseJunto($ofertaId, $_SESSION['ofertas_seleccionadas'], $carritoParaOferta)) {
                $mensajeError = 'No quedan unidades suficientes en el carrito para aplicar esta oferta junto con las ya activas.';
            } else {
                $_SESSION['ofertas_seleccionadas'][] = $ofertaId;
            }
        }
    } elseif ($accionSolicitada === 'cancelar') {
        if ($idPedido) {
            if (PedidoService::eliminar(intval($idPedido))) {
                unset($_SESSION['recompensas_canjeadas']);
                header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
                exit();
            }
            $mensajeError = 'Failed to cancel or delete the order from the database.';
        } else {
            if (isset($_SESSION['carrito_temp'])) {
                $_SESSION['carrito_temp'] = [];
                unset($_SESSION['carrito_temp']);
            }
            unset($_SESSION['recompensas_canjeadas']);
            header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
            exit();
        }
    } elseif ($accionSolicitada === 'oferta_limpiar') {
        $ofertaIdQuitar = intval($_POST['ofertaId'] ?? 0);
        if ($ofertaIdQuitar > 0) {
            $_SESSION['ofertas_seleccionadas'] = array_values(
                array_filter($_SESSION['ofertas_seleccionadas'] ?? [], fn($id) => $id !== $ofertaIdQuitar)
            );
        } else {
            $_SESSION['ofertas_seleccionadas'] = [];
        }
    }
}

$carritoNormalizadoVista = $normalizarCarrito($productosCarrito);
[$costeCanjeActual,] = $calcularCanje($carritoNormalizadoVista, $_SESSION['recompensas_canjeadas'], $recompensasPorProducto);
if ($costeCanjeActual > $saldoBistrocoinsCliente) {
    if ($idPedido) {
        foreach ($_SESSION['recompensas_canjeadas'] as $productoIdCanje => $activo) {
            if (!empty($activo)) {
                PedidoService::actualizarProductoBitCoineado(intval($idPedido), intval($productoIdCanje), 0);
            }
        }
    }
    $_SESSION['recompensas_canjeadas'] = [];
}

$ofertasActivas = OfertaService::listarActivas();

$ofertasSeleccionadasIds = $_SESSION['ofertas_seleccionadas'] ?? [];
$ofertasSeleccionadasDetalle = [];

$desglose = OfertaService::calcularDescuentoMultipleDesglosado(
    array_map('intval', $ofertasSeleccionadasIds),
    $carritoParaOferta
);
$descuentoCalculado = $desglose['total'];

foreach ($ofertasSeleccionadasIds as $oid) {
    $oid = intval($oid);
    $of = OfertaService::buscarPorId($oid);
    if (!$of) continue;
    $info = $desglose['porOferta'][$oid] ?? ['veces' => 0, 'descuento' => 0.0];
    $ofertasSeleccionadasDetalle[] = [
        'oferta'    => $of,
        'aplicable' => $info['veces'] > 0,
        'veces'     => $info['veces'],
        'descuento' => $info['descuento'],
    ];
}
$hayOfertasSeleccionadas = !empty($ofertasSeleccionadasDetalle);

$hiddenPedidoOTipo = $idPedido
    ? '<input type="hidden" name="pedidoId" value="' . intval($idPedido) . '" />'
    : '<input type="hidden" name="tipoPedido" value="' . htmlspecialchars($tipoPedido ?? '') . '" />';

$htmlCarritoCompras = '';
$totalPrecioCarrito = 0;

if (count($productosCarrito) > 0) {
    $descuentoCanjeVista = 0.0;
    $costeCanjeVistaBistrocoins = 0;

    foreach ($productosCarrito as $productoEnPedido) {
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
        [$costeSinProductoActual,] = $calcularCanje($carritoNormalizadoVista, $canjesSinProductoActual, $recompensasPorProducto);
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
                $textoBotonCanje = 'Canjear ' . $costeCanjeItemBistrocoins . ' BistroCoins';
                $valorCanjear = 1;
                $disabledCanje = '';
                $claseBotonCanje = 'btn-ver';
            } else {
                $textoBotonCanje = 'Sin saldo (' . $costeCanjeItemBistrocoins . ' BistroCoins)';
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
            <form method="POST" class="form-update-cart">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="update" />
                <input type="number" name="cantidad" value="{$cantidadActual}" min="0" class="input-mini" />
            </form>
            <span class="item-subtotal">{$subtotalItemFormateado} €</span>
            {$htmlCanjeRecompensa}
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
    $htmlOfertasSeccion = '';
    if (!empty($ofertasActivas)) {
        $htmlOfertasSeccion .= '<div class="ofertas-carrito"><h4>Ofertas disponibles</h4>';

        foreach ($ofertasSeleccionadasDetalle as $item) {
            $of       = $item['oferta'];
            $nomOfer  = htmlspecialchars($of->getNombre());
            $dtoF     = number_format($item['descuento'], 2, ',', '.') . ' €';
            $ofId     = $of->getId();
            $urlDetalleOferta = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $ofId;
            $clase    = $item['aplicable'] ? 'oferta-ok' : 'oferta-ko';
            $icono    = $item['aplicable'] ? '✔' : '✗';
            $vecesTxt = $item['veces'] > 1 ? ' ×' . $item['veces'] : '';
            $msg      = $item['aplicable']
                ? '— descuento: ' . $dtoF
                : '— el carrito no cumple los requisitos';

            $htmlOfertasSeccion .= <<<ACTIVA
            <div class="oferta-activa {$clase}">
                {$icono} <strong>{$nomOfer}</strong> {$msg}
                <a href="{$urlDetalleOferta}" class="btn btn-volver" style="font-size:0.75rem;padding:2px 8px;margin-left:8px;">Ver oferta</a>
                <form method="POST" style="display:inline;margin-left:8px;">
                    {$hiddenPedidoOTipo}
                    <input type="hidden" name="accion" value="oferta_limpiar">
                    <input type="hidden" name="ofertaId" value="{$ofId}">
                    <button type="submit" class="btn btn-borrar" style="font-size:0.75rem;padding:2px 8px;">✕</button>
                </form>
            </div>
ACTIVA;
        }

        $idsSeleccionados = $ofertasSeleccionadasIds;
        foreach ($ofertasActivas as $of) {
            if (in_array($of->getId(), $idsSeleccionados)) {
                continue;
            }

            $nomOf  = htmlspecialchars($of->getNombre());
            $descOf = htmlspecialchars($of->getDescripcion());
            $pctOf  = number_format($of->getDescuento() * 100, 1, ',', '.') . '%';
            $ofId   = $of->getId();
            $urlDetalleOferta = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $ofId;

            $lineasOf = OfertaService::listarLineasDeOferta($ofId);
            $htmlProductosReq = '';
            foreach ($lineasOf as $linea) {
                $prod = \es\ucm\fdi\aw\Producto\ProductoService::buscarPorId($linea->getProductoId());
                if ($prod) {
                    $pnombre = htmlspecialchars($prod->getNombre());
                    $htmlProductosReq .= '<li>' . $pnombre . ' × ' . $linea->getCantidad() . '</li>';
                }
            }

            $htmlOfertasSeccion .= <<<ITEM
            <div class="oferta-disponible">
                <div>
                    <strong>{$nomOf}</strong> — {$pctOf} dto.<br>
                    <small>{$descOf}</small>
                    <ul style="margin:4px 0 0 12px;padding:0;font-size:0.8rem;color:#475569;">
                        {$htmlProductosReq}
                    </ul>
                </div>
                <a href="{$urlDetalleOferta}" class="btn btn-volver" style="font-size:0.8rem;padding:4px 10px;">Ver oferta</a>
                <form method="POST">
                    {$hiddenPedidoOTipo}
                    <input type="hidden" name="accion" value="oferta_seleccionar">
                    <input type="hidden" name="ofertaId" value="{$ofId}">
                    <button type="submit" class="btn btn-ver" style="font-size:0.8rem;padding:4px 10px;">Activar</button>
                </form>
            </div>
ITEM;
        }

        $htmlOfertasSeccion .= '</div>';
    }

    $htmlTotalBloque = '';
    $descuentoTotalVista = $descuentoCanjeVista;
    if ($hayOfertasSeleccionadas && $descuentoCalculado > 0) {
        $descuentoTotalVista += $descuentoCalculado;
    }
    if ($descuentoTotalVista > 0) {
        $descuentoTotalVista = min($totalPrecioCarrito, $descuentoTotalVista);
        $totalConDtoF = number_format($totalPrecioCarrito - $descuentoTotalVista, 2, ',', '.') . ' €';

        # una línea por cada oferta realmente aplicada (con cuántas veces y cuánto descuenta)
        $lineasOfertas = '';
        foreach ($ofertasSeleccionadasDetalle as $item) {
            if (!$item['aplicable']) continue;
            $nomOf    = htmlspecialchars($item['oferta']->getNombre());
            $vecesTxt = $item['veces'] > 1 ? ' (×' . $item['veces'] . ')' : '';
            $dtoOfF   = number_format($item['descuento'], 2, ',', '.');
            $lineasOfertas .= "<div style=\"font-size:0.88rem;color:#ef4444;\">— {$nomOf}{$vecesTxt}: −{$dtoOfF} €</div>";
        }

        # canje BistroCoins (si lo hay)
        $lineaCanje = '';
        if ($descuentoCanjeVista > 0) {
            $descuentoCanjeF = number_format($descuentoCanjeVista, 2, ',', '.');
            $lineaCanje = "<div style=\"font-size:0.88rem;color:#0369a1;\">— Canje BistroCoins: −{$descuentoCanjeF} € ({$costeCanjeVistaBistrocoins} BistroCoins)</div>";
        }

        $htmlTotalBloque = <<<TOT
    <div class="carrito-total">
        <div style="font-size:0.88rem;color:#64748b;">Total sin descuento: {$totalPrecioCarritoFormateado} €</div>
        {$lineasOfertas}
        {$lineaCanje}
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
    $htmlCarritoCompras = '<p>El carrito está vacío.</p>';
}

$tituloPagina = 'Mi Carrito';
$tituloHeader = 'Mi Carrito';

$htmlNotificacionExito = $mensajeExito ? "<p class='msg-success'>{$mensajeExito}</p>" : '';
$htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : '';

$tituloPedido = $idPedido ? 'Pedido #' . intval($pedido->getNumeroPedido()) : 'Nuevo pedido';
$btnCancelar = <<<BTN
<form method="POST" action="" data-confirm="¿Estas seguro de que quieres cancelar este pedido?">
    <input type="hidden" name="accion" value="cancelar" />
    {$hiddenPedidoOTipo}
    <button type="submit" class="btn btn-borrar">Cancelar</button>
</form>
BTN;

$urlSeguirComprando = RUTA_VISTAS . '/pedidos/pedidosadd.php?'
    . ($idPedido ? 'id=' . intval($idPedido) : 'tipo=' . urlencode($tipoPedido ?? 'local'));
$urlJsPedidos = RUTA_JS . '/pedidos.js';

$contenidoPrincipal = <<<EOS
<section id="pedido-shopping">
    <div class="header-shopping">
        <h2>{$tituloPedido}</h2>
        <div style="display:flex; gap:8px;">
            <a href="{$urlSeguirComprando}" class="btn btn-volver">Seguir añadiendo</a>
            {$btnCancelar}
        </div>
    </div>
    {$htmlNotificacionExito}
    {$htmlNotificacionError}

    <div class="cart-summary" style="width:100%;position:static;">
        <h3>Tu Pedido</h3>
        <div class="carrito-contenedor">
            {$htmlCarritoCompras}
        </div>
    </div>
</section>
<script src="{$urlJsPedidos}"></script>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
