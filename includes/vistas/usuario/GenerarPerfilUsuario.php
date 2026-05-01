<?php

namespace es\ucm\fdi\aw\vistas\usuario;

use es\ucm\fdi\aw\Usuario\Rol;

class GenerarPerfilUsuario
{
    public static function generarPerfil($usuario): string
    {
        $nombreUsuarioEsc = htmlspecialchars($usuario->getNombreUsuario());

        $htmlCabecera = self::generarCabeceraUsuario($usuario);
        $htmlTabla    = self::generarTablaInfo($usuario);
        $htmlBotones  = self::generarBotonesAccion($nombreUsuarioEsc);

        return <<<EOS
        <section class="perfil-usuario">
            <div class="perfil-header">
                {$htmlCabecera}
            </div>

            {$htmlTabla}

            <div class="acciones-pagina">
                {$htmlBotones}
            </div>
        </section>
        EOS;
    }

    private static function generarCabeceraUsuario($usuario): string
    {
        $nombreUsuarioEsc = htmlspecialchars($usuario->getNombreUsuario());
        $nombreEsc        = htmlspecialchars($usuario->getNombre());
        $apellidosEsc     = htmlspecialchars($usuario->getApellidos());
        $saldoEsc         = htmlspecialchars($usuario->getSaldoBistrocoins()) . ' BistroCoins';
        $rolNombre        = htmlspecialchars(Rol::cargarRol($usuario->getId())->getNombre());
        $avatarImg        = "<img src='" . RUTA_APP . htmlspecialchars($usuario->getAvatar()) . "' width='80' height='80' alt='Avatar'>";

        return <<<EOS
        <div class="perfil-avatar">
            {$avatarImg}
        </div>
        <div class="perfil-datos-principales">
            <h2>{$nombreUsuarioEsc}</h2>
            <p>{$nombreEsc} {$apellidosEsc}</p>
            <p><strong>Rol:</strong> {$rolNombre}</p>
            <p><strong>Saldo:</strong> {$saldoEsc}</p>
        </div>
        EOS;
    }

    private static function generarTablaInfo($usuario): string
    {
        $nombreUsuarioEsc = htmlspecialchars($usuario->getNombreUsuario());
        $nombreEsc        = htmlspecialchars($usuario->getNombre());
        $apellidosEsc     = htmlspecialchars($usuario->getApellidos());
        $emailEsc         = htmlspecialchars($usuario->getEmail());

        return <<<EOS
        <table class="perfil-tabla">
            <tr>
                <th>ID</th>
                <td>{$usuario->getId()}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td><a href="mailto:{$emailEsc}">{$emailEsc}</a></td>
            </tr>
            <tr>
                <th>Nombre</th>
                <td>{$nombreEsc}</td>
            </tr>
            <tr>
                <th>Apellidos</th>
                <td>{$apellidosEsc}</td>
            </tr>
            <tr>
                <th>Nombre de usuario</th>
                <td>{$nombreUsuarioEsc}</td>
            </tr>
        </table>
        EOS;
    }

    private static function generarBotonesAccion(string $nombreUsuarioEsc): string
    {
        return <<<EOS
        <a class="btn btn-editar" href="usuariosedit.php?nombreUsuario={$nombreUsuarioEsc}">Modificar datos</a>
        <a class="btn btn-borrar" href="usuariosdelete.php?nombreUsuario={$nombreUsuarioEsc}">Eliminar usuario</a>
        EOS;
    }
}
