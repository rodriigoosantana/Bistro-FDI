<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Aplicacion;

require_once dirname(__DIR__, 3) . '/includes/config.php';
if (!Aplicacion::estaLogueado()) {
  header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
  exit();
}

$esGerente  = (Aplicacion::getRolId() === Usuario::ROL_GERENTE);
$esCamarero = (Aplicacion::getRolId() === Usuario::ROL_CAMARERO);
$esCocinero = (Aplicacion::getRolId() === Usuario::ROL_COCINERO);
$esCliente  = (Aplicacion::getRolId() === Usuario::ROL_CLIENTE);
$clienteId  = $esCliente  ? Aplicacion::getUserId() : null;
$cocineroId = $esCocinero ? Aplicacion::getUserId() : null;

$modos = [
    'activos' => [
        'titulo'        => 'Mis pedidos activos',
        'estados'       => [Estado::Nuevo->value, Estado::Recibido->value, Estado::EnPreparacion->value, Estado::Cocinando->value, Estado::ListoCocina->value, Estado::Terminado->value],
        'roles'         => [Usuario::ROL_CLIENTE, Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci'    => false,
        'filtroUsuario' => true,
    ],
    'para recoger' => [
        'titulo'     => 'Por recoger',
        'estados'    => [Estado::ListoCocina->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci' => false,
    ],
    'historial' => [
        'titulo'     => 'Historial',
        'estados'    => [],
        'roles'      => [Usuario::ROL_CLIENTE, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
    'cocina' => [
        'titulo'     => 'En cocina',
        'estados'    => [Estado::EnPreparacion->value, Estado::Cocinando->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci' => false,
    ],
    'mis pedidos' => [
        'titulo'     => 'Mis pedidos asignados',
        'estados'    => [Estado::Cocinando->value],
        'roles'      => [Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci' => true,
    ],
    'pedidos a cocinar' => [
        'titulo'     => 'Por cocinar',
        'estados'    => [Estado::EnPreparacion->value],
        'roles'      => [Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci' => false,
    ],
    'por cobrar' => [
        'titulo'     => 'Por cobrar',
        'estados'    => [Estado::Recibido->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE, Usuario::ROL_COCINERO],
        'filtroCoci' => false,
    ],
];

$modo = $_GET['modo'] ?? ($esCocinero ? 'mis pedidos' : 'activos');

if (!isset($modos[$modo])) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=' . ($esCocinero ? urlencode('mis pedidos') : 'activos'));
    exit();
}

$cfg = $modos[$modo];

if (!$esGerente && !in_array(Aplicacion::getRolId(), $cfg['roles'])) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

$filtroEstado = ($esGerente && $modo === 'historial' && isset($_GET['estado']) && $_GET['estado'] !== '')
    ? trim($_GET['estado'])
    : null;

$estadosFiltro   = $filtroEstado ? [$filtroEstado] : $cfg['estados'];
$filtroCociId    = (!empty($cfg['filtroCoci']) && $esCocinero) ? $cocineroId : null;
$filtroClienteId = (!empty($cfg['filtroUsuario'])) ? intval(Aplicacion::getUserId()) : $clienteId;

$pedidos = PedidoService::listarPorEstados(
    estados:    $estadosFiltro ?: null,
    clienteId:  $filtroClienteId,
    cocineroId: $filtroCociId
);

$rolId         = Aplicacion::getRolId();
$modosVisibles = array_filter($modos, function ($cfgModo) use ($rolId, $esGerente) {
  return $esGerente || in_array($rolId, $cfgModo['roles']);
});

$tituloPagina = $cfg['titulo'];
$tituloHeader = $cfg['titulo'];

$contenidoPrincipal = GenerarListaPedidos::generar(
    pedidos:       $pedidos,
    modosVisibles: $modosVisibles,
    modoActual:    $modo,
    esGerente:     $esGerente,
    esCamarero:    $esCamarero,
    esCocinero:    $esCocinero,
    filtroEstado:  $filtroEstado
);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
