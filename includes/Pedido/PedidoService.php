<?php

namespace es\ucm\fdi\aw\Pedido;

use es\ucm\fdi\aw\Pedido\Pedido;
use es\ucm\fdi\aw\Pedido\PedidoDB;
use es\ucm\fdi\aw\Pedido\PedidoDesglosado;
use \Exception;
use \DateTime;

// Clase PedidoService (Lógica de negocio)
// Capa intermedia

class PedidoService
{
    public static function crear(Pedido $pedido): Pedido
    {
        return PedidoDB::insert($pedido);
    }

    public static function eliminar(int $id): bool
    {
        return PedidoDB::delete($id);
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

    public static function asignarCocinero(int $pedidoId, int $cocineroId): bool
    {
        return PedidoDB::asignarCocinero($pedidoId, $cocineroId);
    }

    public static function cambiarEstado($id, $estado)
    {
        return PedidoDB::cambiarEstado($id, $estado);
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

    public static function listarPorEstados(array $estados = null, int $clienteId = null, int $cocineroId = null): array
    {
        return PedidoDB::listarPorEstados($estados, $clienteId, $cocineroId);
    }

    public static function togglePreparadoProducto(int $productoId, int $pedidoId, bool $nuevoEstado): bool
    {
        return PedidoDB::togglePreparadoStatus($productoId, $pedidoId, $nuevoEstado);
    }

    public static function productoEnPedidodNecesitaPreparacion(int $pedidoId, int $productoId): bool
    {
        return PedidoDB::productoEnPedidoNecesitaPreparacion($pedidoId, $productoId);
    }

    public static function obtenerUltimoPedidoDelDia(DateTime $fecha): ?Pedido
    {
        return PedidoDB::obtenerUltimoPedidoDelDia($fecha);
    }
}
