<?php
      require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
      require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
      require_once RAIZ_APP . '/includes/Usuario/UsuarioService.php';

      class FormularioRegistro extends formularioBase
      {
        private $usuario;
         //region Constructor

         public function __construct($usuario = null) 
         {
            $this->usuario = $usuario;
            parent::__construct('formRegistro', ['urlRedireccion' => RUTA_APP . '/index.php']);
         }
         
         //endregion

         //region Métodos protegidos

         protected function generaCamposFormulario(&$datos)
         {
            $nombreUsuario = $datos['nombreUsuario'] ?? '';
            $nombre = $datos['nombre'] ?? '';

            // Se generan los mensajes de error si existen.
            $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
            
            $erroresCampos = self::generaErroresCampos(['nombreUsuario', 'nombre', 'email', 'apellidos', 'password', 'password2'], $this->errores);

            $html = <<<EOF
            {$htmlErroresGlobales}
            <fieldset>
                  <legend>Datos para el registro</legend>
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
                     <input id="apellidos" type="text" name="apellidos"/>
                     {$erroresCampos['apellidos']}
                  </div>
                  <br>
                  <div>
                     <label for="email">Email:</label><br>
                     <input id="email" type="text" name="email"/>
                     {$erroresCampos['email']}
                  </div>
                  <br>
                  <div>
                     <label for="password">Password:</label><br>
                     <input id="password" type="password" name="password" />
                     {$erroresCampos['password']}
                  </div>
                  <br>
                  <div>
                     <label for="password2">Reintroduce el password:</label><br>
                     <input id="password2" type="password" name="password2" />
                     {$erroresCampos['password2']}
                  </div>
                  <br>
                  <div>
                     <button type="submit" name="registro">
                        <img src="http://clipart-library.com/images/8c6oBGz9i.png" alt="enviar" width="19" height="12">
                        Registrar
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
            
            if ( ! $nombreUsuario || strlen($nombreUsuario) < 4) 
            {
                  $this->errores['nombreUsuario'] = 'El nombre de usuario tiene que tener una longitud de al menos 4 caracteres.';
            }

            $nombre = trim($datos['nombre'] ?? '');
            
            $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ( ! $nombre || strlen($nombre) < 4) 
            {
                  $this->errores['nombre'] = 'El nombre tiene que tener una longitud de al menos 4 caracteres.';
            }

            $apellidos = trim($datos['apellidos'] ?? '');
            
            $apellidos = filter_var($apellidos, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ( ! $apellidos || strlen($apellidos) < 4) 
            {
                  $this->errores['apellidos'] = 'Los apellidos tienen que tener una longitud de al menos 4 caracteres.';
            }
            $email = trim($datos['email'] ?? '');
            
            $email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ( ! $email || strlen($email) < 4) 
            {
                  $this->errores['email'] = 'No es una dirección de correo válida';
            }

            $password = trim($datos['password'] ?? '');
            
            $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ( ! $password || mb_strlen($password) < 4) 
            {
                  $this->errores['password'] = 'El password tiene que tener una longitud de al menos 4 caracteres.';
            }

            $password2 = trim($datos['password2'] ?? '');
            
            $password2 = filter_var($password2, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ( ! $password2 || $password != $password2 ) 
            {
                  $this->errores['password2'] = 'Los passwords deben coincidir';
            }

            if (count($this->errores) === 0) 
            {
              
                  $usuario = UsuarioService::buscarPorNombre($nombreUsuario);
         
                  if ($usuario != NULL) 
                  {
                     $this->errores[] = "El usuario ya existe";
                  } 
                  else 
                  {
                     $dto = new Usuario($nombreUsuario, UsuarioService::hashPassword($password), $nombre, $apellidos, $email, Usuario::ROL_CLIENTE);
                     $usuario = UsuarioService::insertar($dto);
                     
                     if (!$usuario) 
                     {
                        $this->errores[] = "Error en la creación del usuario";
                     } 
                     else 
                     {
                        $_SESSION['login'] = true;
                        $_SESSION['nombre'] = $usuario->getNombre();
                     }
                  }
            }
         }
      }
   ?>
