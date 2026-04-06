<?php

namespace es\ucm\fdi\aw\Recompensa;

use es\ucm\fdi\aw\Aplicacion;

class RecompensaDB
{
  public static function insertar($recompensa)
  {

    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "INSERT INTO Recompensas(producto_id, bistrocoins_necesarias)
         VALUES ('%s', '%s')",
      $conn->real_escape_string($recompensa->getProductoId()),
      $conn->real_escape_string($recompensa->getBistroCoinsNecesarias())
    );

    $conn->query($query);
    return $recompensa;
  }

  public static function eliminar($recompensa)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "DELETE FROM Recompensas WHERE id = '%s'",
      $recompensa->getId()
    );

    $conn->query($query);
  }

  public static function actualizar($recompensa)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Recompensas
      SET producto_id = '%s',
      bistrocoins_necesarias = '%s'
      WHERE id = %d",
      $recompensa->getProductoId(),
      $recompensa->getBistroCoinsNecesarias(),
      $recompensa->getId()
    );

    $conn->query($query);

    return $recompensa;
  }

  public static function listarTodos()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Recompensas");

    $rs = $conn->query($query);

    $recompensas = [];
    while ($fila = $rs->fetch_assoc()) {
      $recompensas[] = new Recompensa($fila['producto_id'], $fila['bistrocoins_necesarias'], $fila['id']);
    }
    $rs->free();

    return $recompensas;
  }

  public static function buscarPorId($id)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Recompensas U WHERE U.id='%s'", $conn->real_escape_string($id));

    $rs = $conn->query($query);

    $fila = $rs->fetch_assoc();
    $rs->free();
    if ($fila) {
      $recompensa = new Recompensa($fila['producto_id'], $fila['bistrocoins_necesarias'], $fila['id']);
      return $recompensa;
    }

    return null;
  }
}
