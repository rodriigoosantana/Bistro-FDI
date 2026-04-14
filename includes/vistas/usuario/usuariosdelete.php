<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;

$nombreUsuario = $_GET['nombreUsuario'] ?? null;
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

if (isset($_POST['eliminar'])) {
  UsuarioService::eliminar($usuario);
  if ($nombreUsuario == $_SESSION['nombreUsuario']) {
    header("Location: " . RUTA_VISTAS . '/usuario/logout.php');
    exit();
  } else {
    header("Location: " . RUTA_VISTAS . '/usuario/usuarioslist.php');
    exit();
  }
}

if (isset($_POST['cancelar'])) {
 header("Location: " . RUTA_VISTAS . '/usuario/usuariosdetail.php?nombreUsuario=' . urlencode($usuario->getNombreUsuario()));
  exit();
}

$eliminarButton = <<<HTML
<form method="POST">
    <button type="submit" name="eliminar" class="btn btn-borrar">
        Sí, eliminar usuario
    </button>
</form>
HTML;

$cancelarButton = <<<HTML
<form method="POST">
    <button type="submit" name="cancelar" class="btn btn-volver">
        Cancelar
    </button>
</form>
HTML;

$tituloPagina = 'Eliminar Usuario';
$tituloHeader = 'Eliminar Usuario';

$contenidoPrincipal = <<<EOS
<section id="contenido">

<h2>Eliminar usuario</h2>

<div class="msg-error">
<p>
Se van a eliminar los datos del usuario 
<strong>{$usuario->getNombreUsuario()}</strong> de forma permanente.
</p>

<p><strong>Esta acción no se puede deshacer.</strong></p>
</div>

<div class="acciones-pagina">
$eliminarButton
$cancelarButton
</div>

</section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
