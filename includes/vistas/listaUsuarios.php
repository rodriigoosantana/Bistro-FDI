<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\login\listarUsuarios;
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';
$filas = "";
$acceso = Aplicacion::getInstance()::puedeListarUsuarios();

if ($acceso) {
  $filas = listarUsuarios::listarUsuarios();
}

$contenidoPrincipal = <<<EOS
<section id="contenido">

<h2>Usuarios</h2>

<div class="lista-categorias">
$filas
</div>

</section>
EOS;

require("common/plantilla.php");
