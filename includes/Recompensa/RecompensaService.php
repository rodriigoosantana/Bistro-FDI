<?php

namespace es\ucm\fdi\aw\Recompensa;

use es\ucm\fdi\aw\Recompensa\RecompensaDB;
use es\ucm\fdi\aw\Recompensa\Recompensa;

require_once dirname(__DIR__, 2) . '/includes/config.php';

class RecompensaService
{
  public static function insertar($recompensa)
  {
    return RecompensaDB::insertar($recompensa);;
  }
  public static function actualizar($recompensa)
  {
    return RecompensaDB::actualizar($recompensa);
  }

  public static function eliminar($recompensa)
  {
    RecompensaDB::eliminar($recompensa);
  }

  public static function listarTodos()
  {
    return RecompensaDB::listarTodos();
  }

  public static function buscarPorId($id)
  {
    return RecompensaDB::buscarPorId($id);
  }
  public static function existePorProductoId($productoId)
  {
    return RecompensaDB::existePorProductoId($productoId);
  }

  public static function buscarPorProductoId($productoId)
  {
    return RecompensaDB::buscarPorProductoId($productoId);
  }
}
