<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\vistas\recompensas\GenerarListaRecompensas;

if (!Aplicacion::estaLogueado()) {
    header('Location: ' . RUTA_VISTAS . '/usuario/login.php');
    exit();
}

$esGerente      = Aplicacion::esGerente();
$soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == 1;
$saldo           = intval($_SESSION['saldo'] ?? 0);
$recompensas     = RecompensaService::listarTodos() ?: [];

$tituloPagina = 'Lista Recompensas';
$tituloHeader = 'Lista Recompensas';

$contenidoPrincipal = GenerarListaRecompensas::generar($recompensas, $esGerente, $saldo, $soloDisponibles);

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
