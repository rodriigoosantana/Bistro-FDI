<?php

namespace es\ucm\fdi\aw\vistas\login;

use es\ucm\fdi\aw\vistas\common\formularioBase;
use es\ucm\fdi\aw\Usuario\Usuario;
use es\ucm\fdi\aw\Usuario\Rol;
use es\ucm\fdi\aw\Usuario\UsuarioService;

use es\ucm\fdi\aw\Usuario\UsuarioYaExisteException; # Excepción personalizada para indicar que el nombre de usuario ya existe

class FormularioRegistro extends formularioBase
{
  private $usuario;
  private $gerente = false; //True cuando el usuario que accede es un gerente
  public function __construct($usuario = null)
  {
    //$this->usuario = null indica que se registra un nuevo usuario
    //$this->usuario != null indica que se está modificando un usuario

    $this->usuario = $usuario;

    if ($this->usuario != null && $_SESSION['rolId'] == Usuario::ROL_GERENTE) {
      $this->gerente = true;
    }
    parent::__construct(
      'formRegistro',
      [
        'urlRedireccion' => RUTA_APP . '/index.php',
        'enctype' => 'multipart/form-data'
      ]
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

    $avatar = $datos['avatar']
      ?? ($this->usuario ? $this->usuario->getAvatar() : '');

    $rol = $datos['rol']
      ?? ($this->usuario ? Rol::cargarRol($this->usuario->getId())->getId() : '');

    $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

    $erroresCampos = self::generaErroresCampos(
      ['nombreUsuario', 'nombre', 'email', 'avatar', 'apellidos', 'password', 'password2'],
      $this->errores
    );

    $opcionesRoles = '';
    if ($this->gerente) {
      $selCliente = $rol == Usuario::ROL_CLIENTE ? 'selected' : '';
      $selCamarero = $rol == Usuario::ROL_CAMARERO ? 'selected' : '';
      $selCocinero = $rol == Usuario::ROL_COCINERO ? 'selected' : '';
      $selGerente = $rol == Usuario::ROL_GERENTE ? 'selected' : '';
      $opcionesRoles = <<<HTML
      <div>
          <label for="rol">Rol:</label><br>
          <select name="rol" id="rol">
              <option value="4" $selCliente>Cliente</option>
              <option value="3" $selCamarero>Camarero</option>
              <option value="2" $selCocinero>Cocinero</option>
              <option value="1" $selGerente>Gerente</option>
          </select>
      </div>
      <br>
      HTML;
    }

    $password2Html = '';

    if ($this->usuario == null) { //Solo aparece el campo de confirmación de constraseña en un registro, no en una modificación
      $password2Html = <<<HTML
            <div>
                <label for="password2">Reintroduce el password:</label><br>
                <input id="password2" type="password" name="password2" />
                {$erroresCampos['password2']}
            </div>
    HTML;
    }

    $htmlAvatarActual = '';
    if ($this->usuario) {
      $htmlAvatarActual .= '<p><strong>Avatar Actual:</strong></p>';
      $htmlAvatarActual .= "<img src='" . RUTA_APP . $avatar . "' width='80' height='80'>";
    }

    $avataresPredeterminados = [
      '/img/uploads/avatares/default.jpg',
      '/img/uploads/avatares/avatar_predeterminado_1.jpeg',
      '/img/uploads/avatares/avatar_predeterminado_2.jpeg'
    ];

    $htmlAvataresPredeterminados = '<div><p>O escoge un avatar predeterminado:</p>';

    foreach ($avataresPredeterminados as $ruta) {
      $checked = ($avatar === $ruta) ? 'checked' : '';
      $ruta_img = RUTA_APP . $ruta;
      $htmlAvataresPredeterminados .= <<<HTML
        <label>
            <input type="radio" name="avatarPredeterminado" value="$ruta" $checked>
            <img src="$ruta_img" width="50" height="50" alt="Avatar">
        </label>
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

            $htmlAvatarActual

            <div>
              <label for="avatar">Añade un Avatar:</label><br>
              <input id="avatar" type="file" name="avatar"/>
              {$erroresCampos['avatar']}
            </div>

            $htmlAvataresPredeterminados 

            <br>
            $opcionesRoles

            <div>
                <label for="password">Password:</label><br>
                <input id="password" type="password" name="password"/>
                {$erroresCampos['password']}
            </div>
            <br>

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
      ($this->usuario != null && $usuarioExistente != null && $usuarioExistente->getNombreUsuario() !== $this->usuario->getNombreUsuario())
    ) {
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

    $avatar_file = $_FILES['avatar'] ?? null;
    $extensiones = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    $avatar_predeterminado = trim($datos['avatarPredeterminado'] ?? null);

    if ($avatar_file && $avatar_file['error'] === UPLOAD_ERR_OK) { //Control de errores si se ha subido un avatar

      if ($avatar_file['size'] <= 0 || !in_array(mime_content_type($avatar_file['tmp_name']), $extensiones)) {
        $this->errores['avatar'] = 'Error al subir el archivo.';
      }
    }

    if (!($avatar_file && $avatar_file['error'] === UPLOAD_ERR_OK)) { //Si no se ha subido avatar
      if (!$avatar_predeterminado) { //Ni predeterminado
        if ($this->usuario == null) { //Si es un registro
          $avatar = "/img/uploads/avatares/default.jpg";
        } else { //Si es una modificacion
          $avatar = $this->usuario->getAvatar(); //Se usa el que ya se tenía
        }
      } else { //Si se ha subido predeterminado
        $avatar = $avatar_predeterminado;
      }
    } else { //Si se ha subido avatar 
      $avatar = UsuarioService::procesarAvatar($avatar_file); //Se usa el subido
    }

    $password = trim($datos['password'] ?? '');
    $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($this->usuario == null && (!$password || mb_strlen($password) < 4)) {
      $this->errores['password'] =
        'El password debe tener al menos 4 caracteres.';
    }

    if ($this->gerente) {
      $rol = intval($datos['rol']);
    } else if ($usuarioExistente != null) {
      $rol = Rol::cargarRol($usuarioExistente->getId())->getId();
    } else {
      $rol = Usuario::ROL_CLIENTE;
    }

    if (count($this->errores) === 0) {
      $nombreUsuarioOriginal = $_GET['nombreUsuario'] ?? null;
      $usuario = UsuarioService::buscarPorNombre($nombreUsuarioOriginal);
      $usuario_id = $usuario != null ? $usuario->getId() : null;


      $dto = new Usuario(
        $nombreUsuario,
        UsuarioService::hashPassword($password),
        $nombre,
        $apellidos,
        $email,
        $avatar,
        $usuario_id
      );

      if ($this->usuario != null) {
        if (UsuarioService::actualizar($dto, $rol) == null) {
          $this->errores[] = "Error en la modificación del usuario";
        }
      } else {
        $usuarioInsertado = UsuarioService::insertar($dto);
        UsuarioService::insertarRoles($usuarioInsertado, Usuario::ROL_CLIENTE);

        if (!$usuarioInsertado) {
          $this->errores[] = "Error en la creación del usuario";
        } else {
          $_SESSION['login'] = true;
          $_SESSION['nombre'] = $usuarioInsertado->getNombre();
          $_SESSION['rolId'] = Rol::cargarRol($usuarioInsertado->getId())->getId();
          $_SESSION['userId'] = $usuarioInsertado->getId();
          $_SESSION['nombreUsuario'] = $usuarioInsertado->getNombreUsuario();
        }
      }
    }
  }
}
