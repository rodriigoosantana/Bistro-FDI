<?php

namespace es\ucm\fdi\aw\vistas\usuario;

use es\ucm\fdi\aw\Usuario\Rol;
use es\ucm\fdi\aw\Usuario\UsuarioService;

class GenerarListaUsuarios
{
  public static function listarUsuarios()
  {
    $filas = "";
    $usuarios = UsuarioService::listarTodos();
    foreach ($usuarios as $u) {

      $rol = Rol::cargarRol($u->getId());

      $avatar_img = "<img src='" . RUTA_APP . $u->getAvatar() . "' alt='{$u->getNombreUsuario()}'>";

      $perfilUrl = "usuariosdetail.php?nombreUsuario={$u->getNombreUsuario()}";

      $saldo = $u->getSaldoBistrocoins() . " BistroCoins";
      $filas .= <<<FILA
        <div class="categoria-item">

            <div class="categoria-imagen">
                {$avatar_img}
            </div>

            <div class="categoria-info">
                <strong class="categoria-nombre">{$u->getNombreUsuario()}</strong>
                <span class="categoria-descripcion">
                    {$u->getNombre()} {$u->getApellidos()}
                </span>
                <span class="categoria-descripcion">
                    {$u->getEmail()}
                </span>
                <span class="categoria-descripcion">
                    {$saldo}
                </span>
                <small>Rol: {$rol->getNombre()}</small>
            </div>

            <div class="categoria-acciones">
                <a href="{$perfilUrl}" class="btn btn-ver">Ver perfil</a>
            </div>

        </div>
        FILA;
    }
    return $filas;
  }
}
