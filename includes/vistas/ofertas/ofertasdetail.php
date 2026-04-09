<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\vistas\ofertas\FormularioOferta;

# solo usuarios logueados
if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit();
}

$esGerente = Aplicacion::esGerente();

# sin id: modo crear, solo gerente
if (!isset($_GET['id']) && !$esGerente) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

# cargar oferta si se pasó id
$oferta = null;
if (isset($_GET['id'])) {
    $oferta = OfertaService::buscarPorId(intval($_GET['id']));
    if (!$oferta) {
        header('Location: ' . RUTA_VISTAS . '/ofertas/ofertaslist.php');
        exit();
    }
}

$listaUrl = RUTA_VISTAS . '/ofertas/ofertaslist.php';

# toggle activa/inactiva (solo gerente, POST)
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'toggleActiva' && $oferta) {
    OfertaService::cambiarEstado($oferta->getId(), !$oferta->isActiva());
    header('Location: ' . RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId());
    exit();
}

# decidir modo: edición/creación o vista
$modoEdicion = ($esGerente && (isset($_GET['editar']) || isset($_POST['formId']) || !$oferta));

if ($modoEdicion) {
    # modo formulario (crear o editar)
    require_once RAIZ_APP . '/includes/vistas/ofertas/FormularioOferta.php';

    $form           = new FormularioOferta($oferta);
    $htmlContenido  = $form->gestiona();

    $tituloPagina   = $oferta ? 'Editar oferta' : 'Nueva oferta';
    $tituloHeader   = $tituloPagina;

    $contenidoPrincipal = <<<EOS
        <section id="contenido">
            <h2>{$tituloPagina}</h2>
            {$htmlContenido}
            <br>
            <a href="{$listaUrl}" class="btn btn-volver">← Volver a la lista</a>
        </section>
    EOS;
} else {
    # modo vista (todos los usuarios logueados)
    $nombre      = htmlspecialchars($oferta->getNombre());
    $descripcion = htmlspecialchars($oferta->getDescripcion());
    $inicio      = $oferta->getInicio()->format('d/m/Y');
    $fin         = $oferta->getFin()->format('d/m/Y');
    $pct         = number_format($oferta->getDescuento() * 100, 0, ',', '.') . ' %';
    
    # badge de estado
    $hoy = new DateTime();
    $hoy = new DateTime();
    if (!$oferta->isActiva()) {
        $badge = '<span class="badge badge-caducada">Inactiva</span>';
    } elseif ($oferta->getInicio() > $hoy) {
        $badge = '<span class="badge badge-futura">Próxima</span>';
    } elseif ($oferta->getFin() < $hoy) {
        $badge = '<span class="badge badge-caducada">Caducada</span>';
    } else {
        $badge = '<span class="badge badge-activa">Activa</span>';
    }

    # construir la tabla de productos del pack
    $lineas     = OfertaService::listarLineasDeOferta($oferta->getId());
    $htmlLineas = '';
    $precioSin  = 0.0;

    foreach ($lineas as $linea) {
        $prod = ProductoService::buscarPorId($linea->getProductoId());
        if (!$prod) continue;

        $nomProd     = htmlspecialchars($prod->getNombre());
        $cant        = $linea->getCantidad();
        $precioUni   = $prod->getPrecioFinal();
        $subtotal    = $precioUni * $cant;
        $precioSin  += $subtotal;

        $precioUniF = number_format($precioUni, 2, ',', '.') . ' €';
        $subtotalF  = number_format($subtotal, 2, ',', '.') . ' €';

        $htmlLineas .= <<<LINEA
            <div class="pack-linea">
                <span class="pack-producto">{$nomProd}</span>
                <span class="pack-cant">× {$cant}</span>
                <span class="pack-precio">{$subtotalF}</span>
            </div>
        LINEA;
    }

    $descuentoEuros = round($precioSin * $oferta->getDescuento(), 2);
    $precioFinal    = max(0.0, $precioSin - $descuentoEuros);
    $precioSinF     = number_format($precioSin, 2, ',', '.') . ' €';
    $descuentoF     = number_format($descuentoEuros, 2, ',', '.') . ' €';
    $precioFinalF   = number_format($precioFinal, 2, ',', '.') . ' €';

    # botones de gerente
    $botonesGerente = '';
    if ($esGerente) {
        $editarUrl = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId() . '&editar=1';
        $toggleLabel = $oferta->isActiva() ? 'Desactivar' : 'Reactivar';
        $toggleClass = $oferta->isActiva() ? 'btn btn-borrar' : 'btn btn-nuevo';
        $confirmMsg  = $oferta->isActiva()
            ? '¿Desactivar esta oferta?'
            : '¿Reactivar esta oferta?';
        $botonesGerente = <<<BTN
            <a href="{$editarUrl}" class="btn btn-editar">Editar</a>
            <form method="POST" action="" style="display:inline"
                  onsubmit="return confirm('{$confirmMsg}')">
                <input type="hidden" name="accion" value="toggleActiva">
                <button type="submit" class="btn {$toggleClass}">{$toggleLabel}</button>
            </form>
        BTN;
    }

    $tituloPagina = $nombre;
    $tituloHeader = 'Detalle de oferta';

    $contenidoPrincipal = <<<EOS
        <section id="contenido">
            <h2>{$nombre} {$badge}</h2>

            <div class="detalle-oferta">
                <p><strong>Descripción:</strong> {$descripcion}</p>
                <p><strong>Vigencia:</strong> {$inicio} – {$fin}</p>
                <p><strong>Descuento:</strong> {$pct}</p>

                <div class="oferta-pack">
                    <h3>Productos del pack</h3>
                    {$htmlLineas}
                    <div class="oferta-resumen-precio">
                        <span class="precio-sin">Precio sin descuento: {$precioSinF}</span>
                        <span class="descuento">– Descuento: {$descuentoF}</span>
                        <span class="precio-con">Precio final: {$precioFinalF}</span>
                    </div>
                </div>
            </div>

            <div class="acciones-pagina">
                <a href="{$listaUrl}" class="btn btn-volver">← Volver</a>
                {$botonesGerente}
            </div>
        </section>
    EOS;
}

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
