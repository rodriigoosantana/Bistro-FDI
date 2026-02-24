<?php
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';

class formularioLogin extends formularioBase
{
   //region Constructor

   public function __construct() 
   {
      parent::__construct('formLogin', ['urlRedireccion' => RUTA_APP . '/index.php']);
   }

   //endregion

   //region Métodos protegidos

   protected function generaCamposFormulario(&$datos)
   {
     $nombreUsuario = $datos['nombreUsuario'] ?? '';
    
     $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
     $erroresCampos = self::generaErroresCampos(['nombreUsuario', 'password'], $this->errores);

$html = <<<EOF
{$htmlErroresGlobales}

<fieldset>
    <legend>Usuario y contraseña</legend>
    <br>

    <div>
        <label for="nombreUsuario">Nombre de usuario:</label><br>
        <input id="nombreUsuario" type="text" name="nombreUsuario" value="{$nombreUsuario}" required />
        {$erroresCampos['nombreUsuario']}
    </div>

    <br>

    <div>
        <label for="password">Password:</label><br>
        <input id="password" type="password" name="password" required />
        {$erroresCampos['password']}
    </div>

    <br>

    <div>
        <button type="submit" name="login">
            <img src="http://clipart-library.com/images/8c6oBGz9i.png" alt="enviar" width="19" height="12">
            Entrar
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
            $this->errores['nombreUsuario'] = 'El nombre de usuario no puede estar vacío o tener logitud menor a 4';
      }

      $password = trim($datos['password'] ?? '');
      
      $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      
      if ( ! $password || empty($password) || strlen($password) < 4) 
      {
            $this->errores['password'] = 'El password no puede estar vacío.';
      }
      
      if (count($this->errores) === 0) 
      {
            $usuario = Usuario::login($nombreUsuario, $password);
   
            if (!$usuario) 
            {
               $this->errores[] = "El usuario o el password no coinciden";
            } 
            else 
            {
               $_SESSION['login'] = true;
               $_SESSION['nombre'] = $usuario->getNombre();
               $_SESSION['esAdmin'] = $usuario->tieneRol(Usuario::ADMIN_ROLE);
            }
      }
   }

   //endregion
}

?>
