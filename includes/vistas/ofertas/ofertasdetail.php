<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\vistas\ofertas\FormularioOferta;
use es\ucm\fdi\aw\vistas\ofertas\GenerarDetalleOferta;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente = Aplicacion::esGerente();

if (!isset($_GET['id']) && !$esGerente) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

$oferta = null;
if (isset($_GET['id'])) {
    $oferta = OfertaService::buscarPorId(intval($_GET['id']));
    if (!$oferta) {
        header('Location: ' . RUTA_VISTAS . '/ofertas/ofertaslist.php');
        exit();
    }
}

$listaUrl = RUTA_VISTAS . '/ofertas/ofertaslist.php';

if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'toggleActiva' && $oferta) {
    OfertaService::cambiarEstado($oferta->getId(), !$oferta->isActiva());
    header('Location: ' . RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId());
    exit();
}

$modoEdicion = ($esGerente && (isset($_GET['editar']) || isset($_POST['formId']) || !$oferta));

if ($modoEdicion) {
    $form          = new FormularioOferta($oferta);
    $htmlContenido = $form->gestiona();
    $tituloPagina  = $oferta ? 'Editar oferta' : 'Nueva oferta';
    $tituloHeader  = $tituloPagina;

    $btnToggleEdicion = '';
    if ($oferta) {
        $toggleLabel = $oferta->isActiva() ? 'Desactivar' : 'Reactivar';
        $toggleClass = $oferta->isActiva() ? 'btn btn-borrar' : 'btn btn-nuevo';
        $confirmMsg  = $oferta->isActiva() ? '¿Desactivar esta oferta?' : '¿Reactivar esta oferta?';
        $toggleUrl   = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId();
        $btnToggleEdicion = <<<BTN
            <form method="POST" action="{$toggleUrl}" style="display:inline"
                  data-confirm="{$confirmMsg}">
                <input type="hidden" name="accion" value="toggleActiva">
                <button type="submit" class="{$toggleClass}">{$toggleLabel}</button>
            </form>
        BTN;
    }

    $contenidoPrincipal = <<<EOS
        <section id="contenido">
            <h2>{$tituloPagina}</h2>
            {$htmlContenido}
            <div class="acciones-pagina">
                <a href="{$listaUrl}" class="btn btn-volver">← Volver a la lista</a>
                {$btnToggleEdicion}
            </div>
        </section>
    EOS;
} else {
    $tituloPagina       = htmlspecialchars($oferta->getNombre());
    $tituloHeader       = 'Detalle de oferta';
    $contenidoPrincipal = GenerarDetalleOferta::generar($oferta, $listaUrl, $esGerente);
}

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
