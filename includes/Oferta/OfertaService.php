<?php

namespace es\ucm\fdi\aw\Oferta;

use es\ucm\fdi\aw\Oferta\Oferta;
use es\ucm\fdi\aw\Oferta\OfertaDB;
use es\ucm\fdi\aw\Oferta\OfertaProducto;
use es\ucm\fdi\aw\Producto\ProductoDB;
use DateTime;

// Clase OfertaService
// Capa de lógica de negocio para Ofertas.

class OfertaService
{
    /**
     * Crea una oferta con sus líneas de productos y actualiza el campo ofertado
     * de los productos involucrados.
     *
     * @param array $lineas  Array de ['producto_id' => int, 'cantidad' => int]
     */
    public static function crear(string $nombre, string $descripcion, DateTime $inicio, DateTime $fin, float $descuento, array $lineas): ?Oferta
    {
        $dto = new Oferta($nombre, $descripcion, $inicio, $fin, $descuento);
        $oferta = OfertaDB::insertar($dto);

        if (!$oferta) return null;

        foreach ($lineas as $linea) {
            $op = new OfertaProducto($oferta->getId(), intval($linea['producto_id']), intval($linea['cantidad']));
            OfertaDB::insertarLinea($op);
        }

        // Si la oferta está activa, marcar productos como ofertados
        if ($oferta->isVigente()) {
            self::actualizarOfertadoProductos($lineas, true);
        }

        return $oferta;
    }

    /**
     * Actualiza una oferta: reemplaza sus líneas y recalcula ofertado en productos.
     */
    public static function actualizar(int $id, string $nombre, string $descripcion, DateTime $inicio, DateTime $fin, float $descuento, array $nuevasLineas): bool
    {
        // Líneas anteriores para recalcular ofertado
        $lineasAnteriores = OfertaDB::listarLineasDeOferta($id);
        $idsAnteriores = array_map(fn($l) => $l->getProductoId(), $lineasAnteriores);

        OfertaDB::borrarLineasDeOferta($id);

        $oferta = new Oferta($nombre, $descripcion, $inicio, $fin, $descuento, $id);
        OfertaDB::actualizar($oferta);

        foreach ($nuevasLineas as $linea) {
            $op = new OfertaProducto($id, intval($linea['producto_id']), intval($linea['cantidad']));
            OfertaDB::insertarLinea($op);
        }

        // Recalcular ofertado: desmarcar los que ya no están en ninguna oferta activa
        self::recalcularOfertado($idsAnteriores);
        if ($oferta->isVigente()) {
            $idsNuevos = array_column($nuevasLineas, 'producto_id');
            self::actualizarOfertadoProductos(array_map(fn($id) => ['producto_id' => $id], $idsNuevos), true);
        }

        return true;
    }

    /**
     * Elimina una oferta y recalcula el campo ofertado de sus productos.
     */
    public static function eliminar(int $id): bool
    {
        $lineas = OfertaDB::listarLineasDeOferta($id);
        $ids = array_map(fn($l) => $l->getProductoId(), $lineas);

        # borrado lógico: pone activa=0
        OfertaDB::eliminar($id);

        self::recalcularOfertado($ids);

        return true;
    }

    # permite reactivar una oferta previamente desactivada
    public static function cambiarEstado(int $id, bool $activa): bool
    {
        $oferta = OfertaDB::buscarPorId($id);
        if (!$oferta) return false;

        $oferta->setActiva($activa);
        OfertaDB::actualizar($oferta);

        # recalcular ofertado de los productos afectados
        $lineas = OfertaDB::listarLineasDeOferta($id);
        $ids = array_map(fn($l) => $l->getProductoId(), $lineas);
        self::recalcularOfertado($ids);

        return true;
    }

    public static function buscarPorId(int $id): ?Oferta
    {
        return OfertaDB::buscarPorId($id);
    }

    public static function listarTodas(): array
    {
        return OfertaDB::listarTodas();
    }

    public static function listarActivas(): array
    {
        return OfertaDB::listarActivas();
    }

    public static function listarLineasDeOferta(int $ofertaId): array
    {
        return OfertaDB::listarLineasDeOferta($ofertaId);
    }

    public static function listarOfertasDePedido(int $pedidoId): array
    {
        return OfertaDB::listarOfertasDePedido($pedidoId);
    }

