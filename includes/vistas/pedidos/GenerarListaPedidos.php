<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Pedido\Estado;
use es\ucm\fdi\aw\Pedido\Tipo;

class GenerarListaPedidos
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

  public static function generar(
    array $pedidos,
    array $modosVisibles,
    string $modoActual,
    bool $esGerente,
    bool $esCamarero,
    bool $esCocinero,
    ?string $filtroEstado
  ): string {
    $tituloPagina = $modosVisibles[$modoActual]['titulo'] ?? $modoActual;
    $htmlNavModos = self::generarNavModos($modosVisibles, $modoActual);
    $htmlFiltro   = self::generarFiltroEstado($esGerente, $modoActual, $filtroEstado);
    $tarjetas     = self::generarTarjetas($pedidos, $esGerente, $esCamarero, $esCocinero);
    $volverUrl    = RUTA_APP . '/index.php';

    $btnCrearNuevo = '';
    if ($esGerente || $esCamarero || $esCocinero) {
      $crearUrl      = RUTA_VISTAS . '/pedidos/pedidosnew.php';
      $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Nuevo pedido</a>";
    }

    return <<<EOS
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
  }

  private static function generarNavModos(array $modosVisibles, string $modoActual): string
  {
    if (count($modosVisibles) <= 1) {
      return '';
    }

    $enlaces = '';
    foreach ($modosVisibles as $claveModo => $cfgModo) {
      $activo   = ($claveModo === $modoActual) ? ' class="btn btn-ver"' : ' class="btn btn-volver"';
      $url      = RUTA_VISTAS . '/pedidos/pedidoslist.php?modo=' . urlencode($claveModo);
      $enlaces .= "<a href=\"{$url}\"{$activo}>{$cfgModo['titulo']}</a> ";
    }

    return "<div class=\"nav-modos\">{$enlaces}</div>";
  }

  private static function generarFiltroEstado(bool $esGerente, string $modoActual, ?string $filtroEstado): string
  {
    if (!$esGerente || $modoActual !== 'historial') {
      return '';
    }

    $etiquetas      = self::getEtiquetasEstado();
    $opcionesFiltro = '<option value="">Todos</option>';
    foreach ($etiquetas as $valor => $etiqueta) {
      $selected        = ($filtroEstado === $valor) ? ' selected' : '';
      $opcionesFiltro .= "<option value=\"{$valor}\"{$selected}>{$etiqueta}</option>";
    }

    return <<<FILTRO
    <div class="filtros-pedidos">
        <form method="GET" action="">
            <input type="hidden" name="modo" value="{$modoActual}">
            <label for="estado">Filtrar por estado:</label>
            <select name="estado" id="estado" onchange="this.form.submit()">
                {$opcionesFiltro}
            </select>
        </form>
    </div>
    FILTRO;
  }

  private static function generarTarjetas(array $pedidos, bool $esGerente, bool $esCamarero, bool $esCocinero): string
  {
    if (!$pedidos || count($pedidos) === 0) {
      return '<p>No hay pedidos en este apartado.</p>';
    }

    $etiquetas = self::getEtiquetasEstado();
    $clases    = self::getClasesEstado();
    $tarjetas  = '';

    foreach ($pedidos as $p) {
      $id           = $p->getId();
      $numeroPedido = htmlspecialchars($p->getNumeroPedido());
      $fecha        = $p->getFechaCreacion()->format('d/m/Y H:i');
      $estadoVal    = $p->getEstado()->value;
      $estadoLabel  = htmlspecialchars($etiquetas[$estadoVal] ?? $estadoVal);
      $estadoClase  = $clases[$estadoVal] ?? '';
      $tipoLabel    = $p->getTipo()->value === Tipo::ParaTomar->value ? 'Para tomar' : 'Para llevar';
      $tipoClase    = $p->getTipo()->value === Tipo::ParaTomar->value ? 'tipo-local' : 'tipo-llevar';

      if ($estadoVal === Estado::Nuevo->value) {
        $verUrl = RUTA_VISTAS . '/pedidos/miCarrito.php?id=' . $id;
      } elseif ($estadoVal === Estado::Recibido->value && !$esCamarero && !$esGerente) {
        $verUrl = RUTA_VISTAS . '/pedidos/pedidospay.php?id=' . $id;
      } else {
        $verUrl = RUTA_VISTAS . '/pedidos/pedidosdetail.php?id=' . $id;
      }

      $htmlTotal = '';
      if (!$esCocinero) {
        $total     = number_format($p->getTotalConDescuento(), 2, ',', '.');
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

    return $tarjetas;
  }
}
