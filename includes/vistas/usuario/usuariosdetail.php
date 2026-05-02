<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\vistas\usuario\GenerarPerfilUsuario;
use es\ucm\fdi\aw\Usuario\UsuarioService;

$nombreUsuario = $_GET['nombreUsuario'] ?? null;
$usuario       = UsuarioService::buscarPorNombre($nombreUsuario);

$tituloPagina = 'Perfil';
$tituloHeader = "Perfil de {$usuario->getNombreUsuario()}";

$contenidoPrincipal = GenerarPerfilUsuario::generarPerfil($usuario);

$modUrl    = RUTA_VISTAS . '/usuario/usuariosedit.php?nombreUsuario=' . urlencode($usuario->getNombreUsuario());
$logoutUrl = RUTA_VISTAS . '/usuario/logout.php';

$contenidoAside = <<<ASIDE
<section>
    <h3>Accesos rápidos</h3>
</section>
<a href="{$modUrl}" class="aside-link"> Modificar datos</a>
<br>
<a href="{$logoutUrl}" class="aside-link">Cerrar sesión</a>
ASIDE;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
