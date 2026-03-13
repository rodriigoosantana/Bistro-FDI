<?php

namespace es\ucm\fdi\aw\Usuario;

use es\ucm\fdi\aw\Aplicacion;

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

    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT Roles.id, Roles.nombre FROM Roles INNER JOIN RolesUsuario ON Roles.id = RolesUsuario.rol WHERE RolesUsuario.usuario=%d", $idUsuario);

    $rs = $conn->query($query);

    if ($rs) {
      $rsRol = $rs->fetch_assoc();
      if ($rsRol != null) {
        $rs->free();
        $rol = new Rol(intval($rsRol['id']), $rsRol['nombre']);
        return $rol;
      }
    } else {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }

    return null;
  }


  public static function cambiarRol($idUsuario, $idRol)
  {

    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("UPDATE RolesUsuario SET rol=%d WHERE usuario=%d", $idRol, $idUsuario);

    if (!$conn->query($query)) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }
  }
}
