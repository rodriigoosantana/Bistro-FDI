<?php

require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';


$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';
$usuarios = UsuarioService::listarTodos();
$filas = "";

foreach ($usuarios as $u) {
   $filas .= "<tr>
      <td>{$u->getId()}</td>
      <td>{$u->getNombreUsuario()}</td>
      <td>{$u->getNombre()}</td>
      <td>{$u->getApellidos()}</td>
      <td>{$u->getEmail()}</td>
      <td>{$u->getAvatar()}</td>
      <td>{$u->getRol()}</td>
      <td> <a href=\"perfilUsuario.php?nombreUsuario={$u->getNombreUsuario()}\">Ver Perfil</a> </td>
   </tr>";
}
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
?>


