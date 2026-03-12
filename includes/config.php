<?php

define('RAIZ_APP', dirname(__DIR__));
define('RUTA_APP', '/Bistro-FDI');
define('RUTA_VISTAS', RUTA_APP . '/includes/vistas');
define('RUTA_IMGS', RUTA_APP . '/img');
define('RUTA_CSS', RUTA_APP . '/css');
define('RUTA_JS', RUTA_APP . '/js');

define('BD_HOST', 'localhost');
define('BD_NAME', 'bistro_fdi');
define('BD_USER', 'bistro_fdi');
define('BD_PASS', 'bistro_fdi');

// session_start();

ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');

spl_autoload_register(function ($class) {

  $prefix = 'es\\ucm\\fdi\\aw\\';
  $base_dir = __DIR__ . '/';  // __DIR__ es includes/

  $len = strlen($prefix);

  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  $relative_class = substr($class, $len);

  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});

use es\ucm\fdi\aw\Aplicacion;

$app = Aplicacion::getInstance();

$app->init(['host' => BD_HOST, 'bd' => BD_NAME, 'user' => BD_USER, 'pass' => BD_PASS]);

register_shutdown_function([$app, 'shutdown']);
