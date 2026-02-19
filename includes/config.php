<?php 
  define('RAIZ_APP', dirname(__DIR__));
  define('RUTA_APP', '/BISTRO-FDI');
  define('RUTA_VISTAS', RUTA_APP.'/includes/vistas');
  define('RUTA_IMGS', RUTA_APP.'/img');
  define('RUTA_CSS', RUTA_APP.'/css');
  define('RUTA_JS', RUTA_APP.'/js');

  session_start();

  ini_set('default_charset', 'UTF-8');
  setLocale(LC_ALL, 'es_ES.UTF.8');
  date_default_timezone_set('Europe/Madrid');
?>
