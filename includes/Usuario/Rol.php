<?php

class Rol
{
   //region Campos privados

   private $id;

   private $nombre;

   //endregion

   //region Constructor

   public function __construct($id, $nombre = null)
   {
      $this->id = $id;
      $this->nombre = $nombre;
   }

   //endregion

   //region Propiedades
   public function __toString() 
   {
     return $this->getNombre();
   }
   public function getId()
   {
      return $this->id;
   }

   public function getNombre()
   {
      return $this->nombre;
   }

   //endregion

   public static function cargarRol($idUsuario)
   {
      if ($idUsuario > 0)
      {
            
            $conn = Aplicacion::getInstance()->getConexionBd();
            
            $query = sprintf("SELECT Roles.id, Roles.nombre FROM Roles INNER JOIN RolesUsuario ON Roles.id = RolesUsuario.rol WHERE RolesUsuario.usuario=%d", $idUsuario);

            $rs = $conn->query($query);
            
            if ($rs) 
            {
               $rsRol = $rs->fetch_assoc();
               
               $rs->free();

               $rol = new Rol(intval($rsRol['id']), $rsRol['nombre']);

               return $rol;
            } 
            else 
            {
               error_log("Error BD ({$conn->errno}): {$conn->error}");
            }
      }

      return false;
   }
}

?>
