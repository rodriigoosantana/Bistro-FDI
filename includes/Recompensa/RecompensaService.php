<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

class RecompensaService
{
  public static function insertar($recompensa)
  {
    return RecompensaBD::insertar($recompensa);;
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
}
