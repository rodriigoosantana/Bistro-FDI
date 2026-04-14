<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Oferta\OfertaService;

# solo usuarios logueados pueden ver la lista de ofertas
if (!Aplicacion::estaLogueado()) {
    header('Location: ' .  RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente = Aplicacion::esGerente();

# el gerente ve todas; el resto solo las activas
$ofertas = $esGerente
    ? OfertaService::listarTodas()
    : OfertaService::listarActivas();

# construimos las filas de la lista
$htmlItems = '';
if ($ofertas && count($ofertas) > 0) {
    foreach ($ofertas as $oferta) {
    $nombre      = htmlspecialchars($oferta->getNombre());
    $descripcion = htmlspecialchars($oferta->getDescripcion());
    $inicio      = $oferta->getInicio()->format('d/m/Y');
    $fin         = $oferta->getFin()->format('d/m/Y');
    $pct         = number_format($oferta->getDescuento() * 100, 1, ',', '.') . '%';
    $verUrl      = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId();

    # badge según estado de la oferta
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

    $botonesGerente = '';
    if ($esGerente) {
        $editarUrl   = RUTA_VISTAS . '/ofertas/ofertasdetail.php?id=' . $oferta->getId() . '&editar=1';
        $toggleLabel = $oferta->isActiva() ? 'Desactivar' : 'Reactivar';
        $toggleClass = $oferta->isActiva() ? 'btn btn-borrar' : 'btn btn-nuevo';
        $confirmMsg  = $oferta->isActiva() ? '¿Desactivar esta oferta?' : '¿Reactivar esta oferta?';
        $botonesGerente = <<<BTN
            <a href="{$editarUrl}" class="btn btn-editar">Editar</a>
            <form method="POST" action="{$verUrl}" style="display:inline"
                  data-confirm="{$confirmMsg}">
                <input type="hidden" name="accion" value="toggleActiva">
                <button type="submit" class="{$toggleClass}">{$toggleLabel}</button>
            </form>
        BTN;
    }

    $htmlItems .= <<<ITEM
        <div class="oferta-item">
            <div class="oferta-info">
                <span class="oferta-nombre">{$nombre}</span>
                <span class="oferta-descripcion">{$descripcion}</span>
                <div class="oferta-meta">
                    <span>{$inicio} – {$fin}</span>
                    <span>Descuento: {$pct}</span>
                    {$badge}
                </div>
            </div>
            <div class="oferta-acciones">
                <a href="{$verUrl}" class="btn btn-ver">Ver</a>
                {$botonesGerente}
            </div>
        </div>
    ITEM;
}
} else {
    $htmlItems = '<p>No hay ofertas disponibles.</p>';
}

# botón de nueva oferta solo para el gerente
$btnNueva = '';
if ($esGerente) {
    $nuevaUrl  = RUTA_VISTAS . '/ofertas/ofertasdetail.php';
    $btnNueva  = "<a href=\"{$nuevaUrl}\" class=\"btn btn-nuevo\">Nueva oferta</a>";
}

$tituloPagina  = 'Gestión de Ofertas';
$tituloHeader  = 'Gestión de Ofertas';
$volverUrl = RUTA_APP . '/index.php';

$contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Ofertas</h2>
        <div class="lista-ofertas">
            {$htmlItems}
        </div>
        <div class="acciones-pagina">
            <a href="{$volverUrl}" class="btn btn-volver">Atrás</a>
            {$btnNueva}
        </div>
    </section>
EOS;

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
