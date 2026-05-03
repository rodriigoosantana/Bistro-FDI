<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Producto\ProductoService;

class GenerarCarrito
{
    /**
     * @param array $carritoNormalizado  Items con claves: productoId, nombre, cantidad, precio, canjeado
     */
    public static function generar(
        array  $carritoNormalizado,
        array  $recompensasPorProducto,
        int    $saldoBistrocoinsCliente,
        array  $canjesActivos,
        array  $ofertasSeleccionadasDetalle,
        array  $ofertasActivas,
        float  $descuentoCalculado,
        string $hiddenPedidoOTipo,
        string $tituloPedido,
        string $urlSeguirComprando,
        string $btnCancelar,
        string $mensajeExito,
        string $mensajeError
    ): string {
        $htmlNotificacionExito = $mensajeExito ? "<p class='msg-success'>{$mensajeExito}</p>" : '';
        $htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>"   : '';
        $htmlCarritoCompras    = self::generarContenidoCarrito(
            $carritoNormalizado, $recompensasPorProducto, $saldoBistrocoinsCliente,
            $canjesActivos, $ofertasSeleccionadasDetalle, $ofertasActivas,
            $descuentoCalculado, $hiddenPedidoOTipo
        );
        $urlJsPedidos = RUTA_JS . '/pedidos.js';

        return <<<EOS
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
    }

    private static function generarContenidoCarrito(
        array  $carritoNormalizado,
        array  $recompensasPorProducto,
        int    $saldoBistrocoinsCliente,
        array  $canjesActivos,
        array  $ofertasSeleccionadasDetalle,
        array  $ofertasActivas,
        float  $descuentoCalculado,
        string $hiddenPedidoOTipo
    ): string {
        if (empty($carritoNormalizado)) {
            return '<p>El carrito está vacío.</p>';
        }

        $htmlItems                  = '';
        $totalPrecioCarrito         = 0.0;
        $descuentoCanjeVista        = 0.0;
        $costeCanjeVistaBistrocoins = 0;

        foreach ($carritoNormalizado as $item) {
            $idProducto       = intval($item['productoId']);
            $cantidadActual   = intval($item['cantidad']);
            $precioUnitario   = floatval($item['precio']);
            $subtotalItem     = $precioUnitario * $cantidadActual;
            $productoCanjeado = !empty($item['canjeado']);
            $totalPrecioCarrito += $subtotalItem;

            $bistrocoinsRecompensa  = $recompensasPorProducto[$idProducto] ?? null;
            $tieneRecompensa        = $bistrocoinsRecompensa !== null;
            $costeCanjeItem         = $tieneRecompensa ? intval($bistrocoinsRecompensa) * $cantidadActual : 0;
            $recompensaSeleccionada = !empty($canjesActivos[$idProducto]) || $productoCanjeado;

            $canjesSin    = $canjesActivos;
            unset($canjesSin[$idProducto]);
            [$costeSin,] = self::calcularCanje($carritoNormalizado, $canjesSin, $recompensasPorProducto);
            $saldoRestante  = max(0, $saldoBistrocoinsCliente - $costeSin);
            $canjeDisponible = $tieneRecompensa && $costeCanjeItem <= $saldoRestante;

            if ($recompensaSeleccionada && $tieneRecompensa) {
                $descuentoCanjeVista        += $subtotalItem;
                $costeCanjeVistaBistrocoins += $costeCanjeItem;
            }

            $htmlItems .= self::generarItemCarrito(
                $item, $tieneRecompensa, $recompensaSeleccionada,
                $canjeDisponible, $costeCanjeItem, $subtotalItem, $hiddenPedidoOTipo
            );
        }

        $htmlOfertas = self::generarSeccionOfertas($ofertasSeleccionadasDetalle, $ofertasActivas, $hiddenPedidoOTipo);
        $htmlTotal   = self::generarTotalBloque($totalPrecioCarrito, $descuentoCalculado, $ofertasSeleccionadasDetalle, $descuentoCanjeVista, $costeCanjeVistaBistrocoins);

        return $htmlItems . $htmlOfertas . $htmlTotal . <<<HTML
        <form method="POST" class="form-confirmar-pedido">
            {$hiddenPedidoOTipo}
            <input type="hidden" name="accion" value="confirmar" />
            <button type="submit" class="btn btn-nuevo btn-block">Confirmar Pedido</button>
        </form>
        HTML;
    }

