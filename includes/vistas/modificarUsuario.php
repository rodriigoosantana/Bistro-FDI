<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/login/FormularioRegistro.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';

$nombreUsuarioModificar = $_GET['nombreUsuario'] ?? null;
$usuario = UsuarioService::buscarPorNombre($nombreUsuarioModificar);

$form = new FormularioRegistro($usuario);

$htmlForm = $form->gestiona();

$tituloPagina = 'Modificar';
$tituloHeader = 'Modificar Usuario';
$contenidoPrincipal = <<<EOS
   <section id="contenido">
         <h2>Modificación de usuario</h2>
         $htmlForm
   </section>
EOS;

require("common/plantilla.php");
