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

    if ($conexion->query($query)) {
      return $conexion->insert_id; #devuelve el id de la imagen insertada
    } else {
      error_log("Error al insertar imagen de producto: " . $conexion->error);
      return false; #falla la inserción
    }
  }
  public static function borrarPorProducto(int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("DELETE FROM ProductoImagen WHERE producto_id = %d", $productoId);

    if ($conexion->query($query)) {
      return true; #se eliminaron las imágenes del producto
    } else {
      error_log("Error al eliminar imágenes de producto: " . $conexion->error);
      return false; #falla la eliminación
    }
  }

  public static function listarPorProducto($productoId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM ProductoImagen WHERE producto_id = %d", $productoId);

    $resultado = $conexion->query($query);

    if ($resultado) {
      $imagenes = $resultado->fetch_all(MYSQLI_ASSOC);
      $resultado->free();
      return $imagenes;
    } else {
      error_log("Error al listar imágenes de producto: " . $conexion->error);
      return []; #falla la consulta, devuelve array vacío
    }
  }
}
