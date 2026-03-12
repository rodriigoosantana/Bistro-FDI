<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\login\FormularioRegistro;

$form = new FormularioRegistro();

$htmlFormRegistro = $form->gestiona();

$tituloPagina = 'Registro';
$tituloHeader = 'Registro';
$contenidoPrincipal = <<<EOS
   <section id="contenido">
         <h2>Registro de usuario</h2>
         $htmlFormRegistro
   </section>
EOS;

$loginUrl = RUTA_VISTAS . '/login.php';
$contenidoAside = <<<ASIDE
<section>
    <h3>Crear cuenta</h3>
    <p class="aside-mensaje">Completa el formulario para unirte a Bistro FDI.</p>
</section>
<a href="{$loginUrl}" class="aside-link">¿Ya tienes cuenta? Inicia sesión</a>
ASIDE;

require("common/plantilla.php");
