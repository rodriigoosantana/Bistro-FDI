<?php

namespace es\ucm\fdi\aw\Oferta;

use DateTime;

// Clase Oferta
// Solo contiene propiedades, getters y setters.

class Oferta
{
    // region Campos privados
    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private DateTime $inicio;
    private DateTime $fin;
    private float $descuento; # 0.215 = 21.5%
    private bool $activa;
    // endregion

    // region Constructor
    public function __construct(string $nombre, string $descripcion, DateTime $inicio, DateTime $fin, float $descuento, ?int $id = null, bool $activa = true)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->inicio = $inicio;
        $this->fin = $fin;
        $this->descuento = $descuento;
        $this->activa = $activa;
    }
    // endregion

    // region Getters
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
    public function getInicio(): DateTime
    {
        return $this->inicio;
    }
    public function getFin(): DateTime
    {
        return $this->fin;
    }
    public function getDescuento(): float
    {
        return $this->descuento;
    }

    # solo comprueba el flag de borrado lógico
    public function isActiva(): bool
    {
        return $this->activa;
    }

    # vigente = activa (no borrada) + dentro de fechas
    public function isVigente(): bool
    {
        $hoy = new DateTime();
        return $this->activa && $this->inicio <= $hoy && $hoy <= $this->fin;
    }
    // endregion

    // region Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
    public function setInicio(DateTime $inicio): void
    {
        $this->inicio = $inicio;
    }
    public function setFin(DateTime $fin): void
    {
        $this->fin = $fin;
    }
    public function setDescuento(float $descuento): void
    {
        $this->descuento = $descuento;
    }

    public function setActiva(bool $activa): void
    {
        $this->activa = $activa;
    }
    // endregion
}
