<!DOCTYPE html>
<?php require_once __DIR__.'/includes/config.php' ?>
<html lang="es">
    <head>
        <title><?= $tituloPagina ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" >
        <meta name="authors" content="Grupo 1"> 
        <meta name="keywords" content="UCM, AW">
        <meta name="description" content="Proyecto AW">
        <link rel="stylesheet" type="text/css" href="<?php echo RUTA_CSS . '/estilos.css' ?>" />
    </head>
    <body>
        <div id="contenedor">
            <?php
                include(RAIZ_APP . '/includes/vistas/common/header.php');
                include(RAIZ_APP . '/includes/vistas/common/nav.php');
            ?>

            <main>
                <?= $contenidoPrincipal ?>
            </main>

            <?php
                include(RAIZ_APP . '/includes/vistas/common/aside.php');
                include(RAIZ_APP . '/includes/vistas/common/footer.php');
            ?>
        </div> 
    </body>
</html>
