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
    && Aplicacion::esCliente()
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
                        <svg viewBox="0 0 24 24" focusable="false" role="img">
                            <circle cx="12" cy="12" r="8.5"></circle>
                            <path d="M10.2 8.1h2.5a2 2 0 1 1 0 4h-1.1a2 2 0 1 0 0 4h2.6"></path>
                            <line x1="10.9" y1="7" x2="10.9" y2="9"></line>
                            <line x1="13.1" y1="15" x2="13.1" y2="17"></line>
                        </svg>
                    </span>
                    <span class="saldo-valor"><?= number_format($saldoCliente, 0, ',', '.') ?></span>
                </div>
            <?php endif; ?>
        <?php else: ?>
            Usuario desconocido. Inicia sesión para acceder a las funcionalidades.
        <?php endif; ?>
    </div>
</header>
