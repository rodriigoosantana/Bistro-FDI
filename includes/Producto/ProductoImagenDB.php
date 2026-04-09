<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Aplicacion;

class ProductoImagenDB
{
  public static function insertar(int $productoId, string $rutaImagen): int|bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("INSERT INTO ProductoImagen (producto_id, ruta_imagen) VALUES (?, ?)");
    $query->bind_param("is", $productoId, $rutaImagen);
    $query->execute();
    $id = $conexion->insert_id;
    $query->close();

    return $id;
  }

  public static function borrarPorProducto(int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("DELETE FROM ProductoImagen WHERE producto_id=?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $query->close();

    return true;
  }

  public static function listarPorProducto(int $productoId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM ProductoImagen WHERE producto_id=?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $resultado = $query->get_result();
    $imagenes = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->free();
    $query->close();

    return $imagenes;
  }
}
