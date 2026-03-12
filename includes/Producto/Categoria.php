<?php

namespace es\ucm\fdi\aw\Producto;

class Categoria
{
  //region Campos privados
  private $id;
  private $nombre;
  private $descripcion;
  private $imagen;
  private $activa;
  //endregion

  //region Constructor
  public function __construct($nombre, $descripcion, $imagen, $activa, $id = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->imagen = $imagen;
    $this->activa = $activa;
  }
  //endregion

  //region Propiedades
  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getDescripcion()
  {
    return $this->descripcion;
  }

  public function getImagen()
  {
    return $this->imagen;
  }

  public function isActiva()
  {
    return (bool) $this->activa;
  }
  //endregion

  //region setters
  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }

  public function setImagen($imagen)
  {
    $this->imagen = $imagen;
  }

  public function setActiva($activa)
  {
    $this->activa = $activa;
  }

  public function setId($id)
  {
    $this->id = $id;
  }
  //endregion
}
