<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\vistas\productos\GenerarListaProductos;

if (!Aplicacion::estaLogueado()) {
  header('Location: ' . RUTA_VISTAS . '/login.php');
  exit();
}

$esGerente = Aplicacion::esGerente();
$categoriaFiltro = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

if ($categoriaFiltro) { # Si hay filtro de categoría, mostrar solo productos de esa categoría
  $productos = $esGerente # Si es gerente, mostrar tanto productos activos como inactivos
    ? ProductoService::listarPorCategoria($categoriaFiltro)
    : ProductoService::listarActivosPorCategoria($categoriaFiltro);
} else {
  $productos = $esGerente
    ? ProductoService::listarTodos()
    : ProductoService::listarActivos();
}

// Título de contexto si hay filtro activo
$tituloCat = '';
if ($categoriaFiltro) {
  $cat = CategoriaService::buscarPorId($categoriaFiltro);
  $tituloCat = $cat ? ' — ' . htmlspecialchars($cat->getNombre()) : '';
}

$categorias = CategoriaService::listarTodas(); #Obtener categorías para mostrar nombres

$mapaCategorias = []; # Crear mapa id => nombre de categoría para mostrar en tabla
if ($categorias) {
  foreach ($categorias as $cat) {
    $mapaCategorias[$cat->getId()] = $cat->getNombre();
  }
}

$tituloPagina = 'Lista de Productos';
$tituloHeader = 'Lista de Productos';

$contenidoPrincipal = GenerarListaProductos::generar($productos, $categoriaFiltro, $esGerente, $mapaCategorias);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
