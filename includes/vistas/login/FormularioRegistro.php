<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';

class FormularioRegistro extends formularioBase
{
    private $usuario;
    private $gerente = false;
    public function __construct($usuario = null)
    {
        $this->usuario = $usuario;

        parent::__construct(
            'formRegistro',
            ['urlRedireccion' => RUTA_APP . '/index.php']
        );
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombreUsuario']
            ?? ($this->usuario ? $this->usuario->getNombreUsuario() : '');

        $nombre = $datos['nombre']
            ?? ($this->usuario ? $this->usuario->getNombre() : '');

        $apellidos = $datos['apellidos']
            ?? ($this->usuario ? $this->usuario->getApellidos() : '');

        $email = $datos['email']
            ?? ($this->usuario ? $this->usuario->getEmail() : '');

        $rol = $datos['rol']
            ?? ($this->usuario ? Rol::cargarRol($this->usuario->getId()) : '');
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        $erroresCampos = self::generaErroresCampos(
            ['nombreUsuario', 'nombre', 'email', 'apellidos', 'password', 'password2'],
            $this->errores
        );

        if ($_SESSION['rolId'] == Usuario::ROL_GERENTE) {
            $this->gerente = true;
        }

        $rolHtml = "";
        if ($this->gerente) {
            $rolHtml = <<<HTML
            <br>
            <div>
                <label for="rol">Rol:</label><br>
                <input id="rol" type="text" name="rol" value="$rol"/>
            </div>
            HTML;
        }

        $password2Html = '';

        if ($this->usuario == null) {
            $password2Html = <<<HTML
            <br>
            <div>
                <label for="password2">Reintroduce el password:</label><br>
                <input id="password2" type="password" name="password2" />
                {$erroresCampos['password2']}
            </div>
            HTML;
        }
        
        $html = <<<EOF
        {$htmlErroresGlobales}
        <fieldset>
            <legend>Rellena los campos</legend>
            <br>

            <div>
                <label for="nombreUsuario">Nombre de usuario:</label><br>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="$nombreUsuario" />
                {$erroresCampos['nombreUsuario']}
            </div>
            <br>

            <div>
                <label for="nombre">Nombre:</label><br>
                <input id="nombre" type="text" name="nombre" value="$nombre" />
                {$erroresCampos['nombre']}
            </div>
            <br>

            <div>
                <label for="apellidos">Apellidos:</label><br>
                <input id="apellidos" type="text" name="apellidos" value="$apellidos"/>
                {$erroresCampos['apellidos']}
            </div>
            <br>

            <div>
                <label for="email">Email:</label><br>
                <input id="email" type="text" name="email" value="$email"/>
                {$erroresCampos['email']}
            </div>
            $rolHtml
            <br>
            <div>
                <label for="password">Password:</label><br>
                <input id="password" type="password" name="password"/>
                {$erroresCampos['password']}
            </div>
            $password2Html
            <br>
            <div>
                <button type="submit" name="registro">
                    Aceptar
                </button>
            </div>
        </fieldset>
        EOF;

        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombreUsuario = trim($datos['nombreUsuario'] ?? '');
        $nombreUsuario = filter_var($nombreUsuario, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$nombreUsuario || strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] =
                'El nombre de usuario debe tener al menos 4 caracteres.';
        }
        $usuarioExistente = UsuarioService::buscarPorNombre($nombreUsuario);

        if (($this->usuario == null && $usuarioExistente != null) ||
          ($this->usuario != null && $usuarioExistente != null && $usuarioExistente->getNombreUsuario() !== $this->usuario->getNombreUsuario())) 
        {
          $this->errores['nombreUsuario'] = "El nombre de usuario ya existe";
        }

        $nombre = trim($datos['nombre'] ?? '');
        $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$nombre || strlen($nombre) < 4) {
            $this->errores['nombre'] =
                'El nombre debe tener al menos 4 caracteres.';
       }

        $apellidos = trim($datos['apellidos'] ?? '');
        $apellidos = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$apellidos || strlen($apellidos) < 4) {
            $this->errores['apellidos'] =
                'Los apellidos deben tener al menos 4 caracteres.';
        }

        $email = trim($datos['email'] ?? '');
        $email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$email || strlen($email) < 4) {
            $this->errores['email'] =
                'No es una dirección de correo válida.';
        }

        $password = trim($datos['password'] ?? '');
        $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$password || mb_strlen($password) < 4) {
            $this->errores['password'] =
                'El password debe tener al menos 4 caracteres.';
        }

        $aux = Usuario::ROL_CLIENTE;
        if ($this->gerente) {
            $aux = trim($datos['rol'] ?? '');
            $aux = filter_var($aux, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($aux == "gerente") {
                $aux = Usuario::ROL_GERENTE;
            }
            if ($aux == "cocinero") {
                $aux = Usuario::ROL_COCINERO;
            }
            if ($aux == "camarero") {
                $aux = Usuario::ROL_CAMARERO;
            }
        }

        if (count($this->errores) === 0) {
            $usuario = UsuarioService::buscarPorNombre($nombreUsuario);
            Rol::cambiarRol($usuario->getId(), $aux);
            $dto = new Usuario(
                $nombreUsuario,
                UsuarioService::hashPassword($password),
                $nombre,
                $apellidos,
                $email,
                null,
                $usuario->getId()
            );
            if ($this->usuario != null) {
                if (UsuarioService::actualizar($dto) == null) {
                    $this->errores[] = "Error en la modificación del usuario";
                }
            } else {

                $usuarioInsertado = UsuarioService::insertar($dto);

                if (!$usuarioInsertado) {
                    $this->errores[] = "Error en la creación del usuario";
                } else {
                    $_SESSION['login'] = true;
                    $_SESSION['nombre'] = $usuarioInsertado->getNombre();
                }
            }
        }
    }
}
?>
