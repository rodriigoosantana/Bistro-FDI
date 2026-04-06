<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\Producto\ProductoService;

class listarRecompensas
{
  public static function listarRecompensas(bool $esGerente): string
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

        $detalleUrl = "detalleRecompensa.php?id={$r->getId()}";

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

    // 🔙 Botón volver (igual que productos)
    $volverUrl = RUTA_APP . '/index.php';

    // ➕ Botón crear (solo gerente)
    $btnCrearNuevo = '';
    if ($esGerente) {
      $crearUrl = RUTA_VISTAS . '/recompensas/detalleRecompensa.php';
      $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Crear nueva</a>";
    }

    return <<<HTML
    <section id="contenido">
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
}
