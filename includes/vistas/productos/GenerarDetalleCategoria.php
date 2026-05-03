<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Producto\Categoria;

class GenerarDetalleCategoria
{
  public static function generar(Categoria $categoria, string $volverUrl, bool $esGerente): string
  {
    $htmlInfo       = self::generarSeccionInfo($categoria);
    $botonesGerente = self::generarBotonesGerente($categoria, $esGerente);

    return <<<EOS
    <section id="contenido">
        <h2>Ver Categoría</h2>
        <div class="detalle-producto">
            <div class="detalle-info">
                {$htmlInfo}
            </div>
        </div>
        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$botonesGerente}
        </div>
    </section>
    EOS;
  }

  private static function generarSeccionInfo(Categoria $categoria): string
  {
    $nombre      = htmlspecialchars($categoria->getNombre());
    $descripcion = htmlspecialchars($categoria->getDescripcion());
    $activa      = $categoria->isActiva() ? 'Sí' : 'No';
    $preparacion = $categoria->necesitaPreparacion() ? 'Sí' : 'No';

    $htmlImagen = '';
    if ($categoria->getImagen()) {
      $rutaImg    = htmlspecialchars(RUTA_APP . $categoria->getImagen());
      $htmlImagen = "<p><img src=\"{$rutaImg}\" alt=\"{$nombre}\" style=\"max-width:200px;\"></p>";
    }

    return <<<EOS
    {$htmlImagen}
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>
    <p><strong>Activa:</strong> {$activa}</p>
    <p><strong>Necesita preparación:</strong> {$preparacion}</p>
    EOS;
  }

  private static function generarBotonesGerente(Categoria $categoria, bool $esGerente): string
  {
    if (!$esGerente) {
      return '';
    }

    $editarUrl    = RUTA_VISTAS . '/productos/categoriasdetail.php?id=' . $categoria->getId() . '&editar=1';
    $accionEstado = $categoria->isActiva() ? 'desactivar' : 'reactivar';
    $textoEstado  = $categoria->isActiva() ? 'Desactivar' : 'Reactivar';
    $claseEstado  = $categoria->isActiva() ? 'btn btn-borrar' : 'btn btn-editar';

    return <<<BTN
    <a href="{$editarUrl}" class="btn btn-editar">Modificar</a>
    <form method="POST" action="" style="display:inline">
        <input type="hidden" name="accion" value="{$accionEstado}">
        <button type="submit" class="{$claseEstado}">{$textoEstado}</button>
    </form>
    BTN;
  }
}
