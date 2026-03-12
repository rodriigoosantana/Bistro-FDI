<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Producto;
use es\ucm\fdi\aw\Producto\ProductoDB;
use es\ucm\fdi\aw\Producto\ProductoImagenDB;

// Clase ProductoService (Lógica de negocio)
class ProductoService
{
  public static function crear(
    $nombre,
    $descripcion,
    $categoriaId,
    $precioBase,
    $iva,
    $disponible,
    $ofertado,
    $activo,
    ?array $imagenes
  ): ?Producto {
    $dto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
    $producto = ProductoDB::insertar($dto); // devuelve DTO con id si se inserta correctamente

    if ($producto && $imagenes) {
      self::guardarImagenes($producto->getId(), $imagenes);
    }

    return $producto ?? null;
  }

  public static function actualizar(
    $id,
    $nombre,
    $descripcion,
    $categoriaId,
    $precioBase,
    $iva,
    $disponible,
    $ofertado,
    $activo,
    ?array $imagenes = null
  ): bool {
    $producto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id);
    $ok = ProductoDB::actualizar($producto);

    if ($ok && $imagenes !== null) {
      // borrar imágenes anteriores
      $img_actuales = ProductoImagenDB::listarPorProducto($id);
      foreach ($img_actuales as $img) {
        $ruta = RAIZ_APP . $img['ruta_imagen'];
        if (file_exists($ruta)) {
          unlink($ruta);
        }
      }

      ProductoImagenDB::borrarPorProducto($id);
      self::guardarImagenes($id, $imagenes);
    }

    return $ok;
  }

  public static function eliminar(int $id): bool
  {
    // borrar imágenes del disco
    $imagenes = ProductoImagenDB::listarPorProducto($id);
    foreach ($imagenes as $img) {
      $rutaFisica = RAIZ_APP . $img['ruta_imagen'];
      if (file_exists($rutaFisica)) {
        unlink($rutaFisica);
      }
    }
    ProductoImagenDB::borrarPorProducto($id);

    return ProductoDB::eliminar($id);
  }

  public static function listarImagenes(int $productoId): array
  {
    return ProductoImagenDB::listarPorProducto($productoId);
  }

  public static function buscarPorId($id): ?Producto
  {
    return ProductoDB::buscarPorId($id);
  }

  public static function listarTodos(): array
  {
    return ProductoDB::listarTodos();
  }

  public static function listarActivos(): array
  {
    return ProductoDB::listarActivos();
  }

  public static function listarActivosPorCategoria(int $categoriaId): array
  {
    return ProductoDB::listarActivosPorCategoria($categoriaId);
  }

  public static function listarPorCategoria($categoriaId): array
  {
    return ProductoDB::listarPorCategoria($categoriaId);
  }

  public static function cambiarDisponibilidad($id, $disponible): bool
  {
    return ProductoDB::cambiarDisponibilidad($id, $disponible);
  }

  public static function cambiarEstado($id, $activo): bool
  {
    return ProductoDB::cambiarEstado($id, $activo);
  }

  public static function contarDisponiblesPorCategoria(int $categoriaId): int
  {
    return ProductoDB::contarDisponiblesPorCategoria($categoriaId);
  }

  // Manejo de imágenes subidas
  private static function guardarImagenes(int $productoId, array $imagenes): void
  {
    $dir = RAIZ_APP . '/img/uploads/productos/';
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }

    $extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $total = count($imagenes['name']);

    for ($i = 0; $i < $total; $i++) {
      if ($imagenes['error'][$i] !== UPLOAD_ERR_OK) continue;

      $extension = strtolower(pathinfo($imagenes['name'][$i], PATHINFO_EXTENSION));
      if (!in_array($extension, $extensiones)) continue;

      $nombreArchivo = 'producto_' . $productoId . '_' . uniqid() . '.' . $extension;
      $rutaDestino = $dir . $nombreArchivo;
      $rutaBD = '/img/uploads/productos/' . $nombreArchivo;

      if (move_uploaded_file($imagenes['tmp_name'][$i], $rutaDestino)) {
        ProductoImagenDB::insertar($productoId, $rutaBD);
      } else {
        error_log("Error al mover archivo subido: " . $imagenes['name'][$i]);
      }
    }
  }
}
