<?php

namespace es\ucm\fdi\aw\Oferta;

use es\ucm\fdi\aw\Oferta\Oferta;
use es\ucm\fdi\aw\Oferta\OfertaDB;
use es\ucm\fdi\aw\Oferta\OfertaProducto;
use es\ucm\fdi\aw\Producto\ProductoDB;
use DateTime;

class OfertaService
{
    /**
     * Crea una oferta con sus líneas de productos y actualiza el campo 'ofertado' de los productos involucrados.
     * @param array $lineas -> Array de ['producto_id' => int, 'cantidad' => int]
     */
    public static function crear(string $nombre, string $descripcion, DateTime $inicio, DateTime $fin, float $descuento, array $lineas): ?Oferta
    {
        $dto = new Oferta($nombre, $descripcion, $inicio, $fin, $descuento);
        $oferta = OfertaDB::insertar($dto);

        if (!$oferta) return null;

        foreach ($lineas as $linea) {
            $ofertaProducto = new OfertaProducto($oferta->getId(), intval($linea['producto_id']), intval($linea['cantidad']));
            OfertaDB::insertarLinea($ofertaProducto);
        }

        if ($oferta->isVigente()) {
            self::actualizarOfertadoProductos($lineas, true); // Si la oferta está activa, marcar productos como ofertados
        }

        return $oferta;
    }

    /**
     * Actualiza una oferta: reemplaza sus líneas y recalcula ofertado en productos.
     */
    public static function actualizar(int $id, string $nombre, string $descripcion, DateTime $inicio, DateTime $fin, float $descuento, array $nuevasLineas): bool
    {
        $lineasAnteriores = OfertaDB::listarLineasDeOferta($id);  // Líneas anteriores para recalcular ofertado
        $idsAnteriores = array_map(fn($l) => $l->getProductoId(), $lineasAnteriores); // IDs de productos antes de la actualización
        OfertaDB::borrarLineasDeOferta($id); # eliminar líneas anteriores

        $oferta = new Oferta($nombre, $descripcion, $inicio, $fin, $descuento, $id);
        OfertaDB::actualizar($oferta);

        foreach ($nuevasLineas as $linea) {
            $ofertaProducto = new OfertaProducto($id, intval($linea['producto_id']), intval($linea['cantidad']));
            OfertaDB::insertarLinea($ofertaProducto);
        }

        // Recalcular ofertado: desmarcar los que ya no están en ninguna oferta activa
        self::recalcularOfertado($idsAnteriores);
        if ($oferta->isVigente()) {
            $idsNuevos = array_column($nuevasLineas, 'producto_id');
            self::actualizarOfertadoProductos(array_map(fn($id) => ['producto_id' => $id], $idsNuevos), true);
        }

        return true;
    }

    // Elimina una oferta y recalcula el campo ofertado de sus productos.
    public static function eliminar(int $id): bool
    {
        $lineas = OfertaDB::listarLineasDeOferta($id);
        $ids = array_map(fn($l) => $l->getProductoId(), $lineas);

        # borrado lógico: pone activa=0
        OfertaDB::eliminar($id);

        self::recalcularOfertado($ids);

        return true;
    }

    // Permite reactivar una oferta previamente desactivada
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

    # Comprueba si una oferta tiene en el carrito al menos una unidad de cada producto requerido.
    # No mide cuántas veces cabe; para eso ver vecesAplicableEnCarrito.
    # $carrito: array de ['producto_id' => int, 'cantidad' => int]
    public static function esAplicable(int $ofertaId, array $carrito): bool
    {
        return self::vecesAplicableEnCarrito($ofertaId, $carrito) > 0;
    }

    # Cuántas veces completas cabe la oferta en el carrito (división entera mínima por línea).
    # Devuelve 0 si falta algún producto de la oferta.
    private static function vecesAplicableEnCarrito(int $ofertaId, array $carrito): int
    {
        $lineas = OfertaDB::listarLineasDeOferta($ofertaId);
        if (empty($lineas)) return 0;

        $idx = self::indexarCarrito($carrito);
        $veces = PHP_INT_MAX;
        foreach ($lineas as $linea) {
            $pid = $linea->getProductoId();
            $cantLinea = $linea->getCantidad();
            if (!isset($idx[$pid]) || $cantLinea < 1) return 0;
            $veces = min($veces, intdiv($idx[$pid], $cantLinea));
            if ($veces === 0) return 0;
        }
        return $veces === PHP_INT_MAX ? 0 : $veces;
    }

    # Descuento monetario de UNA oferta sobre el carrito completo.
    # Útil para mostrar el descuento individual en la vista; para el total del pedido
    # con varias ofertas usar calcularDescuentoMultiple, que reparte unidades correctamente.
    public static function calcularDescuento(int $ofertaId, array $carrito): float
    {
        $veces = self::vecesAplicableEnCarrito($ofertaId, $carrito);
        if ($veces === 0) return 0.0;
        return self::descuentoConVeces($ofertaId, $veces);
    }

