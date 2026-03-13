<!DOCTYPE html>
<html lang="es">

<head>
  <title><?= $tituloPagina ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="authors" content="Grupo 1">
  <meta name="keywords" content="UCM, AW">
  <meta name="description" content="Proyecto AW">
  <link rel="stylesheet" type="text/css" href="<?php echo RUTA_CSS . '/estilos.css' ?>" />
</head>

<body>
  <?php include(RAIZ_APP . '/includes/vistas/common/header.php'); ?>
  <div id="contenedor">
    <?php include(RAIZ_APP . '/includes/vistas/common/nav.php'); ?>

    <main>

      <?php
      //Paso 8.4: Implementación de atributos de petición -> mostrar mensajes flash de la petición anterior 
      $app = \es\ucm\fdi\aw\Aplicacion::getInstance();
      $mensajes = $app->getAtributoPeticion('mensajes'); # obtener los mensajes flash de la petición actual
      if ($mensajes) {
        echo '<div class="mensajes-flash">'; # mostrar los mensajes flash en un contenedor específico
        foreach ($mensajes as $msg) {
          echo '<p>' . htmlspecialchars($msg) . '</p>'; # mostrar cada mensaje flash, escapando para evitar XSS
        }
        echo '</div>';
      }
      ?>
      
      <?php if ($acceso ?? true) {
        echo $contenidoPrincipal;
      } else {
        echo "<h1>Acceso Denegado</h1>";
      }
      ?>
    </main>

    <?php include(RAIZ_APP . '/includes/vistas/common/aside.php'); ?>
  </div>
  <?php include(RAIZ_APP . '/includes/vistas/common/footer.php'); ?>
  <!-- JS al final del body para que el DOM esté listo -->
  <script src="<?php echo RUTA_JS . '/slider.js' ?>"></script>
  <script src="<?php echo RUTA_JS . '/confirmar.js' ?>"></script>
</body>

</html>