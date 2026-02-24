<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/login/FormularioRegistro.php';

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

require("common/plantilla.php");

?>
