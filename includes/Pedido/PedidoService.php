<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDB.php';

// Clase PedidoService (Lógica de negocio)
// Capa intermedia


class PedidoService
{
    public static function crear(Pedido $pedido)
    {
        return PedidoDB::insertar($pedido);
        # devuelve el DTO del pedido con id asignado si se inserta correctamente, null si falla
    }


    public static function actualizar(Pedido $pedido)
    {
        return PedidoDB::actualizar($pedido);
        # devuelve true si se actualiza correctamente, false si falla
    }

    public static function buscarPorId($id)
    {
        return PedidoDB::buscarPorId($id);
        # devuelve el DTO del pedido con el id indicado, o null si no existe
    }

    public static function listarTodos()
    {
        return PedidoDB::listarTodos();
        # devuelve array de pedidos, o array vacío si no hay pedidos
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
}
?>

