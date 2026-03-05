<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDB.php';

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

    public static function cambiarEstado(int $id, Estado $estado)
    {
        # id del pedido, nuevo estado activo/inactivo
        return PedidoDB::cambiarEstado($id, $estado);
        # devuelve true si se actualiza correctamente, false si falla
    }
}
?>

