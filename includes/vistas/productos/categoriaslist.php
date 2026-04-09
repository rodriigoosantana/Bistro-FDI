<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\vistas\productos\GenerarListaCategorias;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente = Aplicacion::esGerente();

# El gerente puede ver todas las categorías, el resto solo las activas
$categorias = $esGerente? CategoriaService::listarTodas() : CategoriaService::listarActivas();

$tituloPagina = 'Categorías';
$tituloHeader = 'Gestión de Categorías';
$contenidoPrincipal = GenerarListaCategorias::generar($categorias, $esGerente);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
