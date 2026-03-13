<?php


namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Usuario\Usuario;

require_once dirname(__DIR__, 3) . '/includes/config.php';
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header('Location: ' . RUTA_VISTAS . '/login.php');
  exit();
}

$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);
$esCocinero = ($_SESSION['rolId'] === Usuario::ROL_COCINERO);
$esCliente  = ($_SESSION['rolId'] === Usuario::ROL_CLIENTE);
$clienteId  = $esCliente  ? $_SESSION['userId'] : null;
$cocineroId = $esCocinero ? $_SESSION['userId'] : null;

$modos = [
    'activos' => [
        'titulo'     => 'En curso',
        'estados'    => [Estado::Nuevo->value, Estado::Recibido->value, Estado::EnPreparacion->value, Estado::Cocinando->value, Estado::ListoCocina->value, Estado::Terminado->value],
        'roles'      => [Usuario::ROL_CLIENTE, Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
    'para recoger' => [
        'titulo'     => 'Por recoger',
        'estados'    => [Estado::ListoCocina->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
    'historial' => [
        'titulo'     => 'Historial',
        'estados'    => [], // vacío = sin filtro
        'roles'      => [Usuario::ROL_CLIENTE, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
    'cocina' => [
        'titulo'     => 'En cocina',
        'estados'    => [Estado::EnPreparacion->value, Estado::Cocinando->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
    'mis pedidos' => [
        'titulo'     => 'Mis pedidos en curso',
        'estados'    => [Estado::Cocinando->value],
        'roles'      => [Usuario::ROL_COCINERO],
        'filtroCoci' => true,
    ],
    'pedidos a cocinar' => [
        'titulo'     => 'Por cocinar',
        'estados'    => [Estado::EnPreparacion->value],
        'roles'      => [Usuario::ROL_COCINERO],
        'filtroCoci' => false,
    ],
    'por cobrar' => [
        'titulo'     => 'Por cobrar',
        'estados'    => [Estado::Recibido->value],
        'roles'      => [Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE],
        'filtroCoci' => false,
    ],
];

$modo = $_GET['modo'] ?? ($esCocinero ? 'mis pedidos' : 'activos');

if (!isset($modos[$modo])) {
header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=' . ($esCocinero ? urlencode('mis pedidos') : 'activos'));
    exit();
}

$cfg = $modos[$modo];

if (!$esGerente && !in_array($_SESSION['rolId'], $cfg['roles'])) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

$filtroEstado = ($esGerente && $modo === 'historial' && isset($_GET['estado']) && $_GET['estado'] !== '')
    ? trim($_GET['estado'])
    : null;

$estadosFiltro = $filtroEstado ? [$filtroEstado] : $cfg['estados'];
$filtroCociId  = (!empty($cfg['filtroCoci']) && $esCocinero) ? $cocineroId : null;

$pedidos = PedidoService::listarPorEstados(
    estados:    $estadosFiltro ?: null,
    clienteId:  $clienteId,
    cocineroId: $filtroCociId
);

$etiquetasEstado = [
    Estado::Nuevo->value         => 'Nuevo',
    Estado::Recibido->value      => 'Recibido',
    Estado::EnPreparacion->value => 'En preparación',
    Estado::Cocinando->value     => 'Cocinando',
    Estado::ListoCocina->value   => 'Listo cocina',
    Estado::Terminado->value     => 'Terminado',
    Estado::Entregado->value     => 'Entregado',
    Estado::Cancelado->value     => 'Cancelado',
];

$clasesEstado = [
    Estado::Nuevo->value         => 'estado-nuevo',
    Estado::Recibido->value      => 'estado-recibido',
    Estado::EnPreparacion->value => 'estado-preparacion',
    Estado::Cocinando->value     => 'estado-cocinando',
    Estado::ListoCocina->value   => 'estado-listo',
    Estado::Terminado->value     => 'estado-terminado',
    Estado::Entregado->value     => 'estado-entregado',
    Estado::Cancelado->value     => 'estado-cancelado',
];

$htmlFiltro = '';
if ($esGerente && $modo === 'historial') {
    $opcionesFiltro = '<option value="">Todos</option>';
    foreach ($etiquetasEstado as $valor => $etiqueta) {
        $selected        = ($filtroEstado === $valor) ? ' selected' : '';
        $opcionesFiltro .= "<option value=\"{$valor}\"{$selected}>{$etiqueta}</option>";
    }
    $htmlFiltro = <<<FILTRO
    <div class="filtros-pedidos">
        <form method="GET" action="">
            <input type="hidden" name="modo" value="{$modo}">
            <label for="estado">Filtrar por estado:</label>
            <select name="estado" id="estado" onchange="this.form.submit()">
                {$opcionesFiltro}
            </select>
        </form>
    </div>
    FILTRO;
}

$rolId = $_SESSION['rolId'];
$modosVisibles = array_filter($modos, function ($cfgModo) use ($rolId, $esGerente) {
  return $esGerente || in_array($rolId, $cfgModo['roles']);
});

$htmlNavModos = '';
if (count($modosVisibles) > 1) {
    $enlaces = '';
    foreach ($modosVisibles as $claveModo => $cfgModo) {
        $activo   = ($claveModo === $modo) ? ' class="btn btn-ver"' : ' class="btn btn-volver"';
        $url      = RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=' . urlencode($claveModo);
        $enlaces .= "<a href=\"{$url}\"{$activo}>{$cfgModo['titulo']}</a> ";
    }
    $htmlNavModos = "<div class=\"nav-modos\">{$enlaces}</div>";
}

$tarjetas = '';
if ($pedidos && count($pedidos) > 0) {
    foreach ($pedidos as $p) {
        $id           = $p->getId();
        $numeroPedido = htmlspecialchars($p->getNumeroPedido());
        $fecha        = $p->getFechaCreacion()->format('d/m/Y H:i');
        $estadoVal    = $p->getEstado()->value;
        $estadoLabel  = htmlspecialchars($etiquetasEstado[$estadoVal] ?? $estadoVal);
        $estadoClase  = $clasesEstado[$estadoVal] ?? '';
        $tipoLabel    = $p->getTipo()->value === Tipo::ParaTomar->value ? 'Para tomar' : 'Para llevar';
        $tipoClase    = $p->getTipo()->value === Tipo::ParaTomar->value ? 'tipo-local' : 'tipo-llevar';

        if ($estadoVal === Estado::Nuevo->value) {
            $verUrl = RUTA_VISTAS . '/pedidos/anadir_productos.php?id=' . $id;
        } elseif ($estadoVal === Estado::Recibido->value && !$esCamarero && !$esGerente) {
            $verUrl = RUTA_VISTAS . '/pedidos/pagar_pedido.php?id=' . $id;
        } else {
            $verUrl = RUTA_VISTAS . '/pedidos/verPedidoDesglosado.php?id=' . $id;
        }

        $htmlTotal = '';
        if (!$esCocinero) {
            $total     = number_format($p->getTotal(), 2, ',', '.');
            $htmlTotal = "<span class=\"tarjeta-precio\">{$total} €</span>";
        }

        $tarjetas .= <<<TARJETA
        <div class="tarjeta-producto">
            <div class="tarjeta-info">
                <strong>Pedido #{$numeroPedido}</strong>
                <span class="badge {$estadoClase}">{$estadoLabel}</span>
                <span class="badge {$tipoClase}">{$tipoLabel}</span>
                <span><small>{$fecha}</small></span>
                {$htmlTotal}
            </div>
            <div class="tarjeta-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        TARJETA;
  }
} else {
  $tarjetas = '<p>No hay pedidos en este apartado.</p>';
}

$btnCrearNuevo = '';
if ($esGerente || $esCamarero) {
  $crearUrl      = RUTA_VISTAS . '/pedidos/nuevo_pedido.php';
  $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Nuevo pedido</a>";
}

$volverUrl    = RUTA_APP . '/index.php';
$tituloPagina = $cfg['titulo'];
$tituloHeader = $cfg['titulo'];

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>{$tituloPagina}</h2>

        {$htmlNavModos}
        {$htmlFiltro}

        <div class="lista-productos">
            {$tarjetas}
        </div>

        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$btnCrearNuevo}
        </div>
    </section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
