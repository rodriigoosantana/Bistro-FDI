<?php

require_once dirname(__DIR__,2).'/includes/config.php';

$tituloPagina = 'Formularios';

$contenidoPrincipal = 
    (isset($_SESSION["esAdmin"])) 
    ?

        <<<EOS
            <section id="contenido">
                <h2>Formularios</h2>

                <form action="procesar.php" method="get">
                    <fieldset>
                        <legend>Información personal:</legend>
                        <br>
                        <label>Email:
                            <input type="email" name="correo" placeholder="nombre@dominio.com" required>
                        </label>
                        <br><br>

                        <label>Lenguaje:
                            <select name="lenguaje">
                                <optgroup label="Orientado a objetos">
                                <option value="cplusplus">C++</option>
                                <option value="java" selected>Java</option>
                                </optgroup>
                                <optgroup label="funcional">
                                <option value="js">Javascript</option>
                                <option value="python">Python</option>
                                </optgroup>
                            </select>
                        </label>
                        <br><br>

                        <label>
                            Lenguajes:
                            <label><input type="checkbox" name="java" value="Java"> Java</label>
                            <label><input type="checkbox" name="cplusplus" value="C++"> C++</label>
                            <label><input type="checkbox" name="csharp" value="C#"> C#</label>
                            <label><input type="checkbox" name="otros" value="Otros"> Otros</label>
                        </label>
                        <br><br>

                        <label>
                            Empleo actual:<br>
                            <label><input type="radio" name="empleoactual" value="tiempoCompleto"> Tiempo completo</label> <br>
                            <label><input type="radio" name="empleoactual" value="tiempoParcial"> Tiempo parcial</label> <br>
                            <label><input type="radio" name="empleoactual" value="sinempleo" checked> Sin empleo</label>
                        </label>
                        <br><br><br>

                        <button type="submit" name="enviar" value="send">
                            <img src="http://clipart-library.com/images/8c6oBGz9i.png" alt="enviar" width="19" height="12">
                            Enviar
                        </button>
                        <button type="reset" name="borrar">
                            <img src="http://clipart-library.com/images/8iz8A7gxT.png" alt="borrar" width="19" height="12">
                            Borrar
                        </button>
                    </fieldset>
                </form>
                
            </section>
        EOS
    :
        <<<EOS
            <section id="contenido">
                <h2>Formulario de administradores</h2>
                <p>Acceso solo para los administradores.</p>
            </section>
        EOS;

require("common/plantilla.php");
?>
