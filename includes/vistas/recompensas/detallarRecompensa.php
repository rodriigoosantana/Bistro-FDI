<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Producto\ProductoService;

class detallarRecompensa
{
  public static function generar(Recompensa $recompensa, string $volverUrl, bool $esGerente): string
  {
    $productoId = $recompensa->getProductoId();
    $bistrocoins = $recompensa->getBistrocoinsNecesarias();

    // Producto
    $producto = ProductoService::buscarPorId($productoId);
    $nombreProducto = htmlspecialchars($producto->getNombre());

    // Imagen SIN enlace
    $imagenes = ProductoService::listarImagenes($productoId);

    if (!empty($imagenes)) {
      $ruta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
      $htmlImagen = "<img src=\"{$ruta}\" alt=\"{$nombreProducto}\">";
    } else {
      $htmlImagen = "<em>Sin imagen</em>";
    }

    // URL producto
    $productoUrl = RUTA_VISTAS . "/productos/productosdetail.php?id={$productoId}";

    // BOTONES GERENTE
    $botonesGerente = '';

    if ($esGerente) {

      $editarUrl = RUTA_VISTAS . '/recompensas/detalleRecompensa.php?id=' . $recompensa->getId() . '&editar=1';

      $botonesGerente = "
        <a href=\"{$editarUrl}\" class=\"btn btn-editar\">Modificar</a>

        <form method=\"POST\" style=\"display:inline;\">
          <input type=\"hidden\" name=\"accion\" value=\"borrar\">
          <button type=\"submit\" class=\"btn btn-borrar\">Eliminar</button>
        </form>
      ";
    }

    return <<<HTML
<section id="contenido">
    <h2>Detalle de recompensa</h2>

    <div class="detalle-producto">

        <div class="detalle-imagenes">
            {$htmlImagen}
        </div>

        <div class="detalle-info">
            <p><strong>Producto:</strong> {$nombreProducto}</p>
            <p><strong>Bistrocoins necesarias:</strong> {$bistrocoins}</p>
        </div>

    </div>

    <div class="acciones-pagina">

        <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>

        <a href="{$productoUrl}" class="btn btn-ver">Ver Producto</a>

        {$botonesGerente}

    </div>
</section>
HTML;
  }
}
