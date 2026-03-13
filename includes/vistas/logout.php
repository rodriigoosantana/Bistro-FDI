 <?php

  require_once dirname(__DIR__, 2) . '/includes/config.php';

  $tituloPagina = 'Logout';
  $tituloHeader = 'Logout';

  //Doble seguridad: unset + destroy
  unset($_SESSION['login']);
  unset($_SESSION['userId']);
  unset($_SESSION['nombre']);
  unset($_SESSION['rolId']);
  unset($_SESSION['nombreUsuario']);

  session_destroy();

  $contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Hasta pronto!</h2>
    </section>
EOS;

  require("common/plantilla.php");
  ?>
