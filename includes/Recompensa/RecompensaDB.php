<?php

namespace es\ucm\fdi\aw\Recompensa;

use es\ucm\fdi\aw\Aplicacion;

class RecompensaDB
{
  public static function insertar($recompensa)
  {

    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "INSERT INTO Recompensas(producto_id, bistrocoins_necesarias)
         VALUES (?, ?)"
    );

    $productoId = $recompensa->getProductoId();
    $bistroCoinsNecesarias = $recompensa->getBistroCoinsNecesarias();
    $query->bind_param("ii", $productoId, $bistroCoinsNecesarias);

    $query->execute();
    $query->close();
    return $recompensa;
  }

  public static function eliminar($recompensa)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("DELETE FROM Recompensas WHERE id = ?");
    $id = $recompensa->getId();
    $query->bind_param("i", $id);

    $query->execute();
    $query->close();
  }

  public static function actualizar($recompensa)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "UPDATE Recompensas
      SET producto_id = ?,
      bistrocoins_necesarias = ?
      WHERE id = ?"
    );

    $productoId = $recompensa->getProductoId();
    $bistroCoinsNecesarias = $recompensa->getBistroCoinsNecesarias();
    $id = $recompensa->getId();
    $query->bind_param("iii", $productoId, $bistroCoinsNecesarias, $id);

    $query->execute();
    $query->close();

    return $recompensa;
  }

  public static function listarTodos()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("SELECT * FROM Recompensas");
    $query->execute();
    $rs = $query->get_result();

    $recompensas = [];
    while ($fila = $rs->fetch_assoc()) {
      $recompensas[] = new Recompensa($fila['producto_id'], $fila['bistrocoins_necesarias'], $fila['id']);
    }
    $rs->free();
    $query->close();

    return $recompensas;
  }

  public static function buscarPorId($id)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("SELECT * FROM Recompensas U WHERE U.id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $rs = $query->get_result();

    $fila = $rs->fetch_assoc();
    $rs->free();
    $query->close();
    if ($fila) {
      $recompensa = new Recompensa($fila['producto_id'], $fila['bistrocoins_necesarias'], $fila['id']);
      return $recompensa;
    }

    return null;
  }

  public static function buscarPorProductoId($productoId)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Recompensas U WHERE U.producto_id='%s' LIMIT 1",
      $conn->real_escape_string($productoId)
    );

    $rs = $conn->query($query);

    $fila = $rs ? $rs->fetch_assoc() : null;
    if ($rs) {
      $rs->free();
    }

    if ($fila) {
      return new Recompensa($fila['producto_id'], $fila['bistrocoins_necesarias'], $fila['id']);
    }

    return null;
  }

  public static function existePorProductoId($productoId)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "SELECT 1 FROM Recompensas U WHERE U.producto_id=? LIMIT 1"
    );
    $query->bind_param("i", $productoId);
    $query->execute();
    $rs = $query->get_result();
    $existe = $rs && $rs->num_rows > 0;
    if ($rs) {
      $rs->free();
    }
    $query->close();

    return $existe;
  }
}
