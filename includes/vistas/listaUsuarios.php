<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Usuario\Rol;
use es\ucm\fdi\aw\Aplicacion;

$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';

$usuarios = UsuarioService::listarTodos();
$filas = "";

foreach ($usuarios as $u) {

  $rol = Rol::cargarRol($u->getId());

  $avatar_img = "<img src='" . RUTA_APP . $u->getAvatar() . "' alt='{$u->getNombreUsuario()}'>";

  $perfilUrl = "perfilUsuario.php?nombreUsuario={$u->getNombreUsuario()}";

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
          <small>Rol: {$rol->getNombre()}</small>
      </div>

      <div class="categoria-acciones">
          <a href="{$perfilUrl}" class="btn btn-ver">Ver perfil</a>
      </div>

  </div>
  FILA;
}

$acceso = Aplicacion::getInstance()::puedeListarUsuarios();

$contenidoPrincipal = <<<EOS
<section id="contenido">

<h2>Usuarios</h2>

<div class="lista-categorias">
$filas
</div>

</section>
EOS;

require("common/plantilla.php");
