<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\vistas\productos\FormularioCategoria;
use es\ucm\fdi\aw\Aplicacion;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}
$esGerente = Aplicacion::esGerente();

# Sin id solo puede entrar el gerente (para crear)
if (!isset($_GET['id']) && !$esGerente) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

# Comprobar si es edición (parámetro id en URL)
$categoria = null;
if (isset($_GET['id'])) {
  $categoria = CategoriaService::buscarPorId(intval($_GET['id']));
  if (!$categoria) {
    header('Location: ' . RUTA_VISTAS . '/categoriaslist.php');
    exit();
  }
}

$volverUrl = RUTA_VISTAS . '/categoriaslist.php';

# BORRADO (solo gerente, acción POST)
if ($esGerente && isset($_POST['accion']) && $categoria) {
  $accion = $_POST['accion'];
  if ($accion === 'desactivar') {
    CategoriaService::cambiarEstado($categoria->getId(), false);
    header('Location:' . RUTA_VISTAS . '/categoriaslist.php');
    exit();
  } elseif ($accion === 'reactivar') {
    CategoriaService::cambiarEstado($categoria->getId(), true);
    header('Location: ' . RUTA_VISTAS . '/categoriaslist.php?id=' . $categoria->getId());
    exit();
  }
}

# MODO EDICIÓN (solo gerente, pulsó "Modificar" o hay error de formulario)
$modoEdicion = ($esGerente && (isset($_GET['editar']) || isset($_POST['formId'])));

if ($esGerente && ($modoEdicion || !$categoria)) {
  require_once RAIZ_APP . '/includes/vistas/productos/formularioCategoria.php';

  $form = new FormularioCategoria($categoria);
  $htmlContenido = $form->gestiona();

  $tituloPagina = $categoria ? 'Editar categoría' : 'Nueva categoría';
  $tituloHeader = $tituloPagina;

  $contenidoPrincipal = <<<EOS
        <section id="contenido">
            <h2>{$tituloPagina}</h2>
            {$htmlContenido}
            <br>
            <a href="{$volverUrl}" class="btn btn-volver">← Volver a la lista</a>
        </section>
    EOS;
} else {
  # MODO VISTA (para todos los usuarios)
  $nombre      = htmlspecialchars($categoria->getNombre());
  $descripcion = htmlspecialchars($categoria->getDescripcion());
  $activa      = $categoria->isActiva() ? 'Sí' : 'No';

  # Imagen
  $htmlImagen = '<em>Sin imagen</em>';
  if ($categoria->getImagen()) {
    $rutaImagen = RUTA_APP . htmlspecialchars($categoria->getImagen());
    $htmlImagen = "<img src=\"{$rutaImagen}\" alt=\"{$nombre}\" width=\"150\" />";
  } else {
    $htmlImagen = "<div class=\"img-placeholder\">📷<br>Sin imagen</div>";
  }

  # Botones de gerente
  $botonesGerente = '';
  if ($esGerente) {
    $editarUrl = RUTA_VISTAS . '/categoriasdetail.php?id=' . $categoria->getId() . '&editar=1';
    $botonesGerente = "<a href=\"{$editarUrl}\" class=\"btn btn-editar\">Modificar</a> ";
    if ($categoria->isActiva()) {   # Solo mostrar botón de desactivar si la categoría está activa
      $botonesGerente .= <<<BTN
            <form method="POST" action="" style="display:inline"
                  onsubmit="return confirm('¿Desactivar esta categoría? Sus productos también se desactivarán.')"> 
                <input type="hidden" name="accion" value="desactivar">
                <button type="submit" class="btn btn-borrar">Desactivar</button>
            </form>
            BTN;
    } else { # Si la categoría está inactiva, mostrar botón de reactivar
      $botonesGerente .= <<<BTN
            <form method="POST" action="" style="display:inline">
                <input type="hidden" name="accion" value="reactivar">
                <button type="submit" class="btn btn-nuevo">Reactivar</button>
            </form>
            BTN;
    }
  }

  $tituloPagina = $nombre;
  $tituloHeader = 'Ver categoría';

  $contenidoPrincipal = <<<EOS
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

require('common/plantilla.php');
?>
