<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);
$esCocinero = ($_SESSION['rolId'] === Usuario::ROL_COCINERO);
$esCliente  = ($_SESSION['rolId'] === Usuario::ROL_CLIENTE);
$clienteId  = $esCliente ? $_SESSION['userId'] : null;

$modos = [
    'activos' => [
        'titulo'  => 'Pedidos en curso',
        'estados' => ['nuevo', 'recibido', 'en preparacion', 'cocinando', 'listo cocina', 'terminado'],
        'roles'   => [Usuario::ROL_CLIENTE, Usuario::ROL_CAMARERO, Usuario::ROL_GERENTE],
    ],
    'historial' => [
        'titulo'  => 'Historial de pedidos',
        'estados' => ['entregado', 'cancelado'],
        'roles'   => [Usuario::ROL_CLIENTE, Usuario::ROL_GERENTE],
    ],
    'cocina' => [
        'titulo'  => 'Cola de cocina',
        'estados' => ['en preparacion', 'cocinando'],
        'roles'   => [Usuario::ROL_COCINERO, Usuario::ROL_GERENTE],
    ],
    'todos' => [
        'titulo'  => 'Todos los pedidos',
        'estados' => [], // vacío = sin filtro de estado
        'roles'   => [Usuario::ROL_GERENTE],
    ],
];

$modo = $_GET['modo'] ?? 'activos';

if (!isset($modos[$modo])) {
    header('Location: ' . RUTA_VISTAS . '/pedidoslist.php?modo=activos');
    exit();
}

$cfg = $modos[$modo];

if (!$esGerente && !in_array($_SESSION['rolId'], $cfg['roles'])) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

$filtroEstado = ($esGerente && isset($_GET['estado']) && $_GET['estado'] !== '')
    ? trim($_GET['estado'])
    : null;

$estadosFiltro = $filtroEstado ? [$filtroEstado] : $cfg['estados'];
$pedidos = PedidoService::listarPorEstados(
    estados:   $estadosFiltro ?: null, 
    clienteId: $clienteId
);

$etiquetasEstado = [
    'nuevo'          => 'Nuevo',
    'recibido'       => 'Recibido',
    'en preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo_cocina'   => 'Listo cocina',
    'terminado'      => 'Terminado',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

$clasesEstado = [
    'nuevo'          => 'estado-nuevo',
    'recibido'       => 'estado-recibido',
    'en preparacion' => 'estado-preparacion',
    'cocinando'      => 'estado-cocinando',
    'listo cocina'   => 'estado-listo',
    'terminado'      => 'estado-terminado',
    'entregado'      => 'estado-entregado',
    'cancelado'      => 'estado-cancelado',
];

$htmlFiltro = '';
if ($esGerente) {
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
        $url      = RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=' . $claveModo;
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
        $tipoLabel    = $p->getTipo()->value === 'local' ? 'Para tomar' : 'Para llevar';
        $tipoClase    = $p->getTipo()->value === 'local' ? 'tipo-local' : 'tipo-llevar';
        
        // Redireccion según el estado
        if ($estadoVal === 'nuevo') {
            $verUrl = RUTA_VISTAS . '/pedidos/anadir_productos.php?id=' . $id;
        } elseif ($estadoVal === 'recibido') {
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
    $crearUrl      = RUTA_VISTAS . '/pedidosdetail.php';
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
?>
