<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/pedidos/FormularioPedido.php';

$form = new FormularioPedido();

$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Nuevo Pedido';
$tituloHeader = 'Nuevo Pedido';

$contenidoPrincipal=<<<EOS
   <section id="contenido">
   <h2>Nuevo Pedido</h2>
   $htmlFormLogin
   </section>
EOS;

require("common/plantilla.php");
?>
