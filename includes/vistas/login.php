<?php

require_once dirname(__DIR__,2).'/includes/config.php';

$tituloPagina = 'Login';
$tituloHeader = 'Login';

$contenidoPrincipal=<<<EOS
    <section id="contenido">
        <h2>Acceso al sistema</h2>

        <form action="procesarLogin.php" method="POST">
            <fieldset>
                <legend>Usuario y contraseña</legend>
                <br>
                <div>
                    <label for="nombreUsuario">Nombre de usuario:</label><br>
                    <input id="nombreUsuario" type="text" name="nombreUsuario" />
                </div>
                <br>
                <div>
                    <label for="password">Password:</label><br>
                    <input id="password" type="password" name="password" />
                </div>
                <br>
                <div>
                    <button type="submit" name="login">
                        <img src="http://clipart-library.com/images/8c6oBGz9i.png" alt="enviar" width="19" height="12">
                        Entrar
                    </button>
                </div>
            </fieldset>
        </form>
        
    </section>
EOS;

require("common/plantilla.php");
?>
