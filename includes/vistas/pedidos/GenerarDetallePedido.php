<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Pedido\PedidoDesglosado;
use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Pedido\Tipo;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Pedido\PedidoService;

class GenerarDetallePedido
{
  private static function getEtiquetasEstado(): array
  {
    return [
      Estado::Nuevo->value         => 'Nuevo',
      Estado::Recibido->value      => 'Recibido',
      Estado::EnPreparacion->value => 'En preparación',
      Estado::Cocinando->value     => 'Cocinando',
      Estado::ListoCocina->value   => 'Listo cocina',
      Estado::Terminado->value     => 'Terminado',
      Estado::Entregado->value     => 'Entregado',
      Estado::Cancelado->value     => 'Cancelado',
    ];
  }

  private static function getClasesEstado(): array
  {
    return [
      Estado::Nuevo->value         => 'estado-nuevo',
      Estado::Recibido->value      => 'estado-recibido',
      Estado::EnPreparacion->value => 'estado-preparacion',
      Estado::Cocinando->value     => 'estado-cocinando',
      Estado::ListoCocina->value   => 'estado-listo',
      Estado::Terminado->value     => 'estado-terminado',
      Estado::Entregado->value     => 'estado-entregado',
      Estado::Cancelado->value     => 'estado-cancelado',
    ];
  }

  public static function generar(PedidoDesglosado $pedido, string $volverUrl, bool $esGerente, bool $esCamarero, bool $esCocinero): string
  {
    $htmlInfo       = self::generarSeccionInfo($pedido);
    $tablaProductos = self::generarTablaProductos($pedido, $esGerente, $esCocinero);
    $botonesAccion  = self::generarBotonesAccion($pedido, $volverUrl, $esGerente, $esCamarero, $esCocinero);

    return <<<EOS
    <section id="contenido">
        <h2>Detalle del pedido</h2>

        <div class="detalle-producto">
            <div class="detalle-info">
                {$htmlInfo}
            </div>
        </div>

        <h3>Productos del pedido</h3>
        {$tablaProductos}

        <div class="acciones-pagina">
            {$botonesAccion}
        </div>
    </section>
    EOS;
  }

  private static function generarSeccionInfo(PedidoDesglosado $pedido): string
  {
    $etiquetas    = self::getEtiquetasEstado();
    $clases       = self::getClasesEstado();
    $numeroPedido = htmlspecialchars($pedido->getNumeroPedido());
    $fecha        = $pedido->getFechaCreacion()->format('d/m/Y H:i');
    $estadoVal    = $pedido->getEstado()->value;
    $estadoLabel  = htmlspecialchars($etiquetas[$estadoVal] ?? $estadoVal);
    $estadoClase  = $clases[$estadoVal] ?? '';
    $tipoVal      = $pedido->getTipo()->value;
    $tipoLabel    = $tipoVal === Tipo::ParaTomar->value ? 'Para tomar' : 'Para llevar';
    $tipoClase    = $tipoVal === Tipo::ParaTomar->value ? 'tipo-local' : 'tipo-llevar';
    $total        = number_format($pedido->getTotal(), 2, ',', '.');
    $descuento    = $pedido->getDescuento();
    $descuentoF   = number_format($descuento, 2, ',', '.');
    $totalPagar   = number_format($pedido->getTotalConDescuento(), 2, ',', '.');
    $clienteId    = htmlspecialchars((string)$pedido->getClienteId());
    $cocineroId   = $pedido->getCocineroId() !== null
      ? htmlspecialchars((string)$pedido->getCocineroId())
      : 'Sin asignar';

    $ofertasPedido = OfertaService::listarOfertasDePedido($pedido->getId());
    $nombreOferta  = !empty($ofertasPedido) ? htmlspecialchars($ofertasPedido[0]->getNombre()) : null;

    if ($descuento > 0) {
      $htmlOferta = $nombreOferta ? " ({$nombreOferta})" : '';
      $htmlPrecio = <<<PRECIO
                <p><strong>Subtotal:</strong> {$total} €</p>
                <p><strong>Descuento:</strong> -{$descuentoF} €{$htmlOferta}</p>
                <p><strong>Total final:</strong> {$totalPagar} €</p>
      PRECIO;
    } else {
      $htmlPrecio = "<p><strong>Total:</strong> {$total} €</p>";
    }

    return <<<EOS
    <p><strong>Número de pedido:</strong> #{$numeroPedido}</p>
    <p><strong>Fecha:</strong> {$fecha}</p>
    <p><strong>Estado:</strong> <span class="badge {$estadoClase}">{$estadoLabel}</span></p>
    <p><strong>Tipo:</strong> <span class="badge {$tipoClase}">{$tipoLabel}</span></p>
    <p><strong>Cliente ID:</strong> {$clienteId}</p>
    <p><strong>Cocinero ID:</strong> {$cocineroId}</p>
    {$htmlPrecio}
    EOS;
  }