    private static function generarItemCarrito(
        array  $item,
        bool   $tieneRecompensa,
        bool   $recompensaSeleccionada,
        bool   $canjeDisponible,
        int    $costeCanjeItem,
        float  $subtotalItem,
        string $hiddenPedidoOTipo
    ): string {
        $idProducto          = intval($item['productoId']);
        $nombreProducto      = htmlspecialchars($item['nombre']);
        $precioUnitarioF     = number_format(floatval($item['precio']), 2, ',', '.');
        $cantidadActual      = intval($item['cantidad']);
        $subtotalItemF       = number_format($subtotalItem, 2, ',', '.');
        $htmlCanjeRecompensa = self::generarBotonesCanje(
            $idProducto, $tieneRecompensa, $recompensaSeleccionada,
            $canjeDisponible, $costeCanjeItem, $hiddenPedidoOTipo
        );

        return <<<HTML
        <div class="carrito-item">
            <span class="item-nombre">{$nombreProducto}</span>
            <span class="item-precio">{$precioUnitarioF} €</span>
            <form method="POST" class="form-update-cart">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="update" />
                <input type="number" name="cantidad" value="{$cantidadActual}" min="0" class="input-mini" />
            </form>
            <span class="item-subtotal">{$subtotalItemF} €</span>
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

    private static function generarBotonesCanje(
        int    $idProducto,
        bool   $tieneRecompensa,
        bool   $recompensaSeleccionada,
        bool   $canjeDisponible,
        int    $costeCanjeItem,
        string $hiddenPedidoOTipo
    ): string {
        if (!$tieneRecompensa) {
            return '';
        }

        if ($recompensaSeleccionada) {
            $textoBoton  = 'Quitar canje';
            $valorCanjear = 0;
            $disabled     = '';
            $claseBtn     = 'btn-borrar';
        } elseif ($canjeDisponible) {
            $textoBoton  = 'Canjear ' . $costeCanjeItem . ' BistroCoins';
            $valorCanjear = 1;
            $disabled     = '';
            $claseBtn     = 'btn-ver';
        } else {
            $textoBoton  = 'Sin saldo (' . $costeCanjeItem . ' BistroCoins)';
            $valorCanjear = 1;
            $disabled     = 'disabled';
            $claseBtn     = 'btn-ver';
        }

        return <<<HTML
        <form method="POST" class="form-toggle-recompensa" style="margin-left:8px;">
            {$hiddenPedidoOTipo}
            <input type="hidden" name="productoId" value="{$idProducto}" />
            <input type="hidden" name="accion" value="toggle_recompensa" />
            <input type="hidden" name="canjear" value="{$valorCanjear}" />
            <button type="submit" class="btn {$claseBtn}" style="font-size:0.75rem;padding:4px 8px;" {$disabled}>{$textoBoton}</button>
        </form>
        HTML;
    }

    private static function generarSeccionOfertas(
        array  $ofertasSeleccionadasDetalle,
        array  $ofertasActivas,
        string $hiddenPedidoOTipo
    ): string {
        if (empty($ofertasActivas)) {
            return '';
        }

        $html = '<div class="ofertas-carrito"><h4>Ofertas disponibles</h4>';

        foreach ($ofertasSeleccionadasDetalle as $item) {
            $html .= self::generarOfertaActiva($item, $hiddenPedidoOTipo);
        }

        $idsSeleccionados = array_map(fn($d) => $d['oferta']->getId(), $ofertasSeleccionadasDetalle);
        foreach ($ofertasActivas as $of) {
            if (in_array($of->getId(), $idsSeleccionados)) continue;
            $html .= self::generarOfertaDisponible($of, $hiddenPedidoOTipo);
        }

        return $html . '</div>';
    }

    private static function generarOfertaActiva(array $item, string $hiddenPedidoOTipo): string
    {
        $of         = $item['oferta'];
        $nomOfer    = htmlspecialchars($of->getNombre());
        $dtoF       = number_format($item['descuento'], 2, ',', '.') . ' €';
        $ofId       = $of->getId();
        $urlDetalle = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $ofId;
        $clase      = $item['aplicable'] ? 'oferta-ok' : 'oferta-ko';
        $icono      = $item['aplicable'] ? '✔' : '✗';
        $msg        = $item['aplicable']
            ? '— descuento: ' . $dtoF
            : '— el carrito no cumple los requisitos';

        return <<<HTML
        <div class="oferta-activa {$clase}">
            {$icono} <strong>{$nomOfer}</strong> {$msg}
            <a href="{$urlDetalle}" class="btn btn-volver" style="font-size:0.75rem;padding:2px 8px;margin-left:8px;">Ver oferta</a>
            <form method="POST" style="display:inline;margin-left:8px;">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="accion" value="oferta_limpiar">
                <input type="hidden" name="ofertaId" value="{$ofId}">
                <button type="submit" class="btn btn-borrar" style="font-size:0.75rem;padding:2px 8px;">✕</button>
            </form>
        </div>
        HTML;
    }

    private static function generarOfertaDisponible($of, string $hiddenPedidoOTipo): string
    {
        $nomOf      = htmlspecialchars($of->getNombre());
        $descOf     = htmlspecialchars($of->getDescripcion());
        $pctOf      = number_format($of->getDescuento() * 100, 1, ',', '.') . '%';
        $ofId       = $of->getId();
        $urlDetalle = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $ofId;

        $lineasOf         = OfertaService::listarLineasDeOferta($ofId);
        $htmlProductosReq = '';
        foreach ($lineasOf as $linea) {
            $prod = ProductoService::buscarPorId($linea->getProductoId());
            if ($prod) {
                $htmlProductosReq .= '<li>' . htmlspecialchars($prod->getNombre()) . ' × ' . $linea->getCantidad() . '</li>';
            }
        }

        return <<<HTML
        <div class="oferta-disponible">
            <div>
                <strong>{$nomOf}</strong> — {$pctOf} dto.<br>
                <small>{$descOf}</small>
                <ul style="margin:4px 0 0 12px;padding:0;font-size:0.8rem;color:#475569;">
                    {$htmlProductosReq}
                </ul>
            </div>
            <a href="{$urlDetalle}" class="btn btn-volver" style="font-size:0.8rem;padding:4px 10px;">Ver oferta</a>
            <form method="POST">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="accion" value="oferta_seleccionar">
                <input type="hidden" name="ofertaId" value="{$ofId}">
                <button type="submit" class="btn btn-ver" style="font-size:0.8rem;padding:4px 10px;">Activar</button>
            </form>
        </div>
        HTML;
    }

    private static function generarTotalBloque(
        float $totalPrecioCarrito,
        float $descuentoCalculado,
        array $ofertasSeleccionadasDetalle,
        float $descuentoCanjeVista,
        int   $costeCanjeVistaBistrocoins
    ): string {
        $totalPrecioCarritoF = number_format($totalPrecioCarrito, 2, ',', '.') . ' €';

        $descuentoTotalVista = $descuentoCanjeVista;
        if (!empty($ofertasSeleccionadasDetalle) && $descuentoCalculado > 0) {
            $descuentoTotalVista += $descuentoCalculado;
        }

        if ($descuentoTotalVista <= 0) {
            return <<<TOT
            <div class="carrito-total">
                <strong>Total: {$totalPrecioCarritoF}</strong>
            </div>
            TOT;
        }

        $descuentoTotalVista = min($totalPrecioCarrito, $descuentoTotalVista);
        $totalConDtoF        = number_format($totalPrecioCarrito - $descuentoTotalVista, 2, ',', '.') . ' €';

        $lineasOfertas = '';
        foreach ($ofertasSeleccionadasDetalle as $item) {
            if (!$item['aplicable']) continue;
            $nomOf    = htmlspecialchars($item['oferta']->getNombre());
            $vecesTxt = $item['veces'] > 1 ? ' (×' . $item['veces'] . ')' : '';
            $dtoOfF   = number_format($item['descuento'], 2, ',', '.');
            $lineasOfertas .= "<div style=\"font-size:0.88rem;color:#ef4444;\">— {$nomOf}{$vecesTxt}: −{$dtoOfF} €</div>";
        }

        $lineaCanje = '';
        if ($descuentoCanjeVista > 0) {
            $descuentoCanjeF = number_format($descuentoCanjeVista, 2, ',', '.');
            $lineaCanje = "<div style=\"font-size:0.88rem;color:#0369a1;\">— Canje BistroCoins: −{$descuentoCanjeF} € ({$costeCanjeVistaBistrocoins} BistroCoins)</div>";
        }

        return <<<TOT
        <div class="carrito-total">
            <div style="font-size:0.88rem;color:#64748b;">Total sin descuento: {$totalPrecioCarritoF}</div>
            {$lineasOfertas}
            {$lineaCanje}
            <strong>Total: {$totalConDtoF}</strong>
        </div>
        TOT;
    }

    // Copia de la función del controlador para uso exclusivo del renderizado
    private static function calcularCanje(array $carrito, array $canjes, array $recompensasProducto): array
    {
        $costeBistrocoins = 0;
        $descuentoCanje   = 0.0;

        foreach ($carrito as $item) {
            $productoId = intval($item['productoId']);
            if (!empty($canjes[$productoId]) && isset($recompensasProducto[$productoId])) {
                $costeBistrocoins += intval($recompensasProducto[$productoId]) * intval($item['cantidad']);
                $descuentoCanje   += floatval($item['precio']) * intval($item['cantidad']);
            }
        }

        return [$costeBistrocoins, $descuentoCanje];
    }
}
