<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

# Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);

$categorias = CategoriaService::listarTodas(); # Obtener lista de categorías

# Generar filas de la lista
$filasLista = '';
if ($categorias && count($categorias) > 0) {
    foreach ($categorias as $cat) {
        $nombre = htmlspecialchars($cat->getNombre());
        $descripcion = htmlspecialchars($cat->getDescripcion());
        
        #Truncar la descripción a 80 caracteres para mostrar en la tabla
        if(strlen($descripcion) > 80) {
            $descripcion = substr($descripcion, 0, 80) . '...';
        }

        # Imagen o placeholder si no tiene imagen
        if ($cat->getImagen()) {
            $rutaImagen = RUTA_APP . htmlspecialchars($cat->getImagen());
            $htmlImagen = "<img src=\"{$rutaImagen}\" alt=\"{$nombre}\" class=\"categoria-img\" />";
        } else {
            $htmlImagen = "<div class=\"img-placeholder\">📷<br>Sin imagen</div>";
        }

        $verURL = RUTA_VISTAS . '/categoriasdetail.php?id=' . $cat->getId();

        $filasLista .= <<<FILA
        <div class="categoria-item">
            <div class="categoria-imagen">
            {$htmlImagen}
            </div>
            <div class="categoria-info">
                <strong class="categoria-nombre">{$nombre}</strong>
                <span class="categoria-descripcion">{$descripcion}</span>
            </div>
            <div class="categoria-acciones">
                <a href="{$verURL}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        FILA;
    }
} else {
    $filasLista = '<p>No hay categorías disponibles.</p>';
}

$tituloPagina = 'Categorías';
$tituloHeader = 'Gestión de Categorías';

$volverUrl  = RUTA_APP . '/index.php';
$btnCrearNueva = '';
if ($esGerente) {   # Solo un gerente puede crear nuevas categorías
    $crearUrl = RUTA_VISTAS . '/categoriasdetail.php';
    $btnCrearNueva = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">+ Crear nueva</a>";
}

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Lista de categorías</h2>

        <div class="lista-categorias">
            {$filasLista}
        </div>

        <div class="acciones-pie">
            <a href="{$volverUrl}" class="btn btn-volver">← Atrás</a>
            {$btnCrearNueva}
        </div>
    </section>
EOS;

require("common/plantilla.php");
?>