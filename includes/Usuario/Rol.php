<?php

class Rol
{
   //region Campos privados

   private $id;

   private $nombre;

   //endregion

   //region Constructor

   private function __construct($id, $nombre = null)
   {
      $this->id = $id;
      $this->nombre = $nombre;
   }

   //endregion

   //region Propiedades

   public function getId()
   {
      return $this->id;
   }

   public function getNombre()
   {
      return $this->nombre;
   }

   //endregion

   public static function cargarRoles($idUsuario)
   {
      if ($idUsuario > 0)
      {
            $roles = [];
            
            $conn = Aplicacion::getInstance()->getConexionBd();
            
            $query = sprintf("SELECT Roles.id, Roles.nombre FROM Roles INNER JOIN RolesUsuario ON Roles.id = RolesUsuario.rol WHERE RolesUsuario.usuario=%d", $idUsuario);

            $rs = $conn->query($query);
            
            if ($rs) 
            {
               $rsRoles = $rs->fetch_all(MYSQLI_ASSOC);
               
               $rs->free();

               foreach($rsRoles as $rol) 
               {
                  $roles[] = new Rol(intval($rol['id']), $rol['nombre']);
               }

               return $roles;
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
