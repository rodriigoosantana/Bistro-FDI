<?php

//Clase Producto
//Solo contiene propiedades, getters y setters.

class Producto
{
   //region Campos privados
   private ?int $id;
   private string $nombre;
   private string $descripcion;
   private int $categoriaId;
   private float $precioBase;
   private int $iva;
   private bool $disponible;
   private bool $ofertado;
   private bool $activo;
   //endregion

   //region Constructor
   public function __construct(string $nombre, string $descripcion, int $categoriaId, float $precioBase, int $iva, bool $disponible, bool $ofertado, bool $activo, ?int $id = null)
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
   public function getId(): ?int
   {
      return $this->id;
   }

   public function getNombre(): string
   {
      return $this->nombre;
   }

   public function getDescripcion(): string
   {
      return $this->descripcion;
   }

   public function getCategoriaId(): int
   {
      return $this->categoriaId;
   }

   public function getPrecioBase(): float
   {
      return $this->precioBase;
   }

   public function getPrecioFinal(): float
   {
      return $this->precioBase * (1 + $this->iva / 100);
   }

   public function getIva(): int
   {
      return $this->iva;
   }

   public function isDisponible(): bool
   {
      return (bool) $this->disponible;
   }

   public function isOfertado(): bool
   {
      return (bool) $this->ofertado;
   }

   public function isActivo(): bool
   {
      return (bool) $this->activo;
   }
   //endregion

   //region Setters
   public function setId($id): void
   {
      $this->id = $id;
   }

   public function setNombre($nombre): void
   {
      $this->nombre = $nombre;
   }

   public function setDescripcion($descripcion): void
   {
      $this->descripcion = $descripcion;
   }

   public function setCategoriaId($categoriaId): void
   {
      $this->categoriaId = $categoriaId;
   }

   public function setPrecioBase($precioBase): void
   {
      $this->precioBase = $precioBase;
   }

   public function setIva($iva): void
   {
      $this->iva = $iva;
   }

   public function setDisponible($disponible): void
   {
      $this->disponible = $disponible;
   }

   public function setOfertado($ofertado): void
   {
      $this->ofertado = $ofertado;
   }

   public function setActivo($activo): void
   {
      $this->activo = $activo;
   }
   //endregion
}
?>