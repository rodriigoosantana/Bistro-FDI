<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDB.php';

// Clase PedidoService (Lógica de negocio)
// Capa intermedia


class PedidoService
{
  public static function crear(Pedido $pedido): Pedido
  {
    return PedidoDB::insertar($pedido);
    # devuelve el DTO del pedido con id asignado si se inserta correctamente, null si falla
  }


  public static function actualizar(Pedido $pedido): bool
  {
    return PedidoDB::actualizar($pedido);
  }

  public static function buscarPorId(int $id): Pedido
  {
    return PedidoDB::buscarPorId($id);
  }

  public static function listarTodos(): array
  {
    return PedidoDB::listarTodos();
  }

  // TODO
  public static function listarPorCategoria($categoriaId)
  {
    return PedidoDB::listarPorCategoria($categoriaId);
    # devuelve array de pedidos que pertenecen a la categoría indicada, o array vacío 
  }


  public static function cambiarDisponibilidad($id, $disponible)
  {
    # id del pedido, nuevo valor de disponibilidad
    return PedidoDB::cambiarDisponibilidad($id, $disponible);
    # devuelve true si se actualiza correctamente, false si falla
  }

  public static function cambiarEstado($id, $activo)
  {
    # id del pedido, nuevo estado activo/inactivo
    return PedidoDB::cambiarEstado($id, $activo);
    # devuelve true si se actualiza correctamente, false si falla
  }

  public static function getPedidoDesglosado(Pedido $pedido): PedidoDesglosado
  {
    if (PedidoDB::buscarPorId($pedido->getId()) === null) {
      throw new Exception("Pedido con id {$pedido->getId()} no encontrado");
    }

    $pd = new PedidoDesglosado($pedido, []);
    PedidoDB::getPedidoDesglosado($pd);
    return $pd;
  }

  public static function listarPorEstados(array $estados = null, int $clienteId = null): array
  {
    return PedidoDB::listarPorEstados($estados, $clienteId);
    # devuelve array de pedidos que coinciden con los estados indicados (si se pasan), o todos los pedidos si estados es null
  }
}
