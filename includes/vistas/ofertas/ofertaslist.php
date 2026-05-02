<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\vistas\ofertas\GenerarListaOfertas;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente = Aplicacion::esGerente();

$ofertas = $esGerente
    ? OfertaService::listarTodas()
    : OfertaService::listarActivas();

$tituloPagina = 'Gestión de Ofertas';
$tituloHeader = 'Gestión de Ofertas';

$contenidoPrincipal = GenerarListaOfertas::generar($ofertas ?: [], $esGerente);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
