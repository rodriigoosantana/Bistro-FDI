<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDB.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDesglosado.php';

// Clase PedidoService (Lógica de negocio)
// Capa intermedia


class PedidoService
{
  public static function crear(Pedido $pedido): Pedido
  {
    return PedidoDB::insert($pedido);
    # devuelve el DTO del pedido con id asignado si se inserta correctamente, null si falla
  }


  public static function actualizar(Pedido $pedido): bool
  {
    return PedidoDB::update($pedido);
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

  public static function insertarProductoPedido(int $pedidoId, int $productoId, int $cantidad, float $precioUnitario): bool
  {
    return PedidoDB::insertarProductoPedido($pedidoId, $productoId, $cantidad, $precioUnitario);
  }

  public static function actualizarProductoPedido(int $pedidoId, int $productoId, int $cantidad): bool
  {
    return PedidoDB::actualizarProductoPedido($pedidoId, $productoId, $cantidad);
  }

  public static function eliminarProductoPedido(int $pedidoId, int $productoId): bool
  {
    return PedidoDB::eliminarProductoPedido($pedidoId, $productoId);
  }

  public static function buscarDesglosadoPorId(int $id): PedidoDesglosado
  {
    $pedido = PedidoDB::buscarPorId($id);
    if ($pedido === null) {
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
