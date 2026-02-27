<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/vistas/productos/FormularioProducto.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php'); #Si no manda al login
    exit();
}

// Verificar rol gerente
if ($_SESSION['rolId'] !== Usuario::ROL_GERENTE) {
    header('Location: ' . RUTA_APP . '/index.php'); #Si no manda al index
    exit();
}

// Comprobar si es edición (parámetro id en URL)
$producto = null;
if (isset($_GET['id'])) {
    $producto = ProductoService::buscarPorId(intval($_GET['id']));
    if (!$producto) {
        header('Location: ' . RUTA_VISTAS . '/productoslist.php');
        exit();
    }
}

$form = new FormularioProducto($producto); #Se crea el formulario, con el producto si existe o null si no
$htmlFormProducto = $form->gestiona();

$tituloPagina = $producto ? 'Editar producto' : 'Nuevo producto'; #Si el producto existe, se marca como editar, si no, como nuevo
$tituloHeader = $producto ? 'Editar producto' : 'Nuevo producto';

$volverUrl = RUTA_VISTAS . '/productoslist.php'; #Se define la URL de la lista de productos

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>{$tituloPagina}</h2>
        {$htmlFormProducto}
        <br>
        <a href="{$volverUrl}" class="btn btn-volver">← Volver a la lista</a>
    </section>
EOS;

require("common/plantilla.php");
?>
