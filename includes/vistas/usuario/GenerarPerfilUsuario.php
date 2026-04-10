<?php

namespace es\ucm\fdi\aw\vistas\login;

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Usuario\Rol;

class GenerarPerfilUsuario
{
  public static function generarPerfil($usuario)
  {
    $nombreUsuarioEsc = htmlspecialchars($usuario->getNombreUsuario());
    $nombreEsc = htmlspecialchars($usuario->getNombre());
    $apellidosEsc = htmlspecialchars($usuario->getApellidos());
    $emailEsc = htmlspecialchars($usuario->getEmail());
    $saldoEsc = htmlspecialchars($usuario->getSaldoBistrocoins()) . " BistroCoins";

    $avatar_img = "<img src='" . RUTA_APP . htmlspecialchars($usuario->getAvatar()) . "' width='80' height='80' alt='Avatar'>";
    $rolNombre = htmlspecialchars(Rol::cargarRol($usuario->getId())->getNombre());

    $fila = <<<EOS
      <section class="perfil-usuario">

          <div class="perfil-header">
              <div class="perfil-avatar">
                  {$avatar_img}
              </div>
              <div class="perfil-datos-principales">
                  <h2>{$nombreUsuarioEsc}</h2>
                  <p>{$nombreEsc} {$apellidosEsc}</p>
                  <p><strong>Rol:</strong> {$rolNombre}</p>
                  <p><strong>Saldo:</strong> {$saldoEsc}</p>
              </div>
          </div>

          <table class="perfil-tabla">
              <tr>
                  <th>ID</th>
                  <td>{$usuario->getId()}</td>
              </tr>
              <tr>
                  <th>Email</th>
                  <td><a href="mailto:{$emailEsc}">{$emailEsc}</a></td>
              </tr>
              <tr>
                  <th>Nombre</th>
                  <td>{$nombreEsc}</td>
              </tr>
              <tr>
                  <th>Apellidos</th>
                  <td>{$apellidosEsc}</td>
              </tr>
              <tr>
                  <th>Nombre de usuario</th>
                  <td>{$nombreUsuarioEsc}</td>
              </tr>
          </table>

          <div class="acciones-pagina">
              <a class="btn btn-editar" href="modificarUsuario.php?nombreUsuario={$nombreUsuarioEsc}">Modificar datos</a>
              <a class="btn btn-borrar" href="eliminarUsuario.php?nombreUsuario={$nombreUsuarioEsc}">Eliminar usuario</a>
          </div>
      </section>
      EOS;
    return $fila;
  }
}
