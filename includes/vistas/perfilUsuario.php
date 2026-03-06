<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__,2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';

$nombreUsuario = $_GET['nombreUsuario'] ?? null; 
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

$tituloPagina = 'Perfil';
$tituloHeader = "Perfil de {$usuario->getNombreUsuario()}";
$rol = Rol::cargarRol($usuario->getId());
$fila = 
   "<tr>
      <td>{$usuario->getId()}</td>
      <td>{$usuario->getNombreUsuario()}</td>
      <td>{$usuario->getNombre()}</td>
      <td>{$usuario->getApellidos()}</td>
      <td>{$usuario->getEmail()}</td>
      <td>{$usuario->getAvatar()}</td>
      <td>{$rol->getNombre()}</td>
      <td> <a href=\"modificarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}\">Modificar Datos</a> </td>
      <td> <a href=\"eliminarUsuario.php?nombreUsuario={$usuario->getNombreUsuario()}\">Eliminar Usuario</a> </td>
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
       <th>Modificar</th>
       <th>Eliminar</th>
   </tr>
   $fila
   </table>
   </section>
EOS;

require("common/plantilla.php");
?>


