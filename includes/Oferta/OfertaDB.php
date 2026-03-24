<?php

namespace es\ucm\fdi\aw\Oferta;

use es\ucm\fdi\aw\Aplicacion;
use DateTime;

// Clase OfertaDB
// Capa de acceso a datos para Oferta y OfertaProducto.

class OfertaDB
{
    public static function insertar(Oferta $oferta): ?Oferta
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare(
            "INSERT INTO Ofertas (nombre, descripcion, inicio, fin, descuento)
       VALUES (?, ?, ?, ?, ?)"
        );
        $nombre      = $oferta->getNombre();
        $descripcion = $oferta->getDescripcion();
        $inicio      = $oferta->getInicio()->format('Y-m-d');
        $fin         = $oferta->getFin()->format('Y-m-d');
        $descuento   = $oferta->getDescuento();

        $stmt->bind_param('ssssd', $nombre, $descripcion, $inicio, $fin, $descuento);
        $stmt->execute();
        $oferta->setId($conexion->insert_id);
        $stmt->close();

        return $oferta;
    }

    public static function actualizar(Oferta $oferta): bool
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare(
            "UPDATE Ofertas SET nombre=?, descripcion=?, inicio=?, fin=?, descuento=?
       WHERE id=?"
        );
        $nombre      = $oferta->getNombre();
        $descripcion = $oferta->getDescripcion();
        $inicio      = $oferta->getInicio()->format('Y-m-d');
        $fin         = $oferta->getFin()->format('Y-m-d');
        $descuento   = $oferta->getDescuento();
        $id          = $oferta->getId();

        $stmt->bind_param('ssssdi', $nombre, $descripcion, $inicio, $fin, $descuento, $id);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    public static function eliminar(int $id): bool
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare("DELETE FROM Ofertas WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    public static function buscarPorId(int $id): ?Oferta
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare("SELECT * FROM Ofertas WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        if (!$fila) return null;

        return new Oferta(
            $fila['nombre'],
            $fila['descripcion'],
            new DateTime($fila['inicio']),
            new DateTime($fila['fin']),
            floatval($fila['descuento']),
            intval($fila['id'])
        );
    }

    public static function listarTodas(): array
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();
        $resultado = $conexion->query("SELECT * FROM Ofertas ORDER BY inicio DESC");

        $ofertas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ofertas[] = new Oferta(
                $fila['nombre'],
                $fila['descripcion'],
                new DateTime($fila['inicio']),
                new DateTime($fila['fin']),
                floatval($fila['descuento']),
                intval($fila['id'])
            );
        }
        $resultado->free();
        return $ofertas;
    }

    public static function listarActivas(): array
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();
        $hoy = (new DateTime())->format('Y-m-d');

        $stmt = $conexion->prepare(
            "SELECT * FROM Ofertas WHERE inicio <= ? AND fin >= ? ORDER BY nombre ASC"
        );
        $stmt->bind_param('ss', $hoy, $hoy);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $ofertas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ofertas[] = new Oferta(
                $fila['nombre'],
                $fila['descripcion'],
                new DateTime($fila['inicio']),
                new DateTime($fila['fin']),
                floatval($fila['descuento']),
                intval($fila['id'])
            );
        }
        $stmt->close();
        return $ofertas;
    }

    public static function insertarLinea(OfertaProducto $linea): bool
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare(
            "INSERT INTO OfertaProducto (oferta_id, producto_id, cantidad) VALUES (?, ?, ?)"
        );
        $ofertaId   = $linea->getOfertaId();
        $productoId = $linea->getProductoId();
        $cantidad   = $linea->getCantidad();

        $stmt->bind_param('iii', $ofertaId, $productoId, $cantidad);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public static function borrarLineasDeOferta(int $ofertaId): bool
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare("DELETE FROM OfertaProducto WHERE oferta_id=?");
        $stmt->bind_param('i', $ofertaId);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public static function listarLineasDeOferta(int $ofertaId): array
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare("SELECT * FROM OfertaProducto WHERE oferta_id=?");
        $stmt->bind_param('i', $ofertaId);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $lineas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $lineas[] = new OfertaProducto(
                intval($fila['oferta_id']),
                intval($fila['producto_id']),
                intval($fila['cantidad']),
                intval($fila['id'])
            );
        }
        $stmt->close();
        return $lineas;
    }

    public static function insertarPedidoOferta(int $pedidoId, int $ofertaId): bool
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO PedidoOferta (pedido_id, oferta_id) VALUES (?, ?)"
        );
        $stmt->bind_param('ii', $pedidoId, $ofertaId);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public static function listarOfertasDePedido(int $pedidoId): array
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conexion->prepare(
            "SELECT o.* FROM Ofertas o
       INNER JOIN PedidoOferta po ON o.id = po.oferta_id
       WHERE po.pedido_id = ?"
        );
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $ofertas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ofertas[] = new Oferta(
                $fila['nombre'],
                $fila['descripcion'],
                new DateTime($fila['inicio']),
                new DateTime($fila['fin']),
                floatval($fila['descuento']),
                intval($fila['id'])
            );
        }
        $stmt->close();
        return $ofertas;
    }
}
