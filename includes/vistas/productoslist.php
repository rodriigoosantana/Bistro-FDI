<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Producto/Categoria.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

// Verificar rol gerente
if ($_SESSION['rolId'] !== Usuario::ROL_GERENTE) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

// Obtener lista de productos
$productos = ProductoService::listarTodos();

// Obtener categorías para mostrar nombres
$categorias = CategoriaService::listarTodas();
$mapaCategorias = [];
if ($categorias) {
    foreach ($categorias as $cat) {
        $mapaCategorias[$cat->getId()] = $cat->getNombre();
    }
}

// Generar filas de la tabla
$filasTabla = '';
if ($productos && count($productos) > 0) {
    foreach ($productos as $p) {
        $nombreCat = htmlspecialchars($mapaCategorias[$p->getCategoriaId()] ?? 'Sin categoría');
        $nombre = htmlspecialchars($p->getNombre());
        $descripcion = htmlspecialchars($p->getDescripcion());
        $precioBase = number_format($p->getPrecioBase(), 2, ',', '.');
        $precioFinal = number_format($p->getPrecioFinal(), 2, ',', '.');
        $iva = $p->getIva();

        $disponible = $p->isDisponible()
            ? '<span class="badge badge-si">Sí</span>'
            : '<span class="badge badge-no">No</span>';
        $ofertado = $p->isOfertado()
            ? '<span class="badge badge-si">Sí</span>'
            : '<span class="badge badge-no">No</span>';
        $activo = $p->isActivo()
            ? '<span class="badge badge-si">Sí</span>'
            : '<span class="badge badge-no">No</span>';

        $editUrl = RUTA_VISTAS . '/productosdetail.php?id=' . $p->getId();

        $filasTabla .= <<<FILA
        <tr>
            <td>{$nombre}</td>
            <td>{$nombreCat}</td>
            <td>{$precioBase} €</td>
            <td>{$iva}%</td>
            <td><strong>{$precioFinal} €</strong></td>
            <td>{$disponible}</td>
            <td>{$ofertado}</td>
            <td>{$activo}</td>
            <td><a href="{$editUrl}" class="btn btn-editar">Editar</a></td>
        </tr>
FILA;
    }
}
else {
    $filasTabla = '<tr><td colspan="9">No hay productos registrados.</td></tr>';
}

$tituloPagina = 'Productos';
$tituloHeader = 'Gestión de Productos';

$nuevoUrl = RUTA_VISTAS . '/productosdetail.php';

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Lista de productos</h2>

        <div class="acciones-tabla">
            <a href="{$nuevoUrl}" class="btn btn-nuevo">+ Nuevo producto</a>
        </div>

        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio base</th>
                    <th>IVA</th>
                    <th>Precio final</th>
                    <th>Disponible</th>
                    <th>Ofertado</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {$filasTabla}
            </tbody>
        </table>
    </section>
EOS;

require("common/plantilla.php");
?>
