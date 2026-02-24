<?php

require_once dirname(__DIR__,2).'/includes/config.php';

$tituloPagina = 'Listas y tablas';


$contenidoPrincipal=
    (isset($_SESSION["login"])) 
    ?
        <<<EOS
            <section id="contenido">
                <h2> Listas y tablas</h2>

                <section>
                    <h3>Listas</h3>
                    <p>Repasemos lo que hemos aprendido hasta ahora:</p>
                    <ul>
                        <li>Formatear texto</li>
                        <li>Insertar imágenes</li>
                        <li>Crear enlaces</li> 
                    </ul>
                </section>

                <section>
                    <h3>Tablas</h3>
                    <table border="1">
                        <caption>Tabla de prueba</caption>
                        <thead>
                            <tr>
                                <th>Etiqueta</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>em</td>
                                <td>Resaltar texto</td>
                            </tr>
                            <tr>
                                <td>img</td>
                                <td>Insertar una imagen</td>
                            </tr>
                            <tr>
                                <td>a</td>
                                <td>Insertar un enlace</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Pie de tabla</td>
                            </tr>
                        </tfoot>
                    </table>
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
