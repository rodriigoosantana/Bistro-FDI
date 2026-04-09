<?php
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Usuario\UsuarioDB;

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

$saldoCliente = null;
if (
    Aplicacion::estaLogueado()
    && !empty($_SESSION['nombreUsuario'])
) {
    $usuarioActual = UsuarioDB::buscarPorNombre($_SESSION['nombreUsuario']);
    if ($usuarioActual) {
        $saldoCliente = (int) $usuarioActual->getSaldoBistrocoins();
        $_SESSION['saldo'] = $saldoCliente;
    }
}
?>
<header>
    <img src="<?php echo RUTA_IMGS; ?>/logo2.png" alt="Logo de Bistro FDI">
    <h1><?= $tituloHeader ?></h1>
    <div class="saludo">
        <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <img src="<?= RUTA_APP . htmlspecialchars($_SESSION['avatar']) ?>" width="80" height="80" alt="Avatar">
            <?php if ($saldoCliente !== null): ?>
                <div class="saldo-cliente" title="Saldo actual en BistroCoins">
                    <span class="moneda-bistro" aria-hidden="true">
                        <img src="<?= RUTA_IMGS . '/bistroCoins.png' ?>" alt="BistroCoins">
                    </span>
                    <span class="saldo-valor"><?= number_format($saldoCliente, 0, ',', '.') ?></span>
                </div>
            <?php endif; ?>
        <?php else: ?>
            Usuario desconocido. Inicia sesión para acceder a las funcionalidades.
        <?php endif; ?>
    </div>
</header>
