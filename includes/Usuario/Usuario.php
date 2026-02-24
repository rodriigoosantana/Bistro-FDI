<?php
require_once RAIZ_APP . '/includes/Usuario/RolesUsuario.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';
class Usuario
{
   //region Campos privados

   private $id;

   private $nombreUsuario;

   private $password;

   private $nombre;

   private $roles;

   private $email;

   private $apellidos;

   //endregion
  
   //region Campos estaticos 

   public const ADMIN_ROLE = 1;

   public const USER_ROLE = 2;

   //endregion

   //region Constructor

   private function __construct($nombreUsuario, $password, $nombre, $apellidos, $email, $id = null, $roles = [])
   {
      $this->id = $id;
      $this->nombreUsuario = $nombreUsuario;
      $this->password = $password;
      $this->nombre = $nombre;
      $this->apellidos = $apellidos;
      $this->roles = $roles;
      $this->email = $email;
   }

   //endregion
   
   //region Propiedades

   public function getId()
   {
      return $this->id;
   }

   public function getNombreUsuario()
   {
      return $this->nombreUsuario;
   }

   public function getNombre()
   {
      return $this->nombre;
   }

   public function getRoles()
   {
     return $this->roles;
   }
  
   public function getEmail()
   {
     return $this->email;
   }
  public function getApellidos()
   {
     return $this->Apellidos;
   }

   //endregion

   //region Métodos publicos

   public function tieneRol($role)
   {
      $this->cargarRoles();

      if ($this->roles) 
      {
         return (bool) array_filter($this->roles, function($o) use ($role)
         {
               return $o->getId() === $role;
         });
      }
      
      return false;
   }

   //endregion
   
   //region Métodos privados

   private function compruebaPassword($password)
   {
      return password_verify($password, $this->password);
   }


   private function cargarRoles()
   {
      $this->roles = Rol::cargarRoles($this->id);
   }

   private function insertar()
   {
      $result = false;

      $conn = Aplicacion::getInstance()->getConexionBd();
      
      $query=sprintf("INSERT INTO Usuarios(nombreUsuario, nombre, apellidos, email, password) VALUES ('%s', '%s', '%s', '%s', '%s')"
         , $conn->real_escape_string($this->nombreUsuario)
         , $conn->real_escape_string($this->nombre)
         , $conn->real_escape_string($this->apellidos)
         , $conn->real_escape_string($this->email)
         , $conn->real_escape_string($this->password)
      );

      if ( $conn->query($query) ) 
      {
         $this->id = $conn->insert_id;
         
         $result = $this->insertarRoles();
      } 
      else 
      {
         error_log("Error BD ({$conn->errno}): {$conn->error}");
      }

      return $result;
   }

   private function insertarRoles()
   {
      foreach($this->roles as $rol) 
      {
         $rolesUsuario = new RolesUsuario($this->id, $rol->getId());

         if (! $rolesUsuario->insertar())
         {
               return false;
         }
      }

      return true;
   }
   //endregion

   //region Métodos estáticos

   public static function login($nombreUsuario, $password)
   {
      $usuario = self::buscaUsuario($nombreUsuario);
      
      if ($usuario && $usuario->compruebaPassword($password)) 
      {
            return $usuario;
      }

      return false;
   }

   public static function buscaUsuario($nombreUsuario)
   {
      $conn = Aplicacion::getInstance()->getConexionBd();
      
      $query = sprintf("SELECT * FROM Usuarios U WHERE U.nombreUsuario='%s'", $conn->real_escape_string($nombreUsuario));
      
      $rs = $conn->query($query);
      
      if ($rs) 
      {
            $fila = $rs->fetch_assoc();
            
            $rs->free();

            if ($fila)
            {
               $user = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['id']);

               return $user;
            }
      } 
      else 
      {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
      }
      
      return false;
   }

   public static function crear($nombreUsuario, $password, $nombre, $apellidos, $email, $rol)
   {
      $roles = [ new Rol($rol) ];

      $user = new Usuario($nombreUsuario, self::hashPassword($password), $nombre, $apellidos, $email, null, $roles);
      
      if($user->insertar())
      {
         return $user;
      }

      return false;
   }
   private static function hashPassword($password)
   {
      return password_hash($password, PASSWORD_DEFAULT);
   }

   //endregion
}

?>
