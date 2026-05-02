<?php

namespace es\ucm\fdi\aw\vistas\usuario;

use es\ucm\fdi\aw\Usuario\Rol;

class GenerarListaUsuarios
{
    public static function generar(array $usuarios): string
    {
        $tarjetas = self::generarTarjetas($usuarios);

        return <<<EOS
        <section id="contenido">
            <h2>Usuarios</h2>
            <div class="lista-categorias">
                {$tarjetas}
            </div>
        </section>
        EOS;
    }

    private static function generarTarjetas(array $usuarios): string
    {
        $html = '';
        foreach ($usuarios as $u) {
            $html .= self::generarTarjeta($u);
        }
        return $html;
    }

    private static function generarTarjeta($u): string
    {
        $rol        = Rol::cargarRol($u->getId());
        $avatarImg  = "<img src='" . RUTA_APP . $u->getAvatar() . "' alt='{$u->getNombreUsuario()}'>";
        $perfilUrl  = "usuariosdetail.php?nombreUsuario={$u->getNombreUsuario()}";
        $saldo      = $u->getSaldoBistrocoins() . ' BistroCoins';

        return <<<FILA
        <div class="categoria-item">
            <div class="categoria-imagen">
                {$avatarImg}
            </div>
            <div class="categoria-info">
                <strong class="categoria-nombre">{$u->getNombreUsuario()}</strong>
                <span class="categoria-descripcion">
                    {$u->getNombre()} {$u->getApellidos()}
                </span>
                <span class="categoria-descripcion">
                    {$u->getEmail()}
                </span>
                <span class="categoria-descripcion">
                    {$saldo}
                </span>
                <small>Rol: {$rol->getNombre()}</small>
            </div>
            <div class="categoria-acciones">
                <a href="{$perfilUrl}" class="btn btn-ver">Ver perfil</a>
            </div>
        </div>
        FILA;
    }
}
