<?php
function mostrarSaludo() 
{
    if (isset($_SESSION['login']) && ($_SESSION['login'] === true)) 
    {
        return "Bienvenido, {$_SESSION['nombre']}";
        
    } 
    else 
    {
        return "Usuario desconocido. Inicia sesión para acceder a las funcionalidades.";
    }
}
?>
<header>
    <img src="<?php echo RUTA_IMGS; ?>/logo2.png" alt="Logo de Bistro FDI">
    <h1> <?= $tituloHeader ?></h1>
    <div class="saludo"><?= mostrarSaludo(); ?></div>
</header>
