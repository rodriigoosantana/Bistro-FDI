<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Categoria;
use es\ucm\fdi\aw\Producto\CategoriaDB;
use es\ucm\fdi\aw\Producto\ProductoService;


class CategoriaService
{

  public static function crear($nombre, $descripcion, ?array $imagen, $activa): ?Categoria
  {
    $dto = new Categoria($nombre, $descripcion, null, $activa);
    $categoria = CategoriaDB::insertar($dto);

    if ($categoria && $imagen) {
      $ruta = self::guardarImagen($categoria->getId(), $imagen);
      if ($ruta) {
        #Si se guarda la imagen correctamente, actualizar la categoría con la ruta de la imagen
        $categoria->setImagen($ruta);
        CategoriaDB::actualizarImagen($categoria->getId(), $ruta); #Actualizar la categoría con la ruta de la imagen
      }
    }

    return $categoria ?? null;
  }

  public static function actualizar($id, $nombre, $descripcion, ?array $imagen, $activa): bool
  {

    #Si hay una imagen nueva, borrar la imagen anterior y guardar la nueva
    if ($imagen) {
      $categoriaActual = CategoriaDB::buscarPorId($id);
      if ($categoriaActual && $categoriaActual->getImagen()) {
        $rutaFisica = RAIZ_APP . $categoriaActual->getImagen();
        if (file_exists($rutaFisica)) {
          unlink($rutaFisica); #Eliminar la imagen anterior del servidor
        }
      }
      $fichero = self::guardarImagen($id, $imagen); #Guardar la nueva imagen y obtener su ruta
    } else {
      #Si no se proporciona una nueva imagen, mantener la ruta de la imagen actual
      $fichero = CategoriaDB::buscarPorId($id)->getImagen();
    }

    $categoria = new Categoria($nombre, $descripcion, $fichero, $activa, $id);
    return CategoriaDB::actualizar($categoria);
    #devuelve true si se actualiza correctamente, false si falla
  }

  public static function buscarPorId($id): ?Categoria
  {
    return CategoriaDB::buscarPorId($id);
    #devuelve el DTO de la categoría con el id indicado, o null si no existe
  }

  public static function listarTodas(): array
  {
    return CategoriaDB::listarTodas();
    #devuelve array de categorías, o array vacío si no hay categorías
  }

  public static function listarActivas(): array
  {
      return CategoriaDB::listarActivas();
  }

  public static function cambiarEstado($id, $activa): bool
  {
    # Si se desactiva la categoría, también se desactivan todos los productos asociados a esa categoría
    if(!$activa) {
      ProductoService::desactivarPorCategoria($id); #Desactivar todos los productos de la categoría si se desactiva la categoría
    }
    return CategoriaDB::cambiarEstado($id, $activa);
    #devuelve true si se actualiza correctamente, false si falla
  }

  private static function guardarImagen(int $categoriaId, array $fichero): ?string
  {
    # $fichero es $_FILES['imagen'], un único fichero con keys: name, tmp_name, error, size

    #Validar que no hubo errores en la subida
    if ($fichero['error'] !== UPLOAD_ERR_OK) {
      error_log("Error al subir imagen: " . $fichero['error']);
      return null;
    }

    #Validar extensión
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($fichero['name'], PATHINFO_EXTENSION)); #obtener extensión del archivo
    if (!in_array($extension, $extensionesPermitidas)) {
      error_log("Archivo no permitido: " . $fichero['name']);
      return null;  #Devolver null si la extensión no es válida
    }

    #Crear directorio si no existe
    $dir = RAIZ_APP . '/img/uploads/categorias/';
    if (!is_dir($dir)) {
      mkdir($dir, 0775, true); # crea el directorio si no existe
    } else {
      chmod($dir, 0775); # asegura permisos de escritura para el servidor
    }

    #Generar nombre único paraF evitar colisiones
    $nombreArchivo = 'categoria_' . $categoriaId . '_' . uniqid() . '.' . $extension;
    $rutaDestino = $dir . $nombreArchivo;  #Ruta completa en el servidor
    $rutaBD = '/img/uploads/categorias/' . $nombreArchivo; #Ruta    

    #Mover el archivo subido a su ubicación definitiva
    if (move_uploaded_file($fichero['tmp_name'], $rutaDestino)) { #Si se mueve correctamente, guardar la ruta en la base de datos
      return $rutaBD; #Devolver la ruta relativa para almacenar en BD
    } else {
      error_log("Error al mover archivo subido: " . $fichero['name']);
      return null; #Devolver null si no se pudo mover el archivo
    }
  }

}
