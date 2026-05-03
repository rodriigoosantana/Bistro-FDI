<?php

namespace es\ucm\fdi\aw\vistas\ofertas;

use es\ucm\fdi\aw\Oferta\Oferta;
use DateTime;

class GenerarListaOfertas
{
    public static function generar(array $ofertas, bool $esGerente): string
    {
        $htmlItems = self::generarItems($ofertas, $esGerente);
        $btnNueva  = self::generarBotonNueva($esGerente);
        $volverUrl = RUTA_APP . '/index.php';

        return <<<EOS
        <section id="contenido">
            <h2>Ofertas</h2>
            <div class="lista-ofertas">
                {$htmlItems}
            </div>
            <div class="acciones-pagina">
                <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
                {$btnNueva}
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

    private static function generarItems(array $ofertas, bool $esGerente): string
    {
        if (empty($ofertas)) {
            return '<p>No hay ofertas disponibles.</p>';
        }

        $html = '';
        foreach ($ofertas as $oferta) {
            $html .= self::generarTarjeta($oferta, $esGerente);
        }
        return $html;
    }

    private static function generarTarjeta(Oferta $oferta, bool $esGerente): string
    {
        $nombre      = htmlspecialchars($oferta->getNombre());
        $descripcion = htmlspecialchars($oferta->getDescripcion());
        $inicio      = $oferta->getInicio()->format('d/m/Y');
        $fin         = $oferta->getFin()->format('d/m/Y');
        $pct         = number_format($oferta->getDescuento() * 100, 1, ',', '.') . '%';
        $verUrl      = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId();
        $badge       = self::generarBadge($oferta);

        $botonesGerente = '';
        if ($esGerente) {
            $editarUrl   = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId() . '&editar=1';
            $toggleLabel = $oferta->isActiva() ? 'Desactivar' : 'Reactivar';
            $toggleClass = $oferta->isActiva() ? 'btn btn-borrar' : 'btn btn-nuevo';
            $confirmMsg  = $oferta->isActiva() ? '¿Desactivar esta oferta?' : '¿Reactivar esta oferta?';
            $botonesGerente = <<<BTN
            <a href="{$editarUrl}" class="btn btn-editar">Editar</a>
            <form method="POST" action="{$verUrl}" style="display:inline"
                  data-confirm="{$confirmMsg}">
                <input type="hidden" name="accion" value="toggleActiva">
                <button type="submit" class="{$toggleClass}">{$toggleLabel}</button>
            </form>
            BTN;
        }

        return <<<ITEM
        <div class="oferta-item">
            <div class="oferta-info">
                <span class="oferta-nombre">{$nombre}</span>
                <span class="oferta-descripcion">{$descripcion}</span>
                <div class="oferta-meta">
                    <span>{$inicio} – {$fin}</span>
                    <span>Descuento: {$pct}</span>
                    {$badge}
                </div>
            </div>
            <div class="oferta-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
                {$botonesGerente}
            </div>
        </div>
        ITEM;
    }

    private static function generarBotonNueva(bool $esGerente): string
    {
        if (!$esGerente) {
            return '';
        }
        $nuevaUrl = RUTA_VISTAS . '/ofertas/ofertasdetail.php';
        return "<a href=\"{$nuevaUrl}\" class=\"btn btn-nuevo\">Nueva oferta</a>";
    }
}
