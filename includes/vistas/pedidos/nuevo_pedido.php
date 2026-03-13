<?php

use es\ucm\fdi\aw\vistas\pedidos\FormularioPedido;

require_once dirname(__DIR__, 3) . '/includes/config.php';

$form = new FormularioPedido();

$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Nuevo Pedido';
$tituloHeader = 'Nuevo Pedido';

$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>Nuevo Pedido</h2>
   $htmlFormLogin
   </section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
