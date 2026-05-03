<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Producto\ProductoService;

class GenerarDetalleRecompensa
{
    public static function generar(Recompensa $recompensa, string $volverUrl, bool $esGerente): string
    {
        $productoId     = $recompensa->getProductoId();
        $producto       = ProductoService::buscarPorId($productoId);
        $nombreProducto = htmlspecialchars($producto->getNombre());
        $productoUrl    = RUTA_VISTAS . "/productos/productosdetail.php?id={$productoId}";

        $htmlImagen = self::generarHtmlImagen($productoId, $nombreProducto);
        $htmlInfo   = self::generarSeccionInfo($nombreProducto, $recompensa->getBistrocoinsNecesarias());
        $botones    = self::generarBotonesGerente($recompensa, $esGerente);

        return <<<HTML
        <section id="contenido">
            <h2>Detalle de recompensa</h2>

            <div class="detalle-producto">
                <div class="detalle-imagenes">
                    {$htmlImagen}
                </div>
                <div class="detalle-info">
                    {$htmlInfo}
                </div>
            </div>

            <div class="acciones-pagina">
                <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
                <a href="{$productoUrl}" class="btn btn-ver">Ver Producto</a>
                {$botones}
            </div>
        </section>
        HTML;
    }

    private static function generarHtmlImagen(int $productoId, string $nombreProducto): string
    {
        $imagenes = ProductoService::listarImagenes($productoId);
        if (!empty($imagenes)) {
            $ruta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
            return <<<IMG
            <div class='tarjeta-imagen'>
                <img class='tarjeta-img-unica' src='{$ruta}' alt='{$nombreProducto}'>
            </div>
            IMG;
        }
        return '<em>Sin imagen</em>';
    }

    private static function generarSeccionInfo(string $nombreProducto, int $bistrocoins): string
    {
        return <<<EOS
        <p><strong>Producto:</strong> {$nombreProducto}</p>
        <p><strong>Bistrocoins necesarias:</strong> {$bistrocoins}</p>
        EOS;
    }

    private static function generarBotonesGerente(Recompensa $recompensa, bool $esGerente): string
    {
        if (!$esGerente) {
            return '';
        }

        $editarUrl = RUTA_VISTAS . '/recompensas/recompensasdetail.php?id=' . $recompensa->getId() . '&editar=1';

        return <<<BTN
        <a href="{$editarUrl}" class="btn btn-editar">Modificar</a>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="accion" value="borrar">
            <button type="submit" class="btn btn-borrar">Eliminar</button>
        </form>
        BTN;
    }
}
