<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';

$nombreUsuario = $_GET['nombreUsuario']; 
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

$tituloPagina = 'Perfil';
$tituloHeader = "Perfil de {$usuario->getNombreUsuario()}";
$fila = 
   "<tr>
      <td>{$usuario->getId()}</td>
      <td>{$usuario->getNombreUsuario()}</td>
      <td>{$usuario->getNombre()}</td>
      <td>{$usuario->getApellidos()}</td>
      <td>{$usuario->getEmail()}</td>
      <td>{$usuario->getAvatar()}</td>
      <td>{$usuario->getRol()}</td>
   </tr>";

$contenidoPrincipal = <<<EOS
   <section id="contenido">
   <h2>usuarios</h2>
   <table border="1">
   <tr>
       <th>id</th>
       <th>nombre de Usuario</th>
       <th>nombre</th>
       <th>apellidos</th>
       <th>email</th>
       <th>avatar</th>
       <th>rol</th>
   </tr>
   $fila
   </table>
   </section>
EOS;

require("common/plantilla.php");
?>


