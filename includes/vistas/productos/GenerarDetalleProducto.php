<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Producto\Producto;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;

class GenerarDetalleProducto
{
  public static function generar(Producto $producto, string $volverUrl, bool $esGerente): string
  {
    $categoria   = CategoriaService::buscarPorId($producto->getCategoriaId());
    $nombreCat   = $categoria ? htmlspecialchars($categoria->getNombre()) : 'Sin categoría';
    $nombre      = htmlspecialchars($producto->getNombre());
    $descripcion = htmlspecialchars($producto->getDescripcion());
    $precioFinal = number_format($producto->getPrecioFinal(), 2, ',', '.');
    $iva         = $producto->getIva();
    $disponible  = $producto->isDisponible() ? 'Sí' : 'No';
    $ofertado    = $producto->isOfertado()   ? 'Sí' : 'No';

    $htmlImagenes = self::generarHtmlImagenes($producto->getId(), $nombre);
    $botonesGerente = self::generarBotonesGerente($producto, $esGerente);

    return <<<EOS
    <section id="contenido">
        <h2>Ver Producto</h2>
        <div class="detalle-producto">
            <div class="detalle-imagenes">
                {$htmlImagenes}
            </div>
            <div class="detalle-info">
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Descripción:</strong> {$descripcion}</p>
                <p><strong>Categoría:</strong> {$nombreCat}</p>
                <p><strong>Precio:</strong> {$precioFinal} €</p>
                <p><strong>IVA:</strong> {$iva}%</p>
                <p><strong>Disponible:</strong> {$disponible}</p>
                <p><strong>Ofertado:</strong> {$ofertado}</p>
            </div>
        </div>
        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$botonesGerente}
        </div>
    </section>
    EOS;
  }

  private static function generarHtmlImagenes(int $productoId, string $nombre): string
  {
    $imagenes = ProductoService::listarImagenes($productoId);

    if (!$imagenes) {
      return '<em>Sin imágenes</em>';
    }

    $primeraRuta  = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
    $rutas        = array_map(fn($img) => htmlspecialchars(RUTA_APP . $img['ruta_imagen']), $imagenes);
    $dataImagenes = htmlspecialchars(json_encode($rutas));

    $dotsHtml = '';
    if (count($imagenes) > 1) {
      foreach ($imagenes as $i => $img) {
        $active = $i === 0 ? ' active' : '';
        $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
      }
      $dotsHtml = "<div class=\"slider-dots\">{$dotsHtml}</div>";
    }

    return '<div class="slider-wrap" data-imagenes="' . $dataImagenes . '" data-auto="true">'
      . '<img class="slider-img" src="' . $primeraRuta . '" alt="' . $nombre . '">'
      . $dotsHtml
      . '</div>';
  }

  private static function generarBotonesGerente(Producto $producto, bool $esGerente): string
  {
    if (!$esGerente) {
      return '';
    }

    $editarUrl   = RUTA_VISTAS . '/productos/productosdetail.php?id=' . $producto->getId() . '&editar=1';
    $textoEstado = $producto->isActivo() ? 'Desactivar' : 'Activar';
    $claseEstado = $producto->isActivo() ? 'btn-borrar' : 'btn-editar';

    return <<<BTN
    <a href="{$editarUrl}" class="btn btn-editar">Modificar</a>
    <form method="POST" action="" style="display:inline">
        <input type="hidden" name="accion" value="toggleActivo">
        <button type="submit" class="{$claseEstado}">{$textoEstado}</button>
    </form>
    BTN;
  }
}