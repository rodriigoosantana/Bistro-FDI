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

    $query = $conn->prepare("INSERT INTO RolesUsuario(usuario, rol) VALUES (?, ?)");
    $idUsuario = $this->idUsuario;
    $idRol = $this->idRol;
    $query->bind_param("ii", $idUsuario, $idRol);
    $query->execute();
    $query->close();
    return true;
  }

  public function eliminar()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();
    $query = $conn->prepare("DELETE FROM RolesUsuario WHERE usuario=?");
    $idUsuario = $this->idUsuario;
    $query->bind_param("i", $idUsuario);
    $query->execute();
    $query->close();
    return true;
  }
}
