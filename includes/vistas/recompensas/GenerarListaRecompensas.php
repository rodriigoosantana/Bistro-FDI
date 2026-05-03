<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Producto\ProductoService;

class GenerarListaRecompensas
{
    public static function generar(array $recompensas, bool $esGerente, int $saldo, bool $soloDisponibles): string
    {
        $tarjetas  = self::generarTarjetas($recompensas, $saldo, $soloDisponibles);
        $btnFiltro = self::generarFiltro($soloDisponibles);
        $volverUrl = RUTA_APP . '/index.php';

        $btnCrearNuevo = '';
        if ($esGerente) {
            $crearUrl      = RUTA_VISTAS . '/recompensas/recompensasdetail.php';
            $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Crear nueva</a>";
        }

        return <<<HTML
        <section id="contenido">
            <div class="acciones-pagina">
                {$btnFiltro}
            </div>

            <div class="lista-productos">
                {$tarjetas}
            </div>

            <div class="acciones-pagina">
                <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
                {$btnCrearNuevo}
            </div>
        </section>
        HTML;
    }

    private static function generarFiltro(bool $soloDisponibles): string
    {
        $filtroActivo = $soloDisponibles ? 'btn-filtrar-activo' : '';
        $filtroUrl    = $soloDisponibles ? 'recompensaslist.php' : 'recompensaslist.php?disponibles=1';
        $textoFiltro  = $soloDisponibles ? 'Mostrar todas' : 'Mostrar solo disponibles';

        return <<<HTML
        <a href="{$filtroUrl}" class="btn-filtrar {$filtroActivo}">
            {$textoFiltro}
        </a>
        HTML;
    }

    private static function generarTarjetas(array $recompensas, int $saldo, bool $soloDisponibles): string
    {
        if (empty($recompensas)) {
            return '<p>No hay recompensas disponibles.</p>';
        }

        usort($recompensas, fn($a, $b) => $a->getBistrocoinsNecesarias() <=> $b->getBistrocoinsNecesarias());

        $tarjetas = '';
        foreach ($recompensas as $r) {
            $producto = ProductoService::buscarPorId($r->getProductoId());
            if (!$producto) continue;

            $disponible = ($saldo >= $r->getBistrocoinsNecesarias());
            if ($soloDisponibles && !$disponible) continue;

            $tarjetas .= self::generarTarjeta($r, $producto->getNombre(), $r->getBistrocoinsNecesarias(), $disponible, $producto->getId());
        }

        return $tarjetas ?: '<p>No hay recompensas disponibles.</p>';
    }

    private static function generarTarjeta(Recompensa $r, string $nombreProducto, int $bistrocoins, bool $disponible, int $productoId): string
    {
        $nombreProducto      = htmlspecialchars($nombreProducto);
        $claseDisponibilidad = $disponible ? 'recompensa-disponible' : 'recompensa-no-disponible';
        $estadoTexto         = $disponible
            ? "<span class='recompensa-ok'>Disponible</span>"
            : "<span class='recompensa-ko'>No disponible</span>";

        $imagenes = ProductoService::listarImagenes($productoId);
        $htmlImg  = !empty($imagenes)
            ? "<img class=\"tarjeta-img-unica\" src=\"" . htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']) . "\" alt=\"{$nombreProducto}\">"
            : "<div class=\"tarjeta-sin-imagen\"><em>Sin imagen</em></div>";

        $detalleUrl = "recompensasdetail.php?id={$r->getId()}";

        return <<<TARJETA
        <div class="tarjeta-producto {$claseDisponibilidad}">
            <div class="tarjeta-imagen">
                {$htmlImg}
            </div>
            <div class="tarjeta-info">
                <strong>{$nombreProducto}</strong>
                <span class="tarjeta-precio">{$bistrocoins} BistroCoins</span>
                <small>{$estadoTexto}</small>
            </div>
            <div class="tarjeta-acciones">
                <a href="{$detalleUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        TARJETA;
    }
}
