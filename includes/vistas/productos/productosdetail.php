<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\vistas\productos\FormularioProducto;
use es\ucm\fdi\aw\vistas\productos\GenerarDetalleProducto;

if (!Aplicacion::estaLogueado()) {
  header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
  exit();
}

$esGerente = Aplicacion::esGerente();

// Sin id solo puede entrar el gerente (para crear)
if (!isset($_GET['id']) && !$esGerente) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

// Comprobar si es edición (parámetro id en URL)
$producto = null;
if (isset($_GET['id'])) {
  $producto = ProductoService::buscarPorId(intval($_GET['id']));
  if (!$producto) {
    header('Location: ' . RUTA_VISTAS . '/productos/productoslist.php');
    exit();
  }
}

$categoriaOrigen = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$volverUrl = RUTA_VISTAS . '/productos/productoslist.php' . ($categoriaOrigen ? '?categoria=' . $categoriaOrigen : '');

/* BORRADO (Solo gerente, acción POST) [QUITADO PARA EVITAR CONFLICTOS, SOLO BORRADO LÓGICO]
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'borrar' && $producto) {
  ProductoService::eliminar($producto->getId());
  header('Location: ' . RUTA_VISTAS . '/productos/productoslist.php');
  exit();
} */

#TOGGLE ACTIVO/INACTIVO (Solo gerente, acción POST)
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'toggleActivo' && $producto) {
  ProductoService::cambiarEstado($producto->getId(), !$producto->isActivo());
  header('Location: ' . RUTA_VISTAS . '/productos/productosdetail.php?id=' . $producto->getId());
  exit();
}


# MODO EDICIÓN: (Solo gerente, pulsó "Modificar" o hay error de formulario)
$modoEdicion = ($esGerente && (isset($_GET['editar']) || isset($_POST['formId'])));

if ($esGerente && ($modoEdicion || !$producto)) {
  require_once RAIZ_APP . '/includes/vistas/productos/FormularioProducto.php';

  $form = new FormularioProducto($producto); #Se crea el formulario, con el producto si existe o null si no
  $htmlContenido = $form->gestiona();

  $tituloPagina = $producto ? 'Editar producto' : 'Nuevo producto'; #Si el producto existe, se marca como editar, si no, como nuevo
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
  #MODO VISTA (Para todos los usuarios)
  $tituloPagina = htmlspecialchars($producto->getNombre());
  $tituloHeader = 'Ver producto';

  $contenidoPrincipal = GenerarDetalleProducto::generar($producto, $volverUrl, $esGerente);
}

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
