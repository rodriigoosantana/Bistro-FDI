<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

// Verificar login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente  = ($_SESSION['rolId'] === Usuario::ROL_GERENTE);
$esCamarero = ($_SESSION['rolId'] === Usuario::ROL_CAMARERO);
$esCocinero = ($_SESSION['rolId'] === Usuario::ROL_COCINERO);

// Filtro de estado (opcional, por GET)
$filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$pedidos = PedidoService::listarTodos($filtroEstado ?: null);

// Mapa de etiquetas legibles por estado
$etiquetasEstado = [
    'nuevo'          => 'Nuevo',
    'recibido'       => 'Recibido',
    'en_preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo_cocina'   => 'Listo cocina',
    'terminado'      => 'Terminado',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

// Clases CSS por estado para badges de color
$clasesEstado = [
    'nuevo'          => 'estado-nuevo',
    'recibido'       => 'estado-recibido',
    'en_preparacion' => 'estado-preparacion',
    'cocinando'      => 'estado-cocinando',
    'listo_cocina'   => 'estado-listo',
    'terminado'      => 'estado-terminado',
    'entregado'      => 'estado-entregado',
    'cancelado'      => 'estado-cancelado',
];

// Construir opciones del selector de filtro
$opcionesFiltro = '<option value="">Todos</option>';
foreach ($etiquetasEstado as $valor => $etiqueta) {
    $selected         = ($filtroEstado === $valor) ? ' selected' : '';
    $opcionesFiltro  .= "<option value=\"{$valor}\"{$selected}>{$etiqueta}</option>";
}

// Construir tarjetas de pedidos
$tarjetas = '';
if ($pedidos && count($pedidos) > 0) {
    foreach ($pedidos as $p) {
        $id            = $p->getId();
        $numeroPedido  = htmlspecialchars($p->getNumeroPedido());
        $fecha         = $p->getFechaCreacion()->format('d/m/Y H:i');
        $estadoVal     = $p->getEstado()->value;
        $estadoLabel   = htmlspecialchars($etiquetasEstado[$estadoVal] ?? $estadoVal);
        $estadoClase   = $clasesEstado[$estadoVal] ?? '';
        $tipoLabel     = $p->getTipo()->value === 'local' ? 'Para tomar' : 'Para llevar';
        $tipoClase     = $p->getTipo()->value === 'local' ? 'tipo-local' : 'tipo-llevar';
        $total         = number_format($p->getTotal(), 2, ',', '.');
        $verUrl        = RUTA_VISTAS . '/pedidosdetail.php?id=' . $id;

        $tarjetas .= <<<TARJETA
        <div class="tarjeta-producto">
            <div class="tarjeta-info">
                <strong>Pedido #{$numeroPedido}</strong>
                <span class="badge {$estadoClase}">{$estadoLabel}</span>
                <span class="badge {$tipoClase}">{$tipoLabel}</span>
                <span><small>{$fecha}</small></span>
                <span class="tarjeta-precio">{$total} €</span>
            </div>
            <div class="tarjeta-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
            </div>
        </div>
        TARJETA;
    }
} else {
    $tarjetas = '<p>No hay pedidos registrados.</p>';
}

// Botón crear solo para gerente/camarero
$btnCrearNuevo = '';
if ($esGerente || $esCamarero) {
    $crearUrl      = RUTA_VISTAS . '/pedidosdetail.php';
    $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Nuevo pedido</a>";
}

$volverUrl    = RUTA_APP . '/index.php';
$tituloPagina = 'Lista de Pedidos';
$tituloHeader = 'Lista de Pedidos';

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Lista de pedidos</h2>

        <div class="filtros-pedidos">
            <form method="GET" action="">
                <label for="estado">Filtrar por estado:</label>
                <select name="estado" id="estado" onchange="this.form.submit()">
                    {$opcionesFiltro}
                </select>
            </form>
        </div>

        <div class="lista-productos">
            {$tarjetas}
        </div>

        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$btnCrearNuevo}
        </div>
    </section>
EOS;

require('common/plantilla.php');
?>
