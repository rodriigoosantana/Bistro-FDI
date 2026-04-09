<?php

namespace es\ucm\fdi\aw\Recompensa;

class Recompensa
{
  private $id;
  private $producto_id;
  private $bistroCoins_necesarias;

  public function __construct($producto_id, $bistroCoins_necesarias, $id = null)
  {
    $this->producto_id = $producto_id;
    $this->bistroCoins_necesarias = $bistroCoins_necesarias;
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getProductoId()
  {
    return $this->producto_id;
  }

  public function getBistroCoinsNecesarias()
  {
    return $this->bistroCoins_necesarias;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setProductoId($producto_id)
  {
    $this->producto_id = $producto_id;
  }

  public function setBistroCoinsNecesarias($bistroCoins_necesarias)
  {
    $this->bistroCoins_necesarias = $bistroCoins_necesarias;
  }
}
