<?php

namespace es\ucm\fdi\aw\Usuario;

use es\ucm\fdi\aw\Aplicacion;

class RolesUsuario
{
  //region Campos privados

  private $idUsuario;

  private $idRol;

  //endregion

  //region Constructor

  public function __construct($idUsuario, $idRol)
  {
    $this->idUsuario = $idUsuario;
    $this->idRol = $idRol;
  }

  //endregion

  //region Propiedades

  public function getIdUsuario()
  {
    return $this->idUsuario;
  }

  public function getIdRol()
  {
    return $this->idRol;
  }

  //endregion

  public function insertar()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "INSERT INTO RolesUsuario(usuario, rol) VALUES (%d, %d)",
      $this->idUsuario,
      $this->idRol
    );

    if (! $conn->query($query)) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");

      return false;
    }

    return true;
  }

  public function eliminar()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("DELETE FROM RolesUsuario WHERE usuario=%d", $this->idUsuario);

    if (! $conn->query($query)) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");

      return false;
    }

    return true;
  }
}