    # Descuento total aplicando ofertas SECUENCIALMENTE sobre el carrito,
    # consumiendo unidades a medida que cada oferta entra. Esto permite que
    # dos ofertas que mencionen el mismo producto convivan si el carrito
    # tiene unidades suficientes (ej.: 2 bocadillos cubren oferta1=bocadillo+café
    # y oferta2=bocadillo+refresco).
    # Las ofertas se ordenan por descuento absoluto descendente para favorecer al cliente.
    public static function calcularDescuentoMultiple(array $ofertasIds, array $carrito): float
    {
        if (empty($ofertasIds)) return 0.0;

        # ordenar por descuento descendente (la que más ahorra entra primero)
        $conDescuento = [];
        foreach ($ofertasIds as $id) {
            $id = intval($id);
            $conDescuento[] = ['id' => $id, 'd' => self::calcularDescuento($id, $carrito)];
        }
        usort($conDescuento, fn($a, $b) => $b['d'] <=> $a['d']);

        $restante = self::indexarCarrito($carrito);
        $total = 0.0;

        foreach ($conDescuento as $item) {
            $id = $item['id'];
            $veces = self::vecesAplicableEnCarrito($id, self::desindexarCarrito($restante));
            if ($veces === 0) continue;
            $total += self::descuentoConVeces($id, $veces);
            # consumir las unidades que esta oferta reserva
            foreach (OfertaDB::listarLineasDeOferta($id) as $l) {
                $restante[$l->getProductoId()] -= $l->getCantidad() * $veces;
            }
        }
        return round($total, 2);
    }

    # ¿Puede esta oferta candidata convivir con las ya seleccionadas dado el carrito?
    # Reserva primero las unidades que consumen las ya seleccionadas y comprueba
    # si la candidata sigue siendo aplicable sobre el carrito sobrante.
    # Sustituye al antiguo seSolapaConOtras (que bloqueaba por nombre, no por cantidad).
    public static function puedeAplicarseJunto(int $ofertaId, array $ofertasYaSeleccionadas, array $carrito): bool
    {
        if (in_array($ofertaId, array_map('intval', $ofertasYaSeleccionadas), true)) {
            return false; # ya está activa
        }

        $restante = self::indexarCarrito($carrito);
        # reservar unidades de las ofertas ya activas
        foreach ($ofertasYaSeleccionadas as $idExistente) {
            $idExistente = intval($idExistente);
            $veces = self::vecesAplicableEnCarrito($idExistente, self::desindexarCarrito($restante));
            if ($veces === 0) continue;
            foreach (OfertaDB::listarLineasDeOferta($idExistente) as $l) {
                $restante[$l->getProductoId()] -= $l->getCantidad() * $veces;
            }
        }
        # ¿queda hueco para la candidata?
        return self::vecesAplicableEnCarrito($ofertaId, self::desindexarCarrito($restante)) > 0;
    }

    # convierte [['producto_id' => x, 'cantidad' => y], ...] en [x => y, ...]
    private static function indexarCarrito(array $carrito): array
    {
        $idx = [];
        foreach ($carrito as $item) {
            $pid = intval($item['producto_id']);
            $cant = intval($item['cantidad']);
            $idx[$pid] = ($idx[$pid] ?? 0) + $cant;
        }
        return $idx;
    }

    # convierte [x => y, ...] en [['producto_id' => x, 'cantidad' => y], ...]
    private static function desindexarCarrito(array $idx): array
    {
        $out = [];
        foreach ($idx as $pid => $cant) {
            if ($cant > 0) $out[] = ['producto_id' => $pid, 'cantidad' => $cant];
        }
        return $out;
    }

    # calcula el descuento monetario para una oferta aplicada N veces
    private static function descuentoConVeces(int $ofertaId, int $veces): float
    {
        if ($veces <= 0) return 0.0;
        $oferta = OfertaDB::buscarPorId($ofertaId);
        if (!$oferta) return 0.0;

        $precioUnPack = 0.0;
        foreach (OfertaDB::listarLineasDeOferta($ofertaId) as $linea) {
            $producto = ProductoDB::buscarPorId($linea->getProductoId());
            if ($producto) {
                $precioUnPack += $producto->getPrecioFinal() * $linea->getCantidad();
            }
        }
        return round($precioUnPack * $oferta->getDescuento() * $veces, 2);
    }

    # Igual que calcularDescuentoMultiple pero devuelve desglose por oferta:
    # ['total' => float, 'porOferta' => [ofertaId => ['veces' => int, 'descuento' => float]]]
    # Si una oferta no entró en el reparto, aparece con veces=0 y descuento=0.
    public static function calcularDescuentoMultipleDesglosado(array $ofertasIds, array $carrito): array
    {
        $resultado = ['total' => 0.0, 'porOferta' => []];
        foreach ($ofertasIds as $id) {
            $resultado['porOferta'][intval($id)] = ['veces' => 0, 'descuento' => 0.0];
        }
        if (empty($ofertasIds)) return $resultado;

        # ordenar por descuento individual descendente (la que más ahorra entra primero)
        $conDescuento = [];
        foreach ($ofertasIds as $id) {
            $id = intval($id);
            $conDescuento[] = ['id' => $id, 'd' => self::calcularDescuento($id, $carrito)];
        }
        usort($conDescuento, fn($a, $b) => $b['d'] <=> $a['d']);

        $restante = self::indexarCarrito($carrito);
        foreach ($conDescuento as $item) {
            $id = $item['id'];
            $veces = self::vecesAplicableEnCarrito($id, self::desindexarCarrito($restante));
            if ($veces === 0) continue;
            $dto = self::descuentoConVeces($id, $veces);
            $resultado['porOferta'][$id] = ['veces' => $veces, 'descuento' => round($dto, 2)];
            $resultado['total'] += $dto;
            foreach (OfertaDB::listarLineasDeOferta($id) as $l) {
                $restante[$l->getProductoId()] -= $l->getCantidad() * $veces;
            }
        }
        $resultado['total'] = round($resultado['total'], 2);
        return $resultado;
    }
}
