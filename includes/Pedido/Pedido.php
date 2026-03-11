<?php

enum Estado: string {
	case Nuevo = "nuevo"; // En proceso de creacion, aun poniendo productos
	case Recibido = "recibido"; // El pedido se ha terminado, pero no pagado
	case EnPreparacion = "en preparacion"; // El pedido se ha pagado, a la espera de un camarero
	case Cocinando = "cocinando"; // Los productos se estan preparando por un cocinero
	case ListoCocina = "listo cocina"; // Los productos se han terminado de preparar por la cocina, falta que lo prepare un camarero
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
	private ?int $id;
	private int $numeroPedido;
	private DateTime $fechaCreacion;
	private Estado $estado;
	private Tipo $tipo;
	private int $clienteId;
	private ?int $cocineroId;
	private float $total;
	// endregion

	// region Constructor
	public function __construct(int $numeroPedido, DateTime $fechaCreacion, Estado $estado, Tipo $tipo, int $clienteId, ?int $cocineroId, float $total, ?int $id = null)
	{
		$this->id = $id;
		$this->numeroPedido = $numeroPedido;
		$this->fechaCreacion = $fechaCreacion;
		$this->estado = $estado;
		$this->tipo = $tipo;
		$this->clienteId = $clienteId;
		$this->cocineroId = $cocineroId;
		$this->total = $total;
	}
	// endregion

	// region Getters
	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNumeroPedido(): int
	{
		return $this->numeroPedido;
	}

	public function getFechaCreacion(): DateTime
	{
		return $this->fechaCreacion;
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
		return $this->clienteId;
	}

	public function getCocineroId(): ?int
	{
		return $this->cocineroId;
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

	public function setNumeroPedido(int $numeroPedido)
	{
		$this->numeroPedido = $numeroPedido;
	}

	public function setFechaCreacion(DateTime $fechaCreacion)
	{
		$this->fechaCreacion = $fechaCreacion;
	}

	public function setEstado(Estado $estado)
	{
		$this->estado = $estado;
	}

	public function setTipo(Tipo $tipo)
	{
		$this->tipo = $tipo;
	}

	public function setClienteId(int $clienteId)
	{
		$this->clienteId = $clienteId;
	}

	public function setCocineroId(?int $cocineroId)
	{
		$this->cocineroId = $cocineroId;
	}

	public function setTotal(float $total)
	{
		$this->total = $total;
	}
	//endregion
}
?>
