<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Usuario\Rol;

$nombreUsuario = $_GET['nombreUsuario'] ?? null;
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

if (isset($_POST['eliminar'])) {
  UsuarioService::eliminar($usuario);
  if ($nombreUsuario == $_SESSION['nombreUsuario']) {
    header("Location: logout.php");
    exit();
  } else {
    header("Location: listaUsuarios.php");
    exit();
  }
}

if (isset($_POST['cancelar'])) {
  header("Location: perfilUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}");
  exit();
}


$eliminarButton = <<<HTML
  <form method="POST">
    <div>
        <button type="submit" name="eliminar">
            Si
        </button>
    </div>
</form>
HTML;


$cancelarButton = <<<HTML
  <form method="POST">
    <div>
        <button type="submit" name="cancelar">
            No
        </button>
    </div>
</form>
HTML;

$tituloPagina = 'Eliminar Usuario';
$tituloHeader = 'Eliminar Usuario';
$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>Eliminar</h2>
   <p> Se van a eliminar los datos de {$usuario->getNombreUsuario()} de forma permanente. ¿Deseas continuar?</p>
   $eliminarButton
   $cancelarButton
   </section>
EOS;

require("common/plantilla.php");
