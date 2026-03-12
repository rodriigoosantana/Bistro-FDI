<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\login\formularioLogin;

$form = new formularioLogin();

$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Login';
$tituloHeader = 'Login';

$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>Acceso al sistema</h2>
   $htmlFormLogin
   </section>
EOS;

$registroUrl = RUTA_VISTAS . '/registro.php';
$contenidoAside = <<<ASIDE
<section>
    <h3>Bienvenido</h3>
    <p class="aside-mensaje">Accede al sistema para gestionar productos, pedidos y mucho más.</p>
</section>
<a href="{$registroUrl}" class="aside-link">¿No tienes cuenta? Regístrate</a>
ASIDE;

require("common/plantilla.php");
