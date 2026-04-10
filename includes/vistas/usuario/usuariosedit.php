<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\login\FormularioRegistro;
use es\ucm\fdi\aw\Usuario\UsuarioService;

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
