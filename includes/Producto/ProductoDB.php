<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Producto;
use es\ucm\fdi\aw\Aplicacion;

//Clase ProductoDB
//Capa de acceso a datos para Producto.
//Utiliza sentencias preparadas para prevenir inyección SQL.

class ProductoDB
{
  // Construye un objeto Producto a partir de una fila de BD
  private static function filaAProducto(array $fila): Producto
  {
    return new Producto(
      $fila['nombre'],
      $fila['descripcion'],
      intval($fila['categoria_id']),
      floatval($fila['precio_base']),
      intval($fila['iva']),
      (bool) $fila['disponible'],
      (bool) $fila['ofertado'],
      (bool) $fila['activo'],
      intval($fila['id'])
    );
  }

  public static function insertar(Producto $producto): ?Producto
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $stmt = $conexion->prepare(
      "INSERT INTO Productos (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado, activo)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $nombre      = $producto->getNombre();
    $descripcion = $producto->getDescripcion();
    $categoriaId = $producto->getCategoriaId();
    $precioBase  = $producto->getPrecioBase();
    $iva         = $producto->getIva();
    $disponible  = $producto->isDisponible() ? 1 : 0;
    $ofertado    = $producto->isOfertado()   ? 1 : 0;
    $activo      = $producto->isActivo()     ? 1 : 0;

    $stmt->bind_param("ssidiiii", $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
    $stmt->execute();
    $producto->setId($conexion->insert_id);
    $stmt->close();

    return $producto;
  }

  public static function actualizar(Producto $producto): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $stmt = $conexion->prepare(
      "UPDATE Productos
       SET nombre=?, descripcion=?, categoria_id=?, precio_base=?, iva=?, disponible=?, ofertado=?, activo=?
       WHERE id=?"
    );

    $nombre      = $producto->getNombre();
    $descripcion = $producto->getDescripcion();
    $categoriaId = $producto->getCategoriaId();
    $precioBase  = $producto->getPrecioBase();
    $iva         = $producto->getIva();
    $disponible  = $producto->isDisponible() ? 1 : 0;
    $ofertado    = $producto->isOfertado()   ? 1 : 0;
    $activo      = $producto->isActivo()     ? 1 : 0;
    $id          = $producto->getId();

    $stmt->bind_param("ssidiiiii", $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id);
    $stmt->execute();
    $stmt->close();

    return true;
  }

  public static function eliminar(int $id): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("DELETE FROM Productos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    return true;
  }

  public static function buscarPorId(int $id): ?Producto
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("SELECT * FROM Productos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();
    return $fila ? self::filaAProducto($fila) : null;
  }

  public static function listarTodos(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("SELECT * FROM Productos ORDER BY categoria_id ASC, nombre ASC");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $stmt->close();
    return $productos;
  }

  public static function listarActivos(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("SELECT * FROM Productos WHERE activo=1 AND disponible=1 ORDER BY categoria_id ASC, nombre ASC");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $stmt->close();
    return $productos;
  }

  public static function listarPorCategoria(int $categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("SELECT * FROM Productos WHERE categoria_id=? ORDER BY nombre ASC");
    $stmt->bind_param("i", $categoriaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $stmt->close();
    return $productos;
  }

  public static function listarActivosPorCategoria(int $categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("SELECT * FROM Productos WHERE categoria_id=? AND activo=1 AND disponible=1 ORDER BY nombre ASC");
    $stmt->bind_param("i", $categoriaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $stmt->close();
    return $productos;
  }

  public static function cambiarDisponibilidad(int $id, bool $disponible): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $valor = $disponible ? 1 : 0;
    $stmt = $conexion->prepare("UPDATE Productos SET disponible=? WHERE id=?");
    $stmt->bind_param("ii", $valor, $id);
    $stmt->execute();
    $stmt->close();
    return true;
  }

  public static function cambiarEstado(int $id, bool $activo): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    if ($activo) {
      $stmt = $conexion->prepare("UPDATE Productos SET activo=1 WHERE id=?");
    } else {
      // Al desactivar, también fuerza disponible=0
      $stmt = $conexion->prepare("UPDATE Productos SET activo=0, disponible=0 WHERE id=?");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    return true;
  }

  public static function desactivarPorCategoria(int $categoriaId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $stmt = $conexion->prepare("UPDATE Productos SET activo=0 WHERE categoria_id=?");
    $stmt->bind_param("i", $categoriaId);
    $stmt->execute();
    $stmt->close();
    return true;
  }
}
