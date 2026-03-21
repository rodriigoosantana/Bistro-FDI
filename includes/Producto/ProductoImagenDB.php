<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Aplicacion;

class ProductoImagenDB
{
  public static function insertar(int $productoId, string $rutaImagen): int|bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $stmt = $conexion->prepare("INSERT INTO ProductoImagen (producto_id, ruta_imagen) VALUES (?, ?)");
    $stmt->bind_param("is", $productoId, $rutaImagen);
    $stmt->execute();
    $id = $conexion->insert_id;
    $stmt->close();

    return $id;
  }

  public static function borrarPorProducto(int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $stmt = $conexion->prepare("DELETE FROM ProductoImagen WHERE producto_id=?");
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    $stmt->close();

    return true;
  }

  public static function listarPorProducto(int $productoId): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $stmt = $conexion->prepare("SELECT * FROM ProductoImagen WHERE producto_id=?");
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $imagenes = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->free();
    $stmt->close();

    return $imagenes;
  }
}
