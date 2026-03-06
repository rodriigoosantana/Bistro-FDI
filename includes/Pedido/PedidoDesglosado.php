<?php

class PedidoDesglosado extends Pedido
{
  /** @var ProductoEnPedido[] */
  private array $productos = [];

  public function __construct(Pedido $p, array $productos)
  {
    parent::__construct(
      $p->getNumeroPedido(),
      $p->getFechaCreacion(),
      $p->getEstado(),
      $p->getTipo(),
      $p->getClienteId(),
      $p->getCocineroId(),
      $p->getTotal()
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

  public function __construct(string $n, float $p, int $c)
  {
    $this->nombre = $n;
    $this->precio = $p;
    $this->cantidad = $c;
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

  public function setNombre(string $n): void
  {
    $this->nombre = $n;
  }

  public function setPrecio(float $p): void
  {
    $this->precio = $p;
  }

  public function setCantidad(int $c): void
  {
    $this->cantidad = $c;
  }
}
