<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
#require_once RAIZ_APP . '/includes/vistas/productos/FormularioProducto.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php'); #Si no manda al login
    exit();
}

$esGerente = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);

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
        header('Location: ' . RUTA_VISTAS . '/productoslist.php');
        exit();
    }
}

$volverUrl = RUTA_VISTAS . '/productoslist.php'; #Ruta de retorno a la lista de productos

#BORRADO (Solo gerente, acción POST)
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'borrar' && $producto) {
    ProductoService::eliminar($producto->getId());
    header('Location: ' . RUTA_VISTAS . '/productoslist.php');
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
    $categoria = CategoriaService::buscarPorId($producto->getCategoriaId());
    $nombreCat = $categoria ? htmlspecialchars($categoria->getNombre()) : 'Sin categoría';
    $nombre = htmlspecialchars($producto->getNombre());
    $descripcion = htmlspecialchars($producto->getDescripcion());
    $precioBase = number_format($producto->getPrecioBase(), 2, ',', '.');
    $precioFinal = number_format($producto->getPrecioFinal(), 2, ',', '.');
    $iva = $producto->getIva();
    $disponible = $producto->isDisponible() ? 'Sí' : 'No';
    $ofertado = $producto->isOfertado() ? 'Sí' : 'No';
    $activo = $producto->isActivo() ? 'Sí' : 'No';

    // Imágenes del producto
    $imagenes = ProductoService::listarImagenes($producto->getId());
    $htmlImagenes = '<em>Sin imágenes</em>';

    if ($imagenes) {
        // Array de rutas para el atributo data-imagenes (JSON)
        $rutas = array_map(function ($img) {
            return htmlspecialchars(RUTA_APP . $img['ruta_imagen']);
        }, $imagenes);
        $dataImagenes = htmlspecialchars(json_encode($rutas));

        $primeraRuta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);

        // Puntos de navegación (solo si hay más de 1 imagen)
        $dotsHtml = '';
        if (count($imagenes) > 1) {
            foreach ($imagenes as $i => $img) {
                $active = $i === 0 ? ' active' : '';
                $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
            }
            $dotsHtml = "<div class=\"slider-dots\">{$dotsHtml}</div>";
        }

        $htmlImagenes = '<div class="slider-wrap" data-imagenes="' . $dataImagenes . '" data-auto="true">'
            . '<img class="slider-img" src="' . $primeraRuta . '" alt="' . $nombre . '">'
            . $dotsHtml
            . '</div>';
    } else {
        $htmlImagenes = '<em>Sin imágenes</em>';
    }

    // Botones de gerente
    $botonesGerente = '';
    if ($esGerente) {
        $editarUrl = RUTA_VISTAS . '/productosdetail.php?id=' . $producto->getId() . '&editar=1';
        $botonesGerente = <<<BTN
        <a href="{$editarUrl}" class="btn btn-editar">Modificar</a>
        <form method="POST" action="" style="display:inline" id="formBorrar">
            <input type="hidden" name="accion" value="borrar">
            <button type="submit" class="btn btn-borrar">Borrar</button>
        </form>
        BTN;
    }

    $tituloPagina = $nombre;
    $tituloHeader = 'Ver producto';

    $contenidoPrincipal = <<<EOS
              <section id="contenido">
            <h2>Ver Producto</h2>

            <div class="detalle-producto">
                <div class="detalle-imagenes">
                    {$htmlImagenes}
                </div>
                <div class="detalle-info">
                    <p><strong>Nombre:</strong> {$nombre}</p>
                    <p><strong>Descripción:</strong> {$descripcion}</p>
                    <p><strong>Categoría:</strong> {$nombreCat}</p>
                    <p><strong>Precio:</strong> {$precioFinal} €</p>
                    <p><strong>IVA:</strong> {$iva}%</p>
                    <p><strong>Disponible:</strong> {$disponible}</p>
                    <p><strong>Ofertado:</strong> {$ofertado}</p>
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