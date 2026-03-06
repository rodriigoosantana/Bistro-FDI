<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);

$productos = ProductoService::listarTodos(); # Obtener lista de productos

$categorias = CategoriaService::listarTodas();#Obtener categorías para mostrar nombres

$mapaCategorias = []; # Crear mapa id => nombre de categoría para mostrar en tabla
if ($categorias) {
    foreach ($categorias as $cat) {
        $mapaCategorias[$cat->getId()] = $cat->getNombre();
    }
}

$tarjetas = '';
if ($productos && count($productos) > 0) {
    foreach ($productos as $p) {
        $nombre      = htmlspecialchars($p->getNombre());
        $nombreCat   = htmlspecialchars($mapaCategorias[$p->getCategoriaId()] ?? 'Sin categoría');
        $precioFinal = number_format($p->getPrecioFinal(), 2, ',', '.');
        $verUrl      = RUTA_VISTAS . '/productosdetail.php?id=' . $p->getId();
        $disponible  = $p->isDisponible() ? '' : '<small>(No disponible)</small>';

        // Primera imagen del producto si tiene
        $imagenes = ProductoService::listarImagenes($p->getId());
        if ($imagenes) {
            $primeraRuta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
            if (count($imagenes) > 1) {
                // Slider automático en tarjeta
                $rutas = array_map(function($img) {
                    return htmlspecialchars(RUTA_APP . $img['ruta_imagen']);
                }, $imagenes);
                $dataImagenes = htmlspecialchars(json_encode($rutas));

                $dotsHtml = '';
                foreach ($imagenes as $i => $img) {
                    $active    = $i === 0 ? ' active' : '';
                    $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
                }

                $htmlImg = <<<SLDR
                <div class="slider-wrap tarjeta-slider" data-imagenes="{$dataImagenes}" data-auto="true">
                    <img class="slider-img" src="{$primeraRuta}" alt="{$nombre}">
                    <div class="slider-dots">{$dotsHtml}</div>
                </div>
                SLDR;
            } else {
                // Una sola imagen, sin slider
                $htmlImg = "<img class=\"tarjeta-img-unica\" src=\"{$primeraRuta}\" alt=\"{$nombre}\">";
            }
        } else {
            $htmlImg = "<div class=\"tarjeta-sin-imagen\"><em>Sin imagen</em></div>";
        }

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

// Botón crear solo para gerente
$btnCrearNuevo = '';
if ($esGerente) {
    $crearUrl      = RUTA_VISTAS . '/productosdetail.php';
    $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Crear nuevo</a>";
}

$volverUrl    = RUTA_APP . '/index.php';
$tituloPagina = 'Lista de Productos';
$tituloHeader = 'Lista de Productos';

$contenidoPrincipal = <<<EOS
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

require('common/plantilla.php');
?>
