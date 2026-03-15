<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Usuario\Rol;

$nombreUsuario = $_GET['nombreUsuario'] ?? null;
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

$tituloPagina = 'Perfil';
$tituloHeader = "Perfil de {$usuario->getNombreUsuario()}";
$rol = Rol::cargarRol($usuario->getId());
$avatar_img = "<img src='" . RUTA_APP . $usuario->getAvatar() . "' width='80' height='80'>";
$fila =
  "<tr>
      <td>{$usuario->getId()}</td>
      <td>{$usuario->getNombreUsuario()}</td>
      <td>{$usuario->getNombre()}</td>
      <td>{$usuario->getApellidos()}</td>
      <td>{$usuario->getEmail()}</td>
      <td>$avatar_img</td>
      <td>{$rol->getNombre()}</td>
      <td> <a href=\"modificarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}\">Modificar Datos</a> </td>
      <td> <a href=\"eliminarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}\">Eliminar Usuario</a> </td>
   </tr>";

$contenidoPrincipal = <<<EOS
  <section class="perfil-usuario">

  <div class="perfil-header">
      <div class="perfil-avatar">
          $avatar_img
      </div>

      <div class="perfil-datos-principales">
          <h2>{$usuario->getNombreUsuario()}</h2>
          <p>{$usuario->getNombre()} {$usuario->getApellidos()}</p>
          <p><strong>Rol:</strong> {$rol->getNombre()}</p>
      </div>
  </div>

  <table class="perfil-tabla">
  <tr>
      <th>ID</th>
      <td>{$usuario->getId()}</td>
  </tr>

  <tr>
      <th>Email</th>
      <td>{$usuario->getEmail()}</td>
  </tr>

  <tr>
      <th>Nombre</th>
      <td>{$usuario->getNombre()}</td>
  </tr>

  <tr>
      <th>Apellidos</th>
      <td>{$usuario->getApellidos()}</td>
  </tr>

  <tr>
      <th>Nombre de usuario</th>
      <td>{$usuario->getNombreUsuario()}</td>
  </tr>
  </table>

  <div class="acciones-pagina">

  <a class="btn btn-editar" 
  href="modificarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}">
  Modificar datos
  </a>

  <a class="btn btn-borrar" 
  href="eliminarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}">
  Eliminar usuario
  </a>

  </div>

  </section>
EOS;

$modUrl = "modificarUsuario.php?nombreUsuario=" . urlencode($usuario->getNombreUsuario());
$logoutUrl = RUTA_VISTAS . '/logout.php';

$contenidoAside = <<<ASIDE
<section>
    <h3>Accesos rápidos</h3>
</section>
<a href="{$modUrl}" class="aside-link"> Modificar datos</a>
<br>
<a href="{$logoutUrl}" class="aside-link">Cerrar sesión</a>
ASIDE;

require("common/plantilla.php");
