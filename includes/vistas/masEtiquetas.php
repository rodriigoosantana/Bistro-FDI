<?php

require_once dirname(__DIR__,2).'/includes/config.php';

$tituloPagina = 'Más etiquetas';

$contenidoPrincipal=
    (isset($_SESSION["login"])) 
    ?
        <<<EOS
        <section id="contenido">
            <h2>Más etiquetas</h2>

            <section id="seccion1">
                <h3>Añadiendo una cita</h3>
                <blockquote>
                    <p>Leonard, por favor no te lo tomes a mal, pero el día que ganes el premio Nobel, será el día que comience mi investigación sobre el coeficiente de arrastre de las borlas en las alfombras voladoras.</p>
                    <footer>
                        <p>- Temporada 1. Capítulo 9.<cite> Sheldon Cooper</cite></p>
                    </footer>
                </blockquote>
            </section>

            <section>
                <h3>Imágenes</h3>
                <p>La siguiente imagen se encuentra en <code>http://cursosinformatica.ucm.es/img/CFI-UCM.png</code> y la hemos escalado a 500x100 píxeles.</p>
                
                <img src="http://cursosinformatica.ucm.es/img/CFI-UCM.png" alt="informatica" width="500" height="100">
                
            </section>

            <section>
                <h3>Enlaces</h3>
                <p>Enlace a la <a href="https://www.ucm.es/master-letrasdigitales">web del máster</a></p>
                <p>Enlace a la sección <a href="#seccion1">Añadiendo una cita</a></p>
            </section>
        </section>
        EOS
    :
        <<<EOS
            <section id="contenido">
                <h2>Usuario no registrado!</h2>
                <p>El usuario o contraseña no son válidos.</p>
            </section>
        EOS;

require("common/plantilla.php");
?>
