<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once dirname(__DIR__, 3) . '/includes/config.php';
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

// Se requiere id para ver un pedido
if (!isset($_GET['id'])) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$pedidoDesglosado = PedidoService::buscarDesglosadoPorId(intval($_GET['id']));
if (!$pedidoDesglosado) {
    header('Location: ' . RUTA_VISTAS . '/pedidos/pedidoslist.php');
    exit();
}

$volverUrl = RUTA_VISTAS . '/pedidos/pedidoslist.php';

// Mapa de etiquetas legibles
$etiquetasEstado = [
    'nuevo'          => 'Nuevo',
    'recibido'       => 'Recibido',
    'en preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo cocina'   => 'Listo cocina',
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

// BORRADO (Solo gerente)
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
    PedidoService::eliminar($pedidoDesglosado->getId());
    header('Location: ' . RUTA_VISTAS . '/pedidoslist.php');
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado' && isset($_POST['nuevo_estado'])) {
    $nuevoEstado = trim($_POST['nuevo_estado']);
    PedidoService::cambiarEstado($pedidoDesglosado->getId(), $nuevoEstado);
    header('Location: ' . RUTA_VISTAS . '/pedidosdetail.php?id=' . $pedidoDesglosado->getId());
    exit();
}

$numeroPedido = htmlspecialchars($pedidoDesglosado->getNumeroPedido());
$fecha        = $pedidoDesglosado->getFechaCreacion()->format('d/m/Y H:i');
$estadoVal    = $pedidoDesglosado->getEstado()->value;
$estadoLabel  = htmlspecialchars($etiquetasEstado[$estadoVal] ?? $estadoVal);
$estadoClase  = $clasesEstado[$estadoVal] ?? '';
$tipoVal      = $pedidoDesglosado->getTipo()->value;
$tipoLabel    = $tipoVal === 'local' ? 'Para tomar' : 'Para llevar';
$tipoClase    = $tipoVal === 'local' ? 'tipo-local' : 'tipo-llevar';
$total        = number_format($pedidoDesglosado->getTotal(), 2, ',', '.');
$clienteId    = htmlspecialchars((string)$pedidoDesglosado->getClienteId());
$cocineroId   = ($pedidoDesglosado->getCocineroId() !== null) 
                ? htmlspecialchars((string)$pedidoDesglosado->getCocineroId()) 
                : 'Sin asignar';

$productos         = $pedidoDesglosado->getProductos();
$filasProductos    = '';
$subtotalCalculado = 0.0;

if ($productos && count($productos) > 0) {
  foreach ($productos as $prod) {
    $pNombre   = htmlspecialchars($prod->getNombre());
    $pPrecio   = number_format($prod->getPrecio(), 2, ',', '.');
    $pCantidad = (int)$prod->getCantidad();
    $pSubtotal = number_format($prod->getPrecio() * $pCantidad, 2, ',', '.');
    $subtotalCalculado += $prod->getPrecio() * $pCantidad;

    $checked   = $prod->isPreparado() ? 'checked' : '';
    $checkHtml = '';

    if ($esCocinero || $esCamarero || $esGerente) {
        $checkHtml = <<<CHECK
        <form method="POST" action="" style="display:inline">
            <input type="hidden" name="accion" value="toggle_producto">
            <input type="hidden" name="producto_id" value="{$prod->getId()}">
            <input type="checkbox" onchange="this.form.submit()" {$checked}>
        </form>
        CHECK;
    }

    $filaClase = $prod->isPreparado() ? ' class="producto-listo"' : '';

    $filasProductos .= <<<FILA
        <tr{$filaClase}>
            <td>{$checkHtml} {$pNombre}</td>
            <td class="text-center">{$pCantidad}</td>
            <td class="text-right">{$pPrecio} €</td>
            <td class="text-right">{$pSubtotal} €</td>
        </tr>
    FILA;
}
} else {
    $filasProductos = '<tr><td colspan="4"><em>Sin productos</em></td></tr>';
}

$subtotalFormateado = number_format($subtotalCalculado, 2, ',', '.');

$tablaProductos = <<<TABLA
<table class="tabla-pedido">
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-right">Precio ud.</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        {$filasProductos}
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td class="text-right"><strong>{$total} €</strong></td>
        </tr>
    </tfoot>
</table>
TABLA;

$transiciones = [];
if ($esGerente || $esCamarero) {
    switch ($estadoVal) {
        case 'nuevo':          $transiciones = ['cancelado' => 'Cancelar']; break;
        case 'recibido':       $transiciones = ['en preparacion' => 'Confirmar pago', 'cancelado' => 'Cancelar']; break;
        case 'listo cocina':   $transiciones = ['terminado' => 'Marcar listo para entregar']; break;
        case 'terminado':      $transiciones = ['entregado' => 'Marcar entregado']; break;
    }
}
if ($esCocinero) {
    switch ($estadoVal) {
        case 'en preparacion': $transiciones['cocinando']    = 'Empezar a cocinar'; break;
        case 'cocinando':      $transiciones['listo cocina'] = 'Marcar listo cocina'; break;
    }
}

$botonesEstado = '';
foreach ($transiciones as $nuevoEstado => $etiquetaBtn) {
    $claseBtn = ($nuevoEstado === 'cancelado') ? 'btn-borrar' : 'btn-editar';
    $botonesEstado .= <<<BTN
    <form method="POST" action="" style="display:inline">
        <input type="hidden" name="accion" value="cambiar_estado">
        <input type="hidden" name="nuevo_estado" value="{$nuevoEstado}">
        <button type="submit" class="btn {$claseBtn}">{$etiquetaBtn}</button>
    </form>
    BTN;
}

// Botón borrar solo para gerente
$btnBorrar = '';
if ($esGerente) {
    $btnBorrar = <<<BTN
    <form method="POST" action="" style="display:inline"
          onsubmit="return confirm('¿Seguro que quieres borrar este pedido?')">
        <input type="hidden" name="accion" value="borrar">
        <button type="submit" class="btn btn-borrar">Borrar</button>
    </form>
    BTN;
}

$tituloPagina = "Pedido #{$numeroPedido}";
$tituloHeader = 'Ver pedido';

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Detalle del pedido</h2>

        <div class="detalle-producto">
            <div class="detalle-info">
                <p><strong>Número de pedido:</strong> #{$numeroPedido}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
                <p><strong>Estado:</strong> <span class="badge {$estadoClase}">{$estadoLabel}</span></p>
                <p><strong>Tipo:</strong> <span class="badge {$tipoClase}">{$tipoLabel}</span></p>
                <p><strong>Cliente ID:</strong> {$clienteId}</p>
                <p><strong>Cocinero ID:</strong> {$cocineroId}</p>
                <p><strong>Total:</strong> {$total} €</p>
            </div>
        </div>

        <h3>Productos del pedido</h3>
        {$tablaProductos}

        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$botonesEstado}
            {$btnBorrar}
        </div>
    </section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
?>
