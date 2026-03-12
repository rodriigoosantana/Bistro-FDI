<?php

namespace es\ucm\fdi\aw\Producto;

use es\ucm\fdi\aw\Producto\Categoria;
use es\ucm\fdi\aw\Aplicacion;

class CategoriaDB
{
  //Inserta una nueva categoría en la base de datos.
  public static function insertar(Categoria $categoria)
  {
    // Obtener la conexión a la base de datos
    $conexion = Aplicacion::getInstance()->getConexionBd();

    //Construcción de la query (petición SQL)
    $query = sprintf(
      "INSERT INTO Categorias (nombre, descripcion, imagen, activa)
            VALUES ('%s', '%s', '%s', %d)",
      $conexion->real_escape_string($categoria->getNombre()),
      $conexion->real_escape_string($categoria->getDescripcion()),
      $conexion->real_escape_string($categoria->getImagen()),
      $categoria->isActiva() ? 1 : 0
    );

    $conexion->query($query);
    $categoria->setId($conexion->insert_id); # Asignar el ID generado por la base de datos

    return $categoria;
  }

  //Actualiza una categoría existente en la base de datos.
  public static function actualizar(Categoria $categoria)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Categorias
             SET nombre='%s', descripcion='%s', imagen='%s', activa=%d
             WHERE id=%d",
      $conexion->real_escape_string($categoria->getNombre()),
      $conexion->real_escape_string($categoria->getDescripcion()),
      $conexion->real_escape_string($categoria->getImagen()),
      $categoria->isActiva() ? 1 : 0,
      intval($categoria->getId())
    );

    $conexion->query($query);

    return true;
  }

  public static function actualizarImagen(int $id, string $rutaImagen): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Categorias SET imagen='%s' WHERE id=%d",
      $conexion->real_escape_string($rutaImagen),
      $id
    );
    $conexion->query($query);
    
    return true; 
  }

  // Busca una categoría por su ID
  public static function buscarPorId($id)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Categorias WHERE id=%d",
      intval($id)
    );

    $resultado = $conexion->query($query);

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    if ($fila) {
      return new Categoria(
        $fila['nombre'],
        $fila['descripcion'],
        $fila['imagen'],
        (bool)$fila['activa'],
        intval($fila['id'])
      );
    }

    return null;
  }

  // Lista todas las categorías ordenadas por nombre
  public static function listarTodas()
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Categorias ORDER BY nombre ASC";

    $resultado = $conexion->query($query);

    $categorias = [];

    while ($fila = $resultado->fetch_assoc()) {  //Recorremos cada fila del resultado y creamos un objeto Categoria para cada una
      $categorias[] = new Categoria(
        $fila['nombre'],
        $fila['descripcion'],
        $fila['imagen'],
        (bool)$fila['activa'],
        intval($fila['id'])
      );
    }
    $resultado->free();

    return $categorias;
  }

  // Lista solo las categorías activas, ordenadas por nombre
  public static function listarActivas()
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Categorias WHERE activa = 1 ORDER BY nombre ASC";

    $resultado = $conexion->query($query);

    $categorias = [];

    while ($fila = $resultado->fetch_assoc()) {
      $categorias[] = new Categoria(
        $fila['nombre'],
        $fila['descripcion'],
        $fila['imagen'],
        (bool)$fila['activa'],
        intval($fila['id'])
      );
    }
    $resultado->free();

    return $categorias;
  }

  // Cambia el estado activa/inactiva de una categoría
  public static function cambiarEstado($id, $activa)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Categorias SET activa=%d WHERE id=%d",
      $activa ? 1 : 0,
      intval($id)
    );

    $conexion->query($query);
    return true;
  }
}
