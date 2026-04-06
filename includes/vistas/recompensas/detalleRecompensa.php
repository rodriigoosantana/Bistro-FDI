<?php
require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\vistas\recompensas\detallarRecompensa;
use es\ucm\fdi\aw\vistas\recompensas\FormularioRecompensa;

if (!Aplicacion::estaLogueado()) {
  header('Location: ' . RUTA_VISTAS . '/login.php');
  exit();
}

$esGerente = Aplicacion::esGerente();

/* =========================
   CONTROL ACCESO CREACIÓN
   ========================= */
if (!isset($_GET['id']) && !$esGerente) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit();
}

/* =========================
   CARGAR RECOMPENSA
   ========================= */
$recompensa = null;

if (isset($_GET['id'])) {
  $recompensa = RecompensaService::buscarPorId(intval($_GET['id']));

  if (!$recompensa) {
    header('Location: ' . RUTA_VISTAS . '/recompensas/listaRecompensas.php');
    exit();
  }
}

$volverUrl = RUTA_VISTAS . '/recompensas/listaRecompensas.php';

/* =========================
   ELIMINAR (BORRADO REAL)
   ========================= */
if ($esGerente && isset($_POST['accion']) && $_POST['accion'] === 'borrar' && $recompensa) {

  RecompensaService::eliminar($recompensa);

  header('Location: ' . RUTA_VISTAS . '/recompensas/listaRecompensas.php');
  exit();
}

/* =========================
   MODO EDICIÓN
   ========================= */
$modoEdicion = ($esGerente && isset($_GET['editar']));

if ($esGerente && ($modoEdicion || !$recompensa)) {

  require_once RAIZ_APP . '/includes/vistas/recompensas/FormularioRecompensa.php';

  $form = new FormularioRecompensa($recompensa);
  $htmlContenido = $form->gestiona();

  $tituloPagina = $recompensa ? 'Editar recompensa' : 'Nueva recompensa';

  $contenidoPrincipal = <<<HTML
<section id="contenido">

    <h2>{$tituloPagina}</h2>

    {$htmlContenido}

    <br>

    <a href="{$volverUrl}" class="btn btn-volver">← Volver</a>

</section>
HTML;
} else {

  /* =========================
     MODO VISTA
     ========================= */

  $tituloPagina = 'Detalle recompensa';

  $contenidoPrincipal = detallarRecompensa::generar(
    $recompensa,
    $volverUrl,
    $esGerente
  );
}

require(RAIZ_APP . '/includes/vistas/common/plantilla.php');
