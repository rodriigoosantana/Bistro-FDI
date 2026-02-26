<?php

require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';


$tituloPagina = 'Eliminar Usuarios';
$tituloHeader = 'Eliminar Usuarios';
$filas = "";

foreach ($usuarios as $u) {
   $filas .= "<tr>
      <td>{$u->getRol()}</td>
      <td>{$u->getNombreUsuario()}</td>
      <td>{$u->getNombre()}</td>
      <td>{$u->getApellidos()}</td>
      <td>{$u->getEmail()}</td>
      <td>{$u->getAvatar()}</td>
      <td>{$u->getId()}</td>
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
   </tr>
   $filas
   </table>
   </section>
EOS;

require("common/plantilla.php");
?>


