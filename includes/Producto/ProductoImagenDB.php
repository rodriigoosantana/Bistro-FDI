<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Aplicacion;
//Capa de acceso a datos para la tabla ProductoImagen
class ProductoImagenDB
{
  public static function insertar(int $productoId, string $rutaImagen): int|bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "INSERT INTO ProductoImagen (producto_id, ruta_imagen) VALUES (%d, '%s')",
      $productoId,
      $conexion->real_escape_string($rutaImagen)
    );

    $conexion->query($query);

    return $conexion->insert_id;
  }

  public static function borrarPorProducto(int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("DELETE FROM ProductoImagen WHERE producto_id = %d", $productoId);

    $conexion->query($query);

    return true;
  }

  public static function listarPorProducto($productoId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM ProductoImagen WHERE producto_id = %d", $productoId);

    $resultado = $conexion->query($query);

    $imagenes = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->free();
    return $imagenes;
  }
}
