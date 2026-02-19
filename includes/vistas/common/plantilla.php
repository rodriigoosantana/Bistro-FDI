<!DOCTYPE html>
<html>
    <head>
        <title><?= $tituloPagina ?></title>
        <meta charset="utf-8">
        <meta name="author" content="Humberto Cortés"> 
        <meta name="keywords" content="web, personal">
        <meta name="description" content="Sitio web personal">
        <link rel="stylesheet" type="text/css" href="<?php echo PATH_INDEX . 'css/estilo.css' ?>" />
    </head>
    <body>
        <div id="contenedor">
            <?php
                include(PATH_VISTAS . 'common/header.php');
                include(PATH_VISTAS . 'common/nav.php');
            ?>

            <main>
                <?= $contenidoPrincipal ?>
            </main>

            <?php
                include(PATH_VISTAS . 'common/aside.php');
                include(PATH_VISTAS . 'common/footer.php');
            ?>
        </div> 
    </body>
</html>
