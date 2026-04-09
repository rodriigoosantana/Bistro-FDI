<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Producto\Categoria;

class GenerarListaCategorias
{
  public static function generar(array $categorias, bool $esGerente): string
  {
    $filasLista = '';

    if ($categorias && count($categorias) > 0) {
      foreach ($categorias as $cat) {
        $nombre      = htmlspecialchars($cat->getNombre());
        $descripcion = htmlspecialchars($cat->getDescripcion());

        // Truncar la descripción a 80 caracteres para mostrar en la lista
        if (strlen($descripcion) > 80) {
          $descripcion = substr($descripcion, 0, 80) . '...';
        }

        $badgeEstado = '';
        $estiloItem  = '';
        if (!$cat->isActiva()) {
          $badgeEstado = '<span style="color:red; font-size:0.9em;">[Inactiva]</span>';
          $estiloItem  = 'style="opacity:0.6;"';
        }

        $productosUrl = RUTA_VISTAS . '/productos/productoslist.php?categoria=' . $cat->getId();
        $htmlImagen   = self::generarHtmlImagen($cat, $nombre, $productosUrl);

        $verUrl = RUTA_VISTAS . '/productos/categoriasdetail.php?id=' . $cat->getId();

        $filasLista .= <<<FILA
        <div class="categoria-item" {$estiloItem}>
            <div class="categoria-imagen">
                {$htmlImagen}
            </div>
            <div class="categoria-info">
                <strong class="categoria-nombre">{$nombre} {$badgeEstado}</strong>
                <span class="categoria-descripcion">{$descripcion}</span>
            </div>
            <div class="categoria-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        FILA;
      }
    } else {
      $filasLista = '<p>No hay categorías disponibles.</p>';
    }

    $volverUrl = RUTA_APP . '/index.php';

    $btnCrearNueva = '';
    if ($esGerente) {
      $crearUrl      = RUTA_VISTAS . '/productos/categoriasdetail.php';
      $btnCrearNueva = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">+ Crear nueva</a>";
    }

    return <<<EOS
    <section id="contenido">
        <h2>Lista de categorías</h2>
        <div class="lista-categorias">
            {$filasLista}
        </div>
        <div class="acciones-pie">
            <a href="{$volverUrl}" class="btn btn-volver">← Atrás</a>
            {$btnCrearNueva}
        </div>
    </section>
    EOS;
  }

  private static function generarHtmlImagen(Categoria $cat, string $nombre, string $productosUrl): string
  {
    if ($cat->getImagen()) {
      $rutaImagen = RUTA_APP . htmlspecialchars($cat->getImagen());
      return "<a href=\"{$productosUrl}\"><img src=\"{$rutaImagen}\" alt=\"{$nombre}\" class=\"categoria-img\" /></a>";
    }
    return "<a href=\"{$productosUrl}\"><div class=\"img-placeholder\">📷<br>Sin imagen</div></a>";
  }
}
?>