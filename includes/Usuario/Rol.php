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

    $query = $conn->prepare("SELECT Roles.id, Roles.nombre FROM Roles INNER JOIN RolesUsuario ON Roles.id = RolesUsuario.rol WHERE RolesUsuario.usuario=?");
    $query->bind_param("i", $idUsuario);
    $query->execute();
    $rs = $query->get_result();

    if ($rs) {
      $rsRol = $rs->fetch_assoc();
      if ($rsRol != null) {
        $rs->free();
        $query->close();
        $rol = new Rol(intval($rsRol['id']), $rsRol['nombre']);
        return $rol;
      }
      $rs->free();
    } else {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }

    $query->close();

    return null;
  }


  public static function cambiarRol($idUsuario, $idRol)
  {

    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("UPDATE RolesUsuario SET rol=? WHERE usuario=?");
    $query->bind_param("ii", $idRol, $idUsuario);

    if (!$query->execute()) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }
    $query->close();
  }
}
