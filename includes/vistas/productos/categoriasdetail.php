<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\vistas\productos\FormularioCategoria;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\productos\GenerarDetalleCategoria;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
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
    header('Location: ' . RUTA_VISTAS . '/productos/categoriaslist.php');
    exit();
  }
}

$volverUrl = RUTA_VISTAS . '/productos/categoriaslist.php';

# BORRADO (solo gerente, acción POST)
if ($esGerente && isset($_POST['accion']) && $categoria) {
  $accion = $_POST['accion'];
  if ($accion === 'desactivar') {
    CategoriaService::cambiarEstado($categoria->getId(), false);
    header('Location:' . RUTA_VISTAS . '/productos/categoriaslist.php');
    exit();
  } elseif ($accion === 'reactivar') {
    CategoriaService::cambiarEstado($categoria->getId(), true);
    header('Location: ' . RUTA_VISTAS . '/productos/categoriaslist.php?id=' . $categoria->getId());
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
  $tituloPagina = $categoria->getNombre();
  $tituloHeader = 'Ver categoría';
  $contenidoPrincipal = GenerarDetalleCategoria::generar($categoria, $volverUrl, $esGerente);
  }

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
?>
