<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\Producto\ProductoService;

class listarRecompensas
{
  public static function listarRecompensas(bool $esGerente): string
  {
    $tarjetas = "";
    $recompensas = RecompensaService::listarTodos();

    // 💰 saldo usuario
    $saldo = $_SESSION['saldo'] ?? 0;

    // 🔽 filtro
    $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == 1;

    if ($recompensas && count($recompensas) > 0) {

      // 🔽 ordenar por bistrocoins ASC
      usort($recompensas, function ($a, $b) {
        return $a->getBistrocoinsNecesarias() <=> $b->getBistrocoinsNecesarias();
      });

      foreach ($recompensas as $r) {

        $producto = ProductoService::buscarPorId($r->getProductoId());

        if (!$producto) continue;

        $nombreProducto = htmlspecialchars($producto->getNombre());
        $bistrocoins = $r->getBistrocoinsNecesarias();

        // 💡 comprobar disponibilidad
        $disponible = ($saldo >= $bistrocoins);

        // 🔽 aplicar filtro
        if ($soloDisponibles && !$disponible) {
          continue;
        }

        // 🎨 CLASE VISUAL (ESTA ES LA CLAVE)
        $claseDisponibilidad = $disponible
          ? 'recompensa-disponible'
          : 'recompensa-no-disponible';

        // 🏷️ TEXTO VISUAL
        $estadoTexto = $disponible
          ? "<span class='recompensa-ok'>Disponible</span>"
          : "<span class='recompensa-ko'>No disponible</span>";

        // 📷 imagen
        $imagenes = ProductoService::listarImagenes($producto->getId());

        if (!empty($imagenes)) {
          $ruta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);
          $htmlImg = "<img class=\"tarjeta-img-unica\" src=\"{$ruta}\" alt=\"{$nombreProducto}\">";
        } else {
          $htmlImg = "<div class=\"tarjeta-sin-imagen\"><em>Sin imagen</em></div>";
        }

        $detalleUrl = "detalleRecompensa.php?id={$r->getId()}";

        // 🔥 IMPORTANTE: clase añadida aquí ↓↓↓
        $tarjetas .= <<<TARJETA
        <div class="tarjeta-producto {$claseDisponibilidad}">
            
            <div class="tarjeta-imagen">
                {$htmlImg}
            </div>

            <div class="tarjeta-info">
                <strong>{$nombreProducto}</strong>
                <span class="tarjeta-precio">{$bistrocoins} BistroCoins</span>
                <small>{$estadoTexto}</small>
            </div>

            <div class="tarjeta-acciones">
                <a href="{$detalleUrl}" class="btn btn-ver">Ver</a>
            </div>

        </div>
        TARJETA;
      }
    } else {
      $tarjetas = '<p>No hay recompensas disponibles.</p>';
    }

    // 🔁 botón filtro (IMPORTANTE: clase activa)
    $filtroActivo = $soloDisponibles ? 'btn-filtrar-activo' : '';

    $filtroUrl = $soloDisponibles
      ? "listaRecompensas.php"
      : "listaRecompensas.php?disponibles=1";

    $textoFiltro = $soloDisponibles
      ? "Mostrar todas"
      : "Mostrar solo disponibles";

    $btnFiltro = <<<HTML
      <a href="{$filtroUrl}" class="btn-filtrar {$filtroActivo}">
        {$textoFiltro}
      </a>
    HTML;

    // 🔙 y ➕ botones
    $volverUrl = RUTA_APP . '/index.php';

    $btnCrearNuevo = '';
    if ($esGerente) {
      $crearUrl = RUTA_VISTAS . '/recompensas/detalleRecompensa.php';
      $btnCrearNuevo = "<a href=\"{$crearUrl}\" class=\"btn btn-nuevo\">Crear nueva</a>";
    }

    return <<<HTML
    <section id="contenido">

        <div class="acciones-pagina">
            {$btnFiltro}
        </div>

        <div class="lista-productos">
            {$tarjetas}
        </div>

        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$btnCrearNuevo}
        </div>

    </section>
    HTML;
  }
}
