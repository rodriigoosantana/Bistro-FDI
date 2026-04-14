<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\recompensas\GenerarListaRecompensas;
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Lista Recompensas';
$tituloHeader = 'Lista Recompensas';

$esGerente = Aplicacion::esGerente();
$filas = GenerarListaRecompensas::listarRecompensas($esGerente);

$contenidoPrincipal = <<<EOS
<section id="contenido">

<h2>Recompensas</h2>

<div class="lista-categorias">
$filas
</div>

</section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
