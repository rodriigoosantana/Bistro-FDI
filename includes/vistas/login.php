<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/login/formularioLogin.php';

$form = new FormularioLogin();

$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Login';
$tituloHeader = 'Login';

$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>Acceso al sistema</h2>
   $htmlFormLogin
   </section>
EOS;

require("common/plantilla.php");
