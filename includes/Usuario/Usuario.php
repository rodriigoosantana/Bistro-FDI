<?php

class Usuario
{
   //region Campos privados

   private $id;

   private $nombreUsuario;

   private $password;

   private $nombre;

   private $roles;

   //endregion
  
   //region Campos estaticos 

   public const ADMIN_ROLE = 1;

   public const USER_ROLE = 2;

   //endregion

   //region Constructor

   private function __construct($nombreUsuario, $password, $nombre, $id = null, $roles = [])
   {
      $this->id = $id;
      $this->nombreUsuario = $nombreUsuario;
      $this->password = $password;
      $this->nombre = $nombre;
      $this->roles = $roles;
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

   require_once RAIZ_APP . '/includes/Usuario/Rol.php';

   private function cargarRoles()
   {
      $this->roles = Rol::cargarRoles($this->id);
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

   private static function buscaUsuario($nombreUsuario)
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
               $user = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['id']);

               return $user;
            }
      } 
      else 
      {
            error_log("Error BD ({$conn->errno}): {$conn->error}");
      }
      
      return false;
   }

   //endregion
}

?>
