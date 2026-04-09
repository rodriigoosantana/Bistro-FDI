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

    $query = $conexion->prepare(
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

    $query->bind_param("ssidiiii", $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
    $query->execute();
    $producto->setId($conexion->insert_id);
    $query->close();

    return $producto;
  }

  public static function actualizar(Producto $producto): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
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

    $query->bind_param("ssidiiiii", $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id);
    $query->execute();
    $query->close();

    return true;
  }

  public static function eliminar(int $id): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("DELETE FROM Productos WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->close();
    return true;
  }

  public static function buscarPorId(int $id): ?Producto
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("SELECT * FROM Productos WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $resultado = $query->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $query->close();
    return $fila ? self::filaAProducto($fila) : null;
  }

  public static function listarTodos(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("SELECT * FROM Productos ORDER BY categoria_id ASC, nombre ASC");
    $query->execute();
    $resultado = $query->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $query->close();
    return $productos;
  }

  public static function listarActivos(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("SELECT * FROM Productos WHERE activo=1 AND disponible=1 ORDER BY categoria_id ASC, nombre ASC");
    $query->execute();
    $resultado = $query->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $query->close();
    return $productos;
  }

  public static function listarPorCategoria(int $categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("SELECT * FROM Productos WHERE categoria_id=? ORDER BY nombre ASC");
    $query->bind_param("i", $categoriaId);
    $query->execute();
    $resultado = $query->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $query->close();
    return $productos;
  }

  public static function listarActivosPorCategoria(int $categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("SELECT * FROM Productos WHERE categoria_id=? AND activo=1 AND disponible=1 ORDER BY nombre ASC");
    $query->bind_param("i", $categoriaId);
    $query->execute();
    $resultado = $query->get_result();
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = self::filaAProducto($fila);
    }
    $resultado->free();
    $query->close();
    return $productos;
  }

  public static function cambiarDisponibilidad(int $id, bool $disponible): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $valor = $disponible ? 1 : 0;
    $query = $conexion->prepare("UPDATE Productos SET disponible=? WHERE id=?");
    $query->bind_param("ii", $valor, $id);
    $query->execute();
    $query->close();
    return true;
  }

  public static function cambiarEstado(int $id, bool $activo): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    if ($activo) {
      $query = $conexion->prepare("UPDATE Productos SET activo=1 WHERE id=?");
    } else {
      // Al desactivar, también fuerza disponible=0
      $query = $conexion->prepare("UPDATE Productos SET activo=0, disponible=0 WHERE id=?");
    }
    $query->bind_param("i", $id);
    $query->execute();
    $query->close();
    return true;
  }

  public static function desactivarPorCategoria(int $categoriaId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("UPDATE Productos SET activo=0 WHERE categoria_id=?");
    $query->bind_param("i", $categoriaId);
    $query->execute();
    $query->close();
    return true;
  }

  public static function actualizarOfertado(int $id, bool $ofertado): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();
    $query = $conexion->prepare("UPDATE Productos SET ofertado=? WHERE id=?");
    $val = $ofertado ? 1 : 0;
    $query->bind_param('ii', $val, $id);
    $query->execute();
    $query->close();
    return true;
  }
}
