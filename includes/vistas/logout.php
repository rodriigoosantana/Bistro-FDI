 <?php

require_once dirname(__DIR__,2).'/includes/config.php';

$tituloPagina = 'Logout';
$tituloHeader = 'Logout';

//Doble seguridad: unset + destroy
unset($_SESSION['login']);
unset($_SESSION['esAdmin']);
unset($_SESSION['nombre']);

session_destroy();

$contenidoPrincipal=<<<EOS
    <section id="contenido">
        <h2>Hasta pronto!</h2>
    </section>
EOS;

require("common/plantilla.php");
        ?>
