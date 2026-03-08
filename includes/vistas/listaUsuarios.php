<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';
require_once RAIZ_APP . '/includes/Aplicacion.php';


$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';
$usuarios = UsuarioService::listarTodos();
$filas = "";

foreach ($usuarios as $u) {
  $rol = Rol::cargarRol($u->getId());
  $avatar_img = "<img src='" . RUTA_APP . $u->getAvatar() . "' width='80' height='80'>";

  $filas .= "<tr>
      <td>{$u->getId()}</td>
      <td>{$u->getNombreUsuario()}</td>
      <td>{$u->getNombre()}</td>
      <td>{$u->getApellidos()}</td>
      <td>{$u->getEmail()}</td>
      <td>$avatar_img</td>
      <td>{$rol->getNombre()}</td>
      <td> <a href=\"perfilUsuario.php?nombreUsuario={$u->getNombreUsuario()}\">Ver Perfil</a> </td>
   </tr>";
}

$acceso = Aplicacion::getInstance()::puedeListarUsuarios();
$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>Usuarios</h2>
   <table border="1">
   <tr>
       <th>id</th>
       <th>Nombre de Usuario</th>
       <th>Nombre</th>
       <th>Apellidos</th>
       <th>Email</th>
       <th>Avatar</th>
       <th>Rol</th>
       <th>Perfil</th>
   </tr>
   $filas
   </table>
   </section>
EOS;

require("common/plantilla.php");
