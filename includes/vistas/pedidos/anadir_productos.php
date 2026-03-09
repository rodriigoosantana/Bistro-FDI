<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/pedidos/FormularioAnadirProductos.php';

$pedidoId = $_GET['id'] ?? null;

if (!$pedidoId) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$form = new FormularioAnadirProductos($pedidoId);
$htmlForm = $form->gestiona();

$tituloPagina = 'Añadir Productos';
$tituloHeader = 'Añadir Productos';

$contenidoPrincipal=<<<EOS
   <section id="contenido">
   <h2>Añadir Productos al Pedido</h2>
   $htmlForm
   </section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
?>
