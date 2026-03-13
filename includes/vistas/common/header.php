<?php
function mostrarSaludo() 
{
    if (isset($_SESSION['login']) && ($_SESSION['login'] === true)) 
    {
        $avatar_img = "<img src='" . RUTA_APP . $_SESSION['avatar'] . "' width='80' height='80'>";
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
    <h1><?= $tituloHeader ?></h1>
    <div class="saludo">
        <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <img src="<?= RUTA_APP . htmlspecialchars($_SESSION['avatar']) ?>" width="80" height="80" alt="Avatar">
        <?php else: ?>
            Usuario desconocido. Inicia sesión para acceder a las funcionalidades.
        <?php endif; ?>
    </div>
</header>