  private static function generarTablaProductos(PedidoDesglosado $pedido, bool $esGerente, bool $esCocinero): string
  {
    $estadoVal      = $pedido->getEstado()->value;
    $productos      = $pedido->getProductos();
    $filasProductos = '';

    if ($productos && count($productos) > 0) {
      foreach ($productos as $prod) {
        $pNombre        = htmlspecialchars($prod->getNombre());
        $pCantidad      = (int)$prod->getCantidad();
        $esCanjeado     = $prod->isBistroCoineado();
        $pPrecioValor   = $esCanjeado ? 0.0 : $prod->getPrecio();
        $pPrecio        = number_format($pPrecioValor, 2, ',', '.');
        $pSubtotalValor = $esCanjeado ? 0.0 : ($prod->getPrecio() * $pCantidad);
        $pSubtotal      = number_format($pSubtotalValor, 2, ',', '.');
        $badgeCanje     = $esCanjeado ? ' <small class="marca-recompensa">(Recompensa)</small>' : '';

        $checkHtml           = '';
        $necesitaPreparacion = PedidoService::productoEnPedidodNecesitaPreparacion($pedido->getId(), $prod->getId());

        if ($necesitaPreparacion) {
          $checked  = $prod->isPreparado() ? 'checked' : '';
          $puedeVer = $estadoVal === Estado::Cocinando->value && ($esCocinero || $esGerente);
          $disabled = $puedeVer ? '' : 'disabled';
          $onChange = $puedeVer ? 'onchange="this.form.submit()"' : '';

          $checkHtml = <<<CHECK
          <form method="POST" action="" style="display:inline">
              <input type="hidden" name="accion" value="toggle_producto">
              <input type="hidden" name="producto_id" value="{$prod->getId()}">
              <input type="checkbox" {$onChange} {$checked} {$disabled}>
          </form>
          CHECK;
        }

        $filaClase       = $prod->isPreparado() ? ' class="producto-listo"' : '';
        $filasProductos .= <<<FILA
            <tr{$filaClase}>
                <td>{$checkHtml} {$pNombre}{$badgeCanje}</td>
                <td class="text-center">{$pCantidad}</td>
                <td class="text-right">{$pPrecio} €</td>
                <td class="text-right">{$pSubtotal} €</td>
            </tr>
        FILA;
      }
    } else {
      $filasProductos = '<tr><td colspan="4"><em>Sin productos</em></td></tr>';
    }

    $totalPagar = number_format($pedido->getTotalConDescuento(), 2, ',', '.');

    return <<<TABLA
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
            <td class="text-right"><strong>{$totalPagar} €</strong></td>
        </tr>
    </tfoot>
</table>
    TABLA;
  }

  private static function generarBotonesAccion(PedidoDesglosado $pedido, string $volverUrl, bool $esGerente, bool $esCamarero, bool $esCocinero): string
  {
    $transiciones = [];
    if ($esGerente || $esCamarero || $esCocinero) {
      switch ($pedido->getEstado()) {
        case Estado::Nuevo:
          $transiciones = [Estado::Cancelado->value => 'Cancelar'];
          break;
        case Estado::Recibido:
          $transiciones = [Estado::EnPreparacion->value => 'Cobrar'];
          break;
        case Estado::ListoCocina:
          $transiciones = [Estado::Terminado->value => 'Marcar listo para entregar'];
          break;
        case Estado::Terminado:
          $transiciones = [Estado::Entregado->value => 'Marcar entregado'];
          break;
        default:
          break;
      }
    }
    if ($esGerente || $esCocinero) {
      switch ($pedido->getEstado()) {
        case Estado::EnPreparacion:
          $transiciones[Estado::Cocinando->value] = 'Empezar a cocinar';
          break;
        case Estado::Cocinando:
          $transiciones[Estado::ListoCocina->value] = 'Marcar listo cocina';
          break;
        default:
          break;
      }
    }

    $botonesEstado = '';
    foreach ($transiciones as $nuevoEstado => $etiquetaBtn) {
      $claseBtn       = ($nuevoEstado === Estado::Cancelado->value) ? 'btn-borrar' : 'btn-editar';
      $botonesEstado .= <<<BTN
      <form method="POST" action="" style="display:inline">
          <input type="hidden" name="accion" value="cambiar_estado">
          <input type="hidden" name="nuevo_estado" value="{$nuevoEstado}">
          <button type="submit" class="btn {$claseBtn}">{$etiquetaBtn}</button>
      </form>
      BTN;
    }

    $btnBorrar = '';
    if ($esGerente) {
      $btnBorrar = <<<BTN
      <form method="POST" action="" style="display:inline"
            data-confirm="¿Seguro que quieres borrar este pedido?">
          <input type="hidden" name="accion" value="borrar">
          <button type="submit" class="btn btn-borrar">Borrar</button>
      </form>
      BTN;
    }

    return <<<EOS
    <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
    {$botonesEstado}
    {$btnBorrar}
    EOS;
  }
}
