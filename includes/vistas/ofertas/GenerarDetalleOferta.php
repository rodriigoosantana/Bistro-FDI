<?php

namespace es\ucm\fdi\aw\vistas\ofertas;

use es\ucm\fdi\aw\Oferta\Oferta;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Producto\ProductoService;
use DateTime;

class GenerarDetalleOferta
{
    public static function generar(Oferta $oferta, string $listaUrl, bool $esGerente): string
    {
        $nombre   = htmlspecialchars($oferta->getNombre());
        $badge    = self::generarBadge($oferta);
        $htmlInfo = self::generarSeccionInfo($oferta);
        $htmlPack = self::generarTablaLineas($oferta);
        $botones  = self::generarBotonesGerente($oferta, $esGerente);

        return <<<EOS
        <section id="contenido">
            <h2>{$nombre} {$badge}</h2>

            <div class="detalle-oferta">
                {$htmlInfo}

                <div class="oferta-pack">
                    <h3>Productos del pack</h3>
                    {$htmlPack}
                </div>
            </div>

            <div class="acciones-pagina">
                <a href="{$listaUrl}" class="btn btn-volver">← Volver</a>
                {$botones}
            </div>
        </section>
        EOS;
    }

    private static function generarBadge(Oferta $oferta): string
    {
        $hoy = new DateTime();
        if (!$oferta->isActiva()) {
            return '<span class="badge badge-caducada">Inactiva</span>';
        } elseif ($oferta->getInicio() > $hoy) {
            return '<span class="badge badge-futura">Próxima</span>';
        } elseif ($oferta->getFin() < $hoy) {
            return '<span class="badge badge-caducada">Caducada</span>';
        }
        return '<span class="badge badge-activa">Activa</span>';
    }

    private static function generarSeccionInfo(Oferta $oferta): string
    {
        $descripcion = htmlspecialchars($oferta->getDescripcion());
        $inicio      = $oferta->getInicio()->format('d/m/Y');
        $fin         = $oferta->getFin()->format('d/m/Y');
        $pct         = number_format($oferta->getDescuento() * 100, 0, ',', '.') . ' %';

        return <<<EOS
        <p><strong>Descripción:</strong> {$descripcion}</p>
        <p><strong>Vigencia:</strong> {$inicio} – {$fin}</p>
        <p><strong>Descuento:</strong> {$pct}</p>
        EOS;
    }

    private static function generarTablaLineas(Oferta $oferta): string
    {
        $lineas     = OfertaService::listarLineasDeOferta($oferta->getId());
        $htmlLineas = '';
        $precioSin  = 0.0;

        foreach ($lineas as $linea) {
            $prod = ProductoService::buscarPorId($linea->getProductoId());
            if (!$prod) continue;

            $nomProd    = htmlspecialchars($prod->getNombre());
            $cant       = $linea->getCantidad();
            $precioUni  = $prod->getPrecioFinal();
            $subtotal   = $precioUni * $cant;
            $precioSin += $subtotal;

            $subtotalF = number_format($subtotal, 2, ',', '.') . ' €';

            $htmlLineas .= <<<LINEA
            <div class="pack-linea">
                <span class="pack-producto">{$nomProd}</span>
                <span class="pack-cant">× {$cant}</span>
                <span class="pack-precio">{$subtotalF}</span>
            </div>
            LINEA;
        }

        $descuentoEuros = round($precioSin * $oferta->getDescuento(), 2);
        $precioFinal    = max(0.0, $precioSin - $descuentoEuros);
        $precioSinF     = number_format($precioSin,       2, ',', '.') . ' €';
        $descuentoF     = number_format($descuentoEuros,  2, ',', '.') . ' €';
        $precioFinalF   = number_format($precioFinal,     2, ',', '.') . ' €';

        return <<<EOS
        {$htmlLineas}
        <div class="oferta-resumen-precio">
            <span class="precio-sin">Precio sin descuento: {$precioSinF}</span>
            <span class="descuento">– Descuento: {$descuentoF}</span>
            <span class="precio-con">Precio final: {$precioFinalF}</span>
        </div>
        EOS;
    }

    private static function generarBotonesGerente(Oferta $oferta, bool $esGerente): string
    {
        if (!$esGerente) {
            return '';
        }

        $editarUrl   = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId() . '&editar=1';
        $toggleLabel = $oferta->isActiva() ? 'Desactivar' : 'Reactivar';
        $toggleClass = $oferta->isActiva() ? 'btn btn-borrar' : 'btn btn-nuevo';
        $confirmMsg  = $oferta->isActiva() ? '¿Desactivar esta oferta?' : '¿Reactivar esta oferta?';

        return <<<BTN
        <a href="{$editarUrl}" class="btn btn-editar">Editar</a>
        <form method="POST" action="" style="display:inline"
              data-confirm="{$confirmMsg}">
            <input type="hidden" name="accion" value="toggleActiva">
            <button type="submit" class="{$toggleClass}">{$toggleLabel}</button>
        </form>
        BTN;
    }
}
