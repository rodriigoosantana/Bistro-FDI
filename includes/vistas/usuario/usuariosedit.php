<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\usuario\FormularioRegistro;
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

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
