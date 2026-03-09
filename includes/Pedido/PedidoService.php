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
  public static function listarPorCategoria($idCategoria)
  {
    return PedidoDB::listarPorCategoria($idCategoria);
    # devuelve array de pedidos que pertenecen a la categoría indicada, o array vacío 
  }


  public static function cambiarDisponibilidad($idPedido, $disponible)
  {
    # id del pedido, nuevo valor de disponibilidad
    return PedidoDB::cambiarDisponibilidad($idPedido, $disponible);
    # devuelve true si se actualiza correctamente, false si falla
  }

  public static function cambiarEstado($idPedido, $activo)
  {
    # id del pedido, nuevo estado activo/inactivo
    return PedidoDB::cambiarEstado($idPedido, $activo);
    # devuelve true si se actualiza correctamente, false si falla
  }

  public static function insertarProductoPedido(int $idPedido, int $idProducto, int $cantidad, float $precioUnitario): bool
  {
    return PedidoDB::insertarProductoPedido($idPedido, $idProducto, $cantidad, $precioUnitario);
  }

  public static function actualizarProductoPedido(int $idPedido, int $idProducto, int $cantidad): bool
  {
    return PedidoDB::actualizarProductoPedido($idPedido, $idProducto, $cantidad);
  }

  public static function eliminarProductoPedido(int $idPedido, int $idProducto): bool
  {
    return PedidoDB::eliminarProductoPedido($idPedido, $idProducto);
  }

  public static function buscarDesglosadoPorId(int $idPedido): PedidoDesglosado
  {
    $pedido = PedidoDB::buscarPorId($idPedido);
    if ($pedido === null) {
      throw new Exception("Pedido con id {$idPedido} no encontrado");
    }

    $pedidoDesglosado = new PedidoDesglosado($pedido, []);
    PedidoDB::getPedidoDesglosado($pedidoDesglosado);
    return $pedidoDesglosado;
  }

  public static function listarPorEstados(array $estados = null, int $clienteId = null): array
  {
    return PedidoDB::listarPorEstados($estados, $clienteId);
    # devuelve array de pedidos que coinciden con los estados indicados (si se pasan), o todos los pedidos si estados es null
  }
}
