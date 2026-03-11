<?php 
require_once __DIR__.'/Aplicacion.php';


/*
Gestor de excepciones personalizado para manejar errores no capturados en la aplicación.
- Registra el error en el log del servidor con detalles completos (tipo, mensaje, archivo, línea y traza).
- Envía una respuesta HTTP 500 al cliente.
- Se define ANTES de inicializar la aplicación para cubrir también posibles fallos durante la propia inicialización.
*/
function gestorExcepciones(Throwable $ex): void
{
    // Loguear fichero, línea y mensaje completo 
    error_log(
        '[Bistro FDI] ' . get_class($ex) . ': ' . $ex->getMessage()
        . ' en ' . $ex->getFile() . ':' . $ex->getLine()
        . "\n" . $ex->getTraceAsString()
    ); 

    // Enviar respuesta HTTP 500 al cliente
    http_response_code(500);

    // Intentamos mostrar la plantilla si las constantes ya están definidas
    $tituloPagina  = 'Error';
    $tituloHeader  = 'Error';
    $contenidoPrincipal = <<<HTML
        <section id="contenido">
            <h2>¡Vaya! Algo ha salido mal</h2>
            <p>Se ha producido un error inesperado en el servidor.</p>
            <p>Por favor, inténtalo de nuevo más tarde o contacta con el administrador.</p>
        </section>
    HTML;

    if (defined('RAIZ_APP')) {
        require RAIZ_APP . '/includes/vistas/common/plantilla.php';
    } else {
        echo '<h1>Error interno del servidor</h1>';
        echo '<p>Por favor, inténtalo de nuevo más tarde.</p>';
    }   
    exit();
}

// Registrar el gestor de excepciones personalizado
set_exception_handler('gestorExcepciones');


  define('RAIZ_APP', dirname(__DIR__));
  define('RUTA_APP', '/Bistro-FDI');
  define('RUTA_VISTAS', RUTA_APP.'/includes/vistas');
  define('RUTA_IMGS', RUTA_APP.'/img');
  define('RUTA_CSS', RUTA_APP.'/css');
  define('RUTA_JS', RUTA_APP.'/js');
  
  define('BD_HOST', 'localhost');
  define('BD_NAME', 'bistro_fdi');
  define('BD_USER', 'bistro_fdi');
  define('BD_PASS', 'bistro_fdi');
  
 // session_start();

  ini_set('default_charset', 'UTF-8');
  setLocale(LC_ALL, 'es_ES.UTF.8');
  date_default_timezone_set('Europe/Madrid');

  $app = Aplicacion::getInstance();

  $app->init(['host'=>BD_HOST, 'bd'=>BD_NAME, 'user'=>BD_USER, 'pass'=>BD_PASS]);

  register_shutdown_function([$app, 'shutdown']);
?>
