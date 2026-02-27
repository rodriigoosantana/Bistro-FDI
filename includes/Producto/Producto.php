<?php

//Clase Producto
//Solo contiene propiedades, getters y setters.

class Producto
{
   //region Campos privados
   private $id;
   private $nombre;
   private $descripcion;
   private $categoriaId;
   private $precioBase;
   private $iva;
   private $disponible;
   private $ofertado;
   private $activo;
   //endregion

   //region Constructor
   public function __construct($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id = null)
   {
      $this->id = $id;
      $this->nombre = $nombre;
      $this->descripcion = $descripcion;
      $this->categoriaId = $categoriaId;
      $this->precioBase = $precioBase;
      $this->iva = $iva;
      $this->disponible = $disponible;
      $this->ofertado = $ofertado;
      $this->activo = $activo;
   }
   //endregion

   //region Getters
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

   public function getCategoriaId()
   {
      return $this->categoriaId;
   }

   public function getPrecioBase()
   {
      return $this->precioBase;
   }

   public function getPrecioFinal()
   {
      return $this->precioBase * (1 + $this->iva / 100);
   }

   public function getIva()
   {
      return $this->iva;
   }

   public function isDisponible()
   {
      return (bool) $this->disponible;
   }

   public function isOfertado()
   {
      return (bool) $this->ofertado;
   }

   public function isActivo()
   {
      return (bool) $this->activo;
   }
   //endregion

   //region Setters
   public function setId($id)
   {
      $this->id = $id;
   }

   public function setNombre($nombre)
   {
      $this->nombre = $nombre;
   }

   public function setDescripcion($descripcion)
   {
      $this->descripcion = $descripcion;
   }

   public function setCategoriaId($categoriaId)
   {
      $this->categoriaId = $categoriaId;
   }

   public function setPrecioBase($precioBase)
   {
      $this->precioBase = $precioBase;
   }

   public function setIva($iva)
   {
      $this->iva = $iva;
   }

   public function setDisponible($disponible)
   {
      $this->disponible = $disponible;
   }

   public function setOfertado($ofertado)
   {
      $this->ofertado = $ofertado;
   }

   public function setActivo($activo)
   {
      $this->activo = $activo;
   }
   //endregion
}
?>