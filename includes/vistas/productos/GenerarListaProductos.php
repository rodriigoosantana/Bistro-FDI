<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Producto\ProductoService;

class GenerarListaProductos
{
  public static function generar(array $productos, ?int $categoriaFiltro, bool $esGerente, array $mapaCategorias): string
  {
    $tarjetas = '';

    if ($productos && count($productos) > 0) {
      foreach ($productos as $p) {
        $nombre    = htmlspecialchars($p->getNombre());
        $nombreCat = htmlspecialchars($mapaCategorias[$p->getCategoriaId()] ?? 'Sin categoría');
        $precioFinal = number_format($p->getPrecioFinal(), 2, ',', '.');
        $verUrl    = RUTA_VISTAS . '/productos/productosdetail.php?id=' . $p->getId()
          . ($categoriaFiltro ? '&categoria=' . $categoriaFiltro : '');
        $disponible = $p->isDisponible() ? '' : '<small>(No disponible)</small>';

        $htmlImg = self::generarHtmlImagenes($p->getId(), $nombre);

        $tarjetas .= <<<TARJETA
        <div class="tarjeta-producto">
            <div class="tarjeta-imagen">{$htmlImg}</div>
            <div class="tarjeta-info">
                <strong>{$nombre}</strong> {$disponible}
                <span>{$nombreCat}</span>
                <span class="tarjeta-precio">{$precioFinal} €</span>
            </div>
            <div class="tarjeta-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        TARJETA;
      }
    } else {
      $tarjetas = '<p>No hay productos registrados.</p>';
    }

    $volverUrl = $categoriaFiltro
      ? RUTA_VISTAS . '/productos/categoriaslist.php'
      : RUTA_APP . '/index.php';

    $btnCrearNuevo = '';
    if ($esGerente) {
      $crearUrl = RUTA_VISTAS . '/productos/productosdetail.php';
      $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Crear nuevo</a>";
    }

    return <<<EOS
    <section id="contenido">
        <h2>Lista de productos</h2>
        <div class="lista-productos">
            {$tarjetas}
        </div>
        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$btnCrearNuevo}
        </div>
    </section>
    EOS;
  }

  private static function generarHtmlImagenes(int $productoId, string $nombre): string
  {
    $imagenes = ProductoService::listarImagenes($productoId);

    if (!$imagenes) {
      return "<div class=\"tarjeta-sin-imagen\"><em>Sin imagen</em></div>";
    }

    $primeraRuta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);

    if (count($imagenes) === 1) {
      return "<img class=\"tarjeta-img-unica\" src=\"{$primeraRuta}\" alt=\"{$nombre}\">";
    }

    // Slider para múltiples imágenes
    $rutas = array_map(fn($img) => htmlspecialchars(RUTA_APP . $img['ruta_imagen']), $imagenes);
    $dataImagenes = htmlspecialchars(json_encode($rutas));

    $dotsHtml = '';
    foreach ($imagenes as $i => $img) {
      $active = $i === 0 ? ' active' : '';
      $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
    }

    return '<div class="slider-wrap tarjeta-slider" data-imagenes="' . $dataImagenes . '" data-auto="true">'
      . '<img class="slider-img" src="' . $primeraRuta . '" alt="' . $nombre . '">'
      . '<div class="slider-dots">' . $dotsHtml . '</div>'
      . '</div>';
  }
}