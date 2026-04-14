<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\usuario\GenerarListaUsuarios;
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';
$filas = "";
$acceso = Aplicacion::getInstance()::puedeListarUsuarios();

if ($acceso) {
  $filas = GenerarListaUsuarios::listarUsuarios();
}

$contenidoPrincipal = <<<EOS
<section id="contenido">

<h2>Usuarios</h2>

<div class="lista-categorias">
$filas
</div>

</section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
