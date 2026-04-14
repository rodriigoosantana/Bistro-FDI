<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Producto\Categoria;

class GenerarDetalleCategoria
{
  public static function generar(Categoria $categoria, string $volverUrl, bool $esGerente): string
  {
    $nombre      = htmlspecialchars($categoria->getNombre());
    $descripcion = htmlspecialchars($categoria->getDescripcion());
    $activa      = $categoria->isActiva() ? 'Activa' : 'Inactiva';

    // Imagen o placeholder
    if ($categoria->getImagen()) {
      $rutaImagen = RUTA_APP . htmlspecialchars($categoria->getImagen());
      $htmlImagen = "<img src=\"{$rutaImagen}\" alt=\"{$nombre}\" width=\"150\" />";
    } else {
      $htmlImagen = "<div class=\"img-placeholder\">📷<br>Sin imagen</div>";
    }

    // Botones de gerente
    $botonesGerente = '';
    if ($esGerente) {
      $editarUrl = RUTA_VISTAS . '/productos/categoriasdetail.php?id=' . $categoria->getId() . '&editar=1';
      $botonesGerente = "<a href=\"{$editarUrl}\" class=\"btn btn-editar\">Modificar</a> ";

      if ($categoria->isActiva()) {
        $botonesGerente .= <<<BTN
                <form method="POST" action="" style="display:inline"
                     data-confirm="¿Desactivar esta categoría? Sus productos también se desactivarán.">
                    <input type="hidden" name="accion" value="desactivar">
                    <button type="submit" class="btn btn-borrar">Desactivar</button>
                </form>
                BTN;
      } else {
        $botonesGerente .= <<<BTN
                <form method="POST" action="" style="display:inline">
                    <input type="hidden" name="accion" value="reactivar">
                    <button type="submit" class="btn btn-nuevo">Reactivar</button>
                </form>
                BTN;
      }
    }

    return <<<EOS
    <section id="contenido">
        <h2>Ver Categoría</h2>
        <div class="detalle-categoria">
            <div class="detalle-imagen">
                {$htmlImagen}
            </div>
            <div class="detalle-info">
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Descripción:</strong> {$descripcion}</p>
                <p><strong>Activa:</strong> {$activa}</p>
            </div>
        </div>
        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$botonesGerente}
        </div>
    </section>
    EOS;
  }
}
?>