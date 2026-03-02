<?php

// Clase Pedido
// Solo contiene propiedades, getters y setters.

class Pedido
{
	// region Campos privados
	private $id;
	private $numero_pedido;
	private $fecha_creacion;
	private $estado_id;
	private $tipo;
	private $cliente_id;
	private $cocinero_id;
	private $total;
	// endregion

	// region Constructor
	public function __construct($numero_pedido, $fecha_creacion, $estado_id, $tipo, $cliente_id, $cocinero_id, $total, $id = null)
	{
		$this->id = $id;
		$this->numero_pedido = $numero_pedido;
		$this->fecha_creacion = $fecha_creacion;
		$this->estado_id = $estado_id;
		$this->tipo = $tipo;
		$this->cliente_id = $cliente_id;
		$this->cocinero_id = $cocinero_id;
		$this->total = $total;
	}
	// endregion

	// region Getters
	public function getId()
	{
		return $this->id;
	}

	public function getNumeroPedido()
	{
		return $this->numero_pedido;
	}

	public function getFechaCreacion()
	{
		return $this->fecha_creacion;
	}

	public function getEstadoId()
	{
		return $this->estado_id;
	}

	public function getTipo()
	{
		return $this->tipo;
	}

	public function getClienteId()
	{
		return $this->cliente_id;
	}

	public function getCocineroId()
	{
		return $this->cocinero_id;
	}

	public function getTotal()
	{
		// TODO probablemente hay que calcular esto en base a PedidoProducto
		return $this->total;
	}
	// endregion

	// region Setters
	public function setId($id)
	{
		$this->id = $id;
	}

	public function setNumeroPedido($numero_pedido)
	{
		$this->numero_pedido = $numero_pedido;
	}

	public function setFechaCreacion($fecha_creacion)
	{
		$this->fecha_creacion = $fecha_creacion;
	}

	public function setEstadoId($estado_id)
	{
		$this->estado_id = $estado_id;
	}

	public function setTipo($tipo)
	{
		$this->tipo = $tipo;
	}

	public function setClienteId($cliente_id)
	{
		$this->cliente_id = $cliente_id;
	}

	public function setCocineroId($cocinero_id)
	{
		$this->cocinero_id = $cocinero_id;
	}

	public function setTotal($total)
	{
		$this->total = $total;
	}
	//endregion
}
?>
