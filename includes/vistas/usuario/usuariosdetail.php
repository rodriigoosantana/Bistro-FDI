<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname(__DIR__, 2) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\login\generarPerfilUsuario;
use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\Usuario\Rol;

$nombreUsuario = $_GET['nombreUsuario'] ?? null;
$usuario = UsuarioService::buscarPorNombre($nombreUsuario);

$tituloPagina = 'Perfil';
$tituloHeader = "Perfil de {$usuario->getNombreUsuario()}";

$contenidoPrincipal = generarPerfilUsuario::generarPerfil($usuario);

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
