<?php

enum Estado: string {
	case Nuevo = "nuevo"; // En proceso de creacion, aun poniendo productos
	case Recibido = "recibido"; // El pedido se ha terminado, pero no pagado
	case EnPreparacion = "en_preparacion"; // El pedido se ha pagado, a la espera de un camarero
	case Cocinando = "cocinando"; // Los productos se estan preparando por un cocinero
	case ListoCocina = "listo_cocina"; // Los productos se han terminado de preparar por la cocina, falta que lo prepare un camarero
	case Terminado = "terminado"; // Pedido completado, esperando que lo recoga un cliente
	case Entregado = "entregado"; // Pedido entregado a cliente (DONE)
	case Cancelado = "cancelado"; // Se puede cancelar en Nuevo o Recibido
};

enum Tipo: string {
	case ParaTomar = "local";
	case ParaLlevar = "llevar";
};

// Clase Pedido
// Solo contiene propiedades, getters y setters.

class Pedido
{
	// region Campos privados
	private int $id;
	private int $numero_pedido;
	private DateTime $fecha_creacion;
	private Estado $estado;
	private Tipo $tipo;
	private int $cliente_id;
	private int $cocinero_id;
	private float $total;
	// endregion

	// region Constructor
	public function __construct(int $numero_pedido, DateTime $fecha_creacion, Estado $estado, Tipo $tipo, int $cliente_id, int $cocinero_id, float $total, int $id = null)
	{
		$this->id = $id;
		$this->numero_pedido = $numero_pedido;
		$this->fecha_creacion = $fecha_creacion;
		$this->estado = $estado;
		$this->tipo = $tipo;
		$this->cliente_id = $cliente_id;
		$this->cocinero_id = $cocinero_id;
		$this->total = $total;
	}
	// endregion

	// region Getters
	public function getId(): int
	{
		return $this->id;
	}

	public function getNumeroPedido(): int
	{
		return $this->numero_pedido;
	}

	public function getFechaCreacion(): DateTime
	{
		return $this->fecha_creacion;
	}

	public function getEstado(): Estado
	{
		return $this->estado;
	}

	public function getTipo(): Tipo
	{
		return $this->tipo;
	}

	public function getClienteId(): int
	{
		return $this->cliente_id;
	}

	public function getCocineroId(): int
	{
		return $this->cocinero_id;
	}

	public function getTotal(): float
	{
		// TODO probablemente hay que calcular esto en base a PedidoProducto
		return $this->total;
	}
	// endregion

	// region Setters
	public function setId(int $id)
	{
		$this->id = $id;
	}

	public function setNumeroPedido(int $numero_pedido)
	{
		$this->numero_pedido = $numero_pedido;
	}

	public function setFechaCreacion(DateTime $fecha_creacion)
	{
		$this->fecha_creacion = $fecha_creacion;
	}

	public function setEstado(Estado $estado)
	{
		$this->estado = $estado;
	}

	public function setTipo(Tipo $tipo)
	{
		$this->tipo = $tipo;
	}

	public function setClienteId(int $cliente_id)
	{
		$this->cliente_id = $cliente_id;
	}

	public function setCocineroId(int $cocinero_id)
	{
		$this->cocinero_id = $cocinero_id;
	}

	public function setTotal(float $total)
	{
		$this->total = $total;
	}
	//endregion
}
?>
