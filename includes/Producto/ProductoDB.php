<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Producto;
use es\ucm\fdi\aw\Aplicacion;

//Clase ProductoDB
//Capa de acceso a datos para Producto.
//Contiene todas las operaciones SQL (INSERT, UPDATE, SELECT).
//Recibe y devuelve objetos Producto (DTO).

class ProductoDB
{
  //Inserta un nuevo producto en la base de datos.    
  public static function insertar(Producto $producto): ?Producto
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "INSERT INTO Productos (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado, activo)
            VALUES ('%s', '%s', %d, %f, %f, %d, %d, %d)",

      $conexion->real_escape_string($producto->getNombre()),
      $conexion->real_escape_string($producto->getDescripcion()),
      intval($producto->getCategoriaId()),
      floatval($producto->getPrecioBase()),
      floatval($producto->getIva()),
      $producto->isDisponible() ? 1 : 0,
      $producto->isOfertado() ? 1 : 0,
      $producto->isActivo() ? 1 : 0
    );

    $conexion->query($query);
    $producto->setId($conexion->insert_id); #Asignar el id al producto

    return $producto;
  }

  //Actualiza un producto existente en la base de datos.
  public static function actualizar(Producto $producto): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Productos
             SET nombre='%s', descripcion='%s', categoria_id=%d,
                 precio_base=%.2f, iva=%d, disponible=%d, ofertado=%d, activo=%d
             WHERE id=%d",

      $conexion->real_escape_string($producto->getNombre()),
      $conexion->real_escape_string($producto->getDescripcion()),
      intval($producto->getCategoriaId()),
      floatval($producto->getPrecioBase()),
      intval($producto->getIva()),
      $producto->isDisponible() ? 1 : 0,
      $producto->isOfertado() ? 1 : 0,
      $producto->isActivo() ? 1 : 0,
      intval($producto->getId())
    );

    $conexion->query($query);

    return true;
  }

  // Elimina un producto por su id. Devuelve true si se elimina, false si falla.
  public static function eliminar(int $id): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("DELETE FROM Productos WHERE id=%d", $id);

    $conexion->query($query);

    return true;
  }

  //Busca un producto por su id.
  public static function buscarPorId($id): ?Producto
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Productos WHERE id=%d",
      intval($id)
    );

    $resultado = $conexion->query($query);

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    if ($fila) {
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

    return null;
  }


  //Lista todos los productos ordenados por nombre.
  public static function listarTodos(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query =  "SELECT * FROM Productos ORDER BY categoria_id ASC, nombre ASC";

    $resultado = $conexion->query($query);

    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = new Producto(
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
    $resultado->free();

    return $productos; #En este caso si hay error devuelve array vacío en vez de false
  }

  public static function listarActivos(): array
  {
    // igual que listarTodos() pero con WHERE activo = 1 AND disponible = 1
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Productos WHERE activo = 1 AND disponible = 1 ORDER BY categoria_id ASC, nombre ASC";

    $resultado = $conexion->query($query);

    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = new Producto(
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
    $resultado->free();

    return $productos;
  }


  //Lista productos filtrados por categoría.
  public static function listarPorCategoria($categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Productos WHERE categoria_id=%d ORDER BY nombre ASC",
      intval($categoriaId)
    );

    $resultado = $conexion->query($query);

    $productos = [];

    if ($resultado) {
      while ($fila = $resultado->fetch_assoc()) {
        $productos[] = new Producto(
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
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    return $productos;
  }

  // Lista productos activos de una categoría concreta.
  public static function listarActivosPorCategoria(int $categoriaId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Productos WHERE categoria_id=%d AND activo=1 AND disponible=1 ORDER BY nombre ASC",
      intval($categoriaId)
    );

    $resultado = $conexion->query($query);
    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = new Producto(
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
    $resultado->free();

    return $productos;
  }

  //Cambia la disponibilidad de un producto.
  public static function cambiarDisponibilidad($id, $disponible): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Productos SET disponible=%d WHERE id=%d",
      $disponible ? 1 : 0,
      intval($id)
    );

    $conexion->query($query);

    return true;
  }


  //Cambia el estado activo/inactivo de un producto.
  public static function cambiarEstado($id, $activo): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

     if ($activo) {
        // Al activar, solo cambiamos activo (disponible lo gestiona el gerente a mano)
        $query = sprintf(
            "UPDATE Productos SET activo=1 WHERE id=%d",
            intval($id)
        );
    } else {
        // Al desactivar, también quitamos disponible automáticamente
        $query = sprintf(
            "UPDATE Productos SET activo=0, disponible=0 WHERE id=%d",
            intval($id)
        );
    }
    
    $conexion->query($query);

    return true;
  }

  // Desactiva todos los productos de una categoría
  public static function desactivarPorCategoria(int $categoriaId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Productos SET activo = 0 WHERE categoria_id = %d",
      $categoriaId
    );

    $conexion->query($query);

    return true;
  }
}
