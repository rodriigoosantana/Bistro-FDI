<?php
function mostrarSaludo() 
{
    if (isset($_SESSION['login']) && ($_SESSION['login'] === true)) 
    {
        return "Bienvenido, {$_SESSION['nombre']} <a href=' " . RUTA_VISTAS . "/logout.php'>(salir)</a>";
        
    } 
    else 
    {
        return "Usuario desconocido. <a href=' " . RUTA_VISTAS . "/login.php'>Login</a>";
    }
}
?>
<header>
    <img src="<?php echo RUTA_IMGS; ?>/logo2.png" alt="Logo de Bistro FDI" width="400">
    <h1> <?= $tituloHeader ?></h1>
    <div class="saludo"><?= mostrarSaludo(); ?></div>
</header>
