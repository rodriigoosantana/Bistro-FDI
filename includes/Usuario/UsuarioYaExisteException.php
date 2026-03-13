<?php

namespace es\ucm\fdi\aw\Usuario;

/*
 * Excepción  de dominio lanzada cuando se intenta crear un usuario con un nombre que ya existe.
*/

class UsuarioYaExisteException extends \RuntimeException
{
    public function __construct(string $nombreUsuario)
    {
        parent::__construct("El nombre de usuario '{$nombreUsuario}' ya está en uso.");
    }
}