    /**
     * Comprueba si una oferta es aplicable a un pedido dado su carrito.
     * $carrito: array de ['producto_id' => int, 'cantidad' => int]
     */
    public static function esAplicable(int $ofertaId, array $carrito): bool
    {
        $lineas = OfertaDB::listarLineasDeOferta($ofertaId);
        $carritoIndexado = [];
        foreach ($carrito as $item) {
            $carritoIndexado[intval($item['producto_id'])] = intval($item['cantidad']);
        }

        foreach ($lineas as $linea) {
            $pid = $linea->getProductoId();
            if (!isset($carritoIndexado[$pid]) || $carritoIndexado[$pid] < $linea->getCantidad()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Calcula el descuento monetario que aplica una oferta dado el carrito.
     * Usa el precio con IVA de cada producto de la oferta.
     */
    public static function calcularDescuento(int $ofertaId, array $carrito): float
    {
        $oferta = OfertaDB::buscarPorId($ofertaId);
        if (!$oferta) return 0.0;

        $lineas = OfertaDB::listarLineasDeOferta($ofertaId);
        $carritoIndexado = [];
        foreach ($carrito as $item) {
            $carritoIndexado[intval($item['producto_id'])] = intval($item['cantidad']);
        }

        // Cuántas veces cabe la oferta completa en el carrito
        $vecesAplicable = PHP_INT_MAX;
        foreach ($lineas as $linea) {
            $pid = $linea->getProductoId();
            if (!isset($carritoIndexado[$pid])) return 0.0;
            $vecesAplicable = min($vecesAplicable, intdiv($carritoIndexado[$pid], $linea->getCantidad()));
        }
        if ($vecesAplicable === PHP_INT_MAX || $vecesAplicable === 0) return 0.0;

        // Precio base del pack (una vez)
        $precioUnPack = 0.0;
        foreach ($lineas as $linea) {
            $producto = ProductoDB::buscarPorId($linea->getProductoId());
            if ($producto) {
                $precioUnPack += $producto->getPrecioFinal() * $linea->getCantidad();
            }
        }

        return round($precioUnPack * $oferta->getDescuento() * $vecesAplicable, 2);
    }

    private static function actualizarOfertadoProductos(array $lineas, bool $valor): void
    {
        foreach ($lineas as $linea) {
            ProductoDB::actualizarOfertado(intval($linea['producto_id']), $valor);
        }
    }

    /**
     * Para una lista de producto_ids, recalcula si siguen en alguna oferta activa
     * y actualiza su campo ofertado en consecuencia.
     */
    private static function recalcularOfertado(array $productoIds): void
    {
        foreach ($productoIds as $pid) {
            $pid = intval($pid);
            $enOfertaVigente = OfertaDB::contarOfertasVigentesDeProducto($pid) > 0;
            ProductoDB::actualizarOfertado($pid, $enOfertaVigente);
        }
    }

    # registra todas las ofertas aplicadas a un pedido
    # $ofertasIds: array de ids
    public static function registrarOfertasEnPedido(int $pedidoId, array $ofertasIds): void
    {
        foreach ($ofertasIds as $id) {
            OfertaDB::insertarPedidoOferta($pedidoId, intval($id));
        }
    }

    # comprueba si una oferta se solapa en productos con alguna de las ya seleccionadas
    # $ofertasYaSeleccionadas: array de ids de ofertas ya activas en el pedido
    public static function seSolapaConOtras(int $ofertaId, array $ofertasYaSeleccionadas): bool
    {
        if (empty($ofertasYaSeleccionadas)) return false;

        # productos que requiere la nueva oferta
        $lineasNueva = OfertaDB::listarLineasDeOferta($ofertaId);
        $idsNueva = array_map(fn($l) => $l->getProductoId(), $lineasNueva);

        # comparar con cada oferta ya seleccionada
        foreach ($ofertasYaSeleccionadas as $idExistente) {
            if ($idExistente === $ofertaId) return true; # misma oferta
            $lineasExistente = OfertaDB::listarLineasDeOferta(intval($idExistente));
            $idsExistente = array_map(fn($l) => $l->getProductoId(), $lineasExistente);
            if (!empty(array_intersect($idsNueva, $idsExistente))) {
                return true; # comparten al menos un producto
            }
        }
        return false;
    }

    # calcula el descuento total sumando todas las ofertas seleccionadas
    # $ofertasIds: array de ids de ofertas activas
    public static function calcularDescuentoMultiple(array $ofertasIds, array $carrito): float
    {
        $total = 0.0;
        foreach ($ofertasIds as $id) {
            $total += self::calcularDescuento(intval($id), $carrito);
        }
        return round($total, 2);
    }
}
