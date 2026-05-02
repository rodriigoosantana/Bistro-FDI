<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Pedido\PedidoDesglosado;

class GenerarPedidoPay
{
    public static function generar(
        PedidoDesglosado $pedido,
        array  $ofertasPedido,
        array  $desglose,
        string $mensajeError
    ): string {
        $numeroPedido          = htmlspecialchars($pedido->getNumeroPedido());
        $htmlNotificacionError = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>" : '';
        $htmlResumen           = self::generarResumenProductos($pedido, $ofertasPedido, $desglose);
        $htmlFormPago          = self::generarFormPago($pedido->getId());
        $urlJsPedidos          = RUTA_JS . '/pedidos.js';

        return <<<EOS
        <section id="contenido" class="pago-wrapper">
            <h2>Resumen de tu pedido</h2>
            {$htmlNotificacionError}

            <div class="resumen-pedido">
                {$htmlResumen}
            </div>

            <div class="pago-bloque">
                <h3>Selecciona el metodo de pago</h3>
                {$htmlFormPago}
            </div>
        </section>
        <script src="{$urlJsPedidos}"></script>
        EOS;
    }

    private static function generarResumenProductos(
        PedidoDesglosado $pedido,
        array $ofertasPedido,
        array $desglose
    ): string {
        $totalBrutoF  = number_format($pedido->getTotal(), 2, ',', '.');
        $totalPagarF  = number_format($pedido->getTotalConDescuento(), 2, ',', '.');
        $descuentoCanje = $pedido->getDescuento() - $desglose['total'];

        $htmlTabla      = self::generarTablaProductos($pedido->getProductos(), $totalBrutoF);
        $htmlDescuentos = self::generarBloqueDescuentos($ofertasPedido, $desglose, $descuentoCanje, $totalPagarF);

        return $htmlTabla . $htmlDescuentos;
    }

    private static function generarTablaProductos(array $productos, string $totalBrutoF): string
    {
        $filasProductos = '';
        foreach ($productos as $prod) {
            $pNombre      = htmlspecialchars($prod->getNombre());
            $pCantidad    = (int)$prod->getCantidad();
            $esCanjeado   = $prod->isBistroCoineado();
            $pPrecio      = number_format($prod->getPrecio(), 2, ',', '.');
            $pSubtotal    = number_format($prod->getPrecio() * $pCantidad, 2, ',', '.');
            $badgeCanje   = $esCanjeado ? ' <small class="marca-recompensa">(Recompensa)</small>' : '';

            $filasProductos .= <<<FILA
            <tr>
                <td>{$pNombre}{$badgeCanje}</td>
                <td class="text-center">{$pCantidad}</td>
                <td class="text-right">{$pPrecio} €</td>
                <td class="text-right">{$pSubtotal} €</td>
            </tr>
            FILA;
        }

        return <<<TABLA
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio ud.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {$filasProductos}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td class="text-right"><strong>{$totalBrutoF} €</strong></td>
                </tr>
            </tfoot>
        </table>
        TABLA;
    }

    private static function generarBloqueDescuentos(
        array  $ofertasPedido,
        array  $desglose,
        float  $descuentoCanje,
        string $totalPagarF
    ): string {
        $html = '';

        if (!empty($ofertasPedido)) {
            $html .= '<div class="resumen-descuentos" style="margin-top:10px;"><strong>Ofertas aplicadas:</strong>';
            foreach ($ofertasPedido as $of) {
                $info = $desglose['porOferta'][$of->getId()] ?? ['veces' => 0, 'descuento' => 0];
                if ($info['veces'] === 0) continue;
                $nom      = htmlspecialchars($of->getNombre());
                $vecesTxt = $info['veces'] > 1 ? ' (×' . $info['veces'] . ')' : '';
                $dtoF     = number_format($info['descuento'], 2, ',', '.');
                $html    .= "<div class=\"descuento\">— {$nom}{$vecesTxt}: −{$dtoF} €</div>";
            }
            $html .= '</div>';
        }

        if ($descuentoCanje > 0.01) {
            $canjeF = number_format($descuentoCanje, 2, ',', '.');
            $html  .= "<div class=\"descuento\">— Canje BistroCoins: −{$canjeF} €</div>";
        }

        $html .= "<div class=\"total-pagar\" style=\"margin-top:8px;font-weight:bold;font-size:1.1rem;\">Total a pagar: {$totalPagarF} €</div>";

        return $html;
    }

    private static function generarFormPago(int $idPedido): string
    {
        return <<<HTML
        <form id="formPago" method="POST" action="" class="form-pago">
            <div class="pago-opciones">
                <label class="pago-opcion" for="pago_tarjeta">
                    <div class="pago-opcion-body">
                        <span class="pago-opcion-titulo">Pagar con tarjeta</span>
                        <span class="pago-opcion-desc">Completa la tarjeta y se procesa al momento.</span>
                    </div>
                    <span class="pago-opcion-badge">Online</span>
                    <input type="radio" id="pago_tarjeta" name="metodo_pago" value="tarjeta" checked onclick="alternarMetodoPago('tarjeta')">
                </label>

                <label class="pago-opcion" for="pago_camarero">
                    <div class="pago-opcion-body">
                        <span class="pago-opcion-titulo">Pagar al camarero</span>
                        <span class="pago-opcion-desc">Lo abonas en sala cuando te atienda el personal.</span>
                    </div>
                    <span class="pago-opcion-badge">En sala</span>
                    <input type="radio" id="pago_camarero" name="metodo_pago" value="camarero" onclick="alternarMetodoPago('camarero')">
                </label>
            </div>

            <div id="campo_tarjeta" class="campo-pago">
                <label for="numero_tarjeta">Numero de tarjeta:</label>
                <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 1234 1234 1234">
            </div>
        </form>

        <div class="botones-pago">
            <button type="submit" form="formPago" class="btn btn-nuevo">Pagar</button>
            <form method="POST" action="miCarrito.php">
                <input type="hidden" name="pedidoId" value="{$idPedido}" />
                <input type="hidden" name="accion" value="reabrir" />
                <button type="submit" class="btn btn-volver">Volver al carrito</button>
            </form>
        </div>
        HTML;
    }
}
