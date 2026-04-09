<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Categoria;
use es\ucm\fdi\aw\Aplicacion;

class CategoriaDB
{
  // Construye un objeto Categoria a partir de una fila de BD
  private static function filaACategoria(array $fila): Categoria
  {
    return new Categoria(
      $fila['nombre'],
      $fila['descripcion'],
      $fila['imagen'],
      (bool) $fila['activa'],
      intval($fila['id'])
    );
  }

  public static function insertar(Categoria $categoria): ?Categoria
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "INSERT INTO Categorias (nombre, descripcion, imagen, activa, necesita_preparacion)
       VALUES (?, ?, ?, ?, ?)"
    );

    $nombre              = $categoria->getNombre();
    $descripcion         = $categoria->getDescripcion();
    $imagen              = $categoria->getImagen();
    $activa              = $categoria->isActiva() ? 1 : 0;
    $necesitaPreparacion = $categoria->necesitaPreparacion() ? 1 : 0;

    $query->bind_param("sssii", $nombre, $descripcion, $imagen, $activa, $necesitaPreparacion);
    $query->execute();
    $categoria->setId($conexion->insert_id);
    $query->close();

    return $categoria;
  }

  public static function actualizar(Categoria $categoria): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "UPDATE Categorias SET nombre=?, descripcion=?, imagen=?, activa=? WHERE id=?"
    );

    $nombre      = $categoria->getNombre();
    $descripcion = $categoria->getDescripcion();
    $imagen      = $categoria->getImagen();
    $activa      = $categoria->isActiva() ? 1 : 0;
    $id          = $categoria->getId();

    $query->bind_param("sssii", $nombre, $descripcion, $imagen, $activa, $id);
    $query->execute();
    $query->close();

    return true;
  }

  public static function actualizarImagen(int $id, string $rutaImagen): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("UPDATE Categorias SET imagen=? WHERE id=?");
    $query->bind_param("si", $rutaImagen, $id);
    $query->execute();
    $query->close();

    return true;
  }

  public static function buscarPorId(int $id): ?Categoria
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM Categorias WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $resultado = $query->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $query->close();

    return $fila ? self::filaACategoria($fila) : null;
  }

  public static function listarTodas(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM Categorias ORDER BY nombre ASC");
    $query->execute();
    $resultado = $query->get_result();

    $categorias = [];
    while ($fila = $resultado->fetch_assoc()) {
      $categorias[] = self::filaACategoria($fila);
    }
    $resultado->free();
    $query->close();

    return $categorias;
  }

  public static function listarActivas(): array
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM Categorias WHERE activa=1 ORDER BY nombre ASC");
    $query->execute();
    $resultado = $query->get_result();

    $categorias = [];
    while ($fila = $resultado->fetch_assoc()) {
      $categorias[] = self::filaACategoria($fila);
    }
    $resultado->free();
    $query->close();

    return $categorias;
  }

  public static function cambiarEstado(int $id, bool $activa): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $valor = $activa ? 1 : 0;
    $query = $conexion->prepare("UPDATE Categorias SET activa=? WHERE id=?");
    $query->bind_param("ii", $valor, $id);
    $query->execute();
    $query->close();

    return true;
  }
}
