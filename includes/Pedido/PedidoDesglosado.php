<?php

class PedidoDesglosado extends Pedido
{
  /** @var ProductoEnPedido[] */
  private array $productos = [];

  public function __construct(Pedido $pedido, array $productos)
  {
    parent::__construct(
      $pedido->getNumeroPedido(),
      $pedido->getFechaCreacion(),
      $pedido->getEstado(),
      $pedido->getTipo(),
      $pedido->getClienteId(),
      $pedido->getCocineroId(),
      $pedido->getTotal(),
      $pedido->getId()
    );
    $this->productos = $productos;
  }

  public function getProductos(): array
  {
    return $this->productos;
  }

  public function setProductos(array $productos): void
  {
    $this->productos = $productos;
  }
}

class ProductoEnPedido
{
  private string $nombre;
  private float $precio;
  private int $cantidad;
  private bool $preparado;
  private int $id;

  public function __construct(int $id, string $n, float $p, int $c, bool $preparado)
  {
    $this->id = $id;
    $this->nombre = $n;
    $this->precio = $p;
    $this->cantidad = $c;
    $this->preparado = $preparado;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getNombre(): string
  {
    return $this->nombre;
  }

  public function getPrecio(): float
  {
    return $this->precio;
  }

  public function getCantidad(): int
  {
    return $this->cantidad;
  }

  public function getProductoId(): int
  {
    return $this->id;
  }

  public function setNombre(string $nombre): void
  {
    $this->nombre = $nombre;
  }

  public function setPrecio(float $precio): void
  {
    $this->precio = $precio;
  }

  public function setCantidad(int $cantidad): void
  {
    $this->cantidad = $cantidad;
  }

  public function setProductoId(int $productoId): void
  {
    $this->productoId = $id;
  }

  public function isPreparado(): bool
  {
    return $this->preparado;
  }

  public function setPreparado(bool $preparado): void
  {
    $this->preparado = $preparado;
  }
}
