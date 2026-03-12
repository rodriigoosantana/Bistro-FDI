<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Usuario\Usuario;

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
    if (strlen($descripcion) > 80) {
      $descripcion = substr($descripcion, 0, 80) . '...';
    }

    $badgeEstado = '';
    $estiloItem = '';
    if (!$cat->isActiva()) {
      $badgeEstado = '<span style="color:red; font-size:0.9em;">[Inactiva]</span>';
      $estiloItem = 'style=opacity:0.6;'; #Apariencia atenuada para categorías inactivas
    }

    # Imagen o placeholder si no tiene imagen
    $productosUrl = RUTA_VISTAS . '/productoslist.php?categoria=' . $cat->getId();
    if ($cat->getImagen()) {
      $rutaImagen = RUTA_APP . htmlspecialchars($cat->getImagen());
      $htmlImagen = "<a href=\"{$productosUrl}\"><img src=\"{$rutaImagen}\" alt=\"{$nombre}\" class=\"categoria-img\" /></a>";
    } else {
      $htmlImagen = "<a href=\"{$productosUrl}\"><div class=\"img-placeholder\">📷<br>Sin imagen</div></a>";
    }

    $verURL = RUTA_VISTAS . '/categoriasdetail.php?id=' . $cat->getId();

    $filasLista .= <<<FILA
        <div class="categoria-item" {$estiloItem}>
            <div class="categoria-imagen">
            {$htmlImagen}
            </div>
            <div class="categoria-info">
                <strong class="categoria-nombre">{$nombre}{$badgeEstado}</strong>
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
