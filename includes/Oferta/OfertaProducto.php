<?php

namespace es\ucm\fdi\aw\Oferta;

// DTO que representa una relación entre oferta y producto.

class OfertaProducto
{
    private ?int $id;
    private int $ofertaId;
    private int $productoId;
    private int $cantidad;

    public function __construct(int $ofertaId, int $productoId, int $cantidad, ?int $id = null)
    {
        $this->id = $id;
        $this->ofertaId = $ofertaId;
        $this->productoId = $productoId;
        $this->cantidad = $cantidad;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getOfertaId(): int
    {
        return $this->ofertaId;
    }
    public function getProductoId(): int
    {
        return $this->productoId;
    }
    public function getCantidad(): int
    {
        return $this->cantidad;
    }
}
