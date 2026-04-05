<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\Producto\ProductoService;

class listarRecompensas
{
  public static function listarRecompensas()
  {
    $tarjetas = "";
    $recompensas = RecompensaService::listarTodos();

    if ($recompensas && count($recompensas) > 0) {
      foreach ($recompensas as $r) {

        $producto = ProductoService::buscarPorId($r->getProductoId());

        $nombreProducto = htmlspecialchars($producto->getNombre());
        $bistrocoins = $r->getBistrocoinsNecesarias();

        $imagenes = ProductoService::listarImagenes($producto->getId());

        if (!empty($imagenes)) {
          $ruta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
          $htmlImg = "<img class=\"tarjeta-img-unica\" src=\"{$ruta}\" alt=\"{$nombreProducto}\">";
        } else {
          $htmlImg = "<div class=\"tarjeta-sin-imagen\"><em>Sin imagen</em></div>";
        }

        $detalleUrl = "detalleRecompensa.php?idRecompensa={$r->getId()}";

        $tarjetas .= <<<TARJETA
        <div class="tarjeta-producto">
            <div class="tarjeta-imagen">
                {$htmlImg}
            </div>

            <div class="tarjeta-info">
                <strong>{$nombreProducto}</strong>
                <span class="tarjeta-precio">{$bistrocoins} BistroCoins</span>
            </div>

            <div class="tarjeta-acciones">
                <a href="{$detalleUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        TARJETA;
      }
    } else {
      $tarjetas = '<p>No hay recompensas disponibles.</p>';
    }

    return <<<HTML
    <section id="contenido">
        <h2>Lista de recompensas</h2>
        <div class="lista-productos">
            {$tarjetas}
        </div>
    </section>
    HTML;
  }
}
