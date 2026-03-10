<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Contacto';
$tituloHeader = 'Cómo Contactarnos';
$contenidoPrincipal = <<<EOS
    <div id="contenedor">
        <!-- Contenido principal -->
        <main>
    <form action="mailto:nucieda@ucm.es" method="post" enctype="text/plain">
			<label for="nombre">Nombre:</label><br>
			<input type="text" id="nombre" name="nombre" required><br>

			<label for="correo">Correo Electronico:</label><br>
			<input type="email" id="correo" name="correo" required><br>

			<label for="evaluacion">Evaluacion</label>
			<input type="radio" id="evaluacion" name="motivo" value="evaluacion" required><br>
			<label for="sugerencias">Sugerencias</label>
			<input type="radio" id="sugerencias" name="motivo" value="sugerencias" required><br>
			<label for="criticas">Criticas</label>
			<input type="radio" id="criticas" name="motivo" value="criticas" required><br>

			<label for="termsandconditions">Marque esta casilla para verificar que ha leído nuestros términos y condiciones del servicio:</label>
			<input type="checkbox" id="termsandconditions" name="termsandconditions" required><br>

			<label for="consulta">Consulta:</label><br>
			<textarea id="consulta" name="consulta" required></textarea>

			<button type="submit">Enviar</button>
		</form>

                    </main>
    </div>
EOS;

$listaCaracteristicas = [
	"⏰Respuesta en menos de 24 horas",
	"👷Atención personalizada",

];

require("includes/vistas/common/plantilla.php");
?>
