<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Usuario\UsuarioService;
use es\ucm\fdi\aw\vistas\usuario\GenerarListaUsuarios;

$tituloPagina = 'Lista Usuarios';
$tituloHeader = 'Lista Usuarios';

$acceso   = Aplicacion::getInstance()::puedeListarUsuarios();
$usuarios = $acceso ? (UsuarioService::listarTodos() ?: []) : [];

$contenidoPrincipal = GenerarListaUsuarios::generar($usuarios);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
