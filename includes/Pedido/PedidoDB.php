<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';

// Clase PedidoDB
// Capa de acceso a datos para Pedido.
// Contiene todas las operaciones SQL (INSERT, UPDATE, SELECT).
// Recibe y devuelve objetos Pedido (DTO).

class PedidoDB
{
	public static function insert(Pedido $pedido)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"INSERT INTO Pedidos (numero_dia, fecha_creacion, estado, tipo, cliente_id, cocinero_id, total)
			VALUES (%d, %s, %s, %s, %d, %d, %f)",

			intval($pedido->getNumeroPedido()),
			$conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")), // TODO now?
			$conexion->real_escape_string($pedido->getEstado()->value),
			$conexion->real_escape_string($pedido->getTipo()->value),
			intval($pedido->getClienteId()),
			intval($pedido->getCocineroId()),
			floatval($pedido->getTotal()),
		);

		if ($conexion->query($query) == true) {
			$pedido->setId($conexion->insert_id); # Asignar el id al pedido
			return $pedido;
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
			return null;
		}
	}

	public static function update(Pedido $pedido): bool
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"UPDATE Pedidos
			SET numero_pedido='%d', fecha_creacion='%s', estado_id='%d',
			tipo='%s', cliente_id='%d', cocinero_id='%d', total='%f'
			WHERE id=%d",

			intval($pedido->getNumeroPedido()),
			$conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")), // TODO now?
			$conexion->real_escape_string($pedido->getEstado()->value),
			$conexion->real_escape_string($pedido->getTipo()->value),
			intval($pedido->getClienteId()),
			intval($pedido->getCocineroId()),
			floatval($pedido->getTotal()),
			intval($pedido->getId())
		);

		if ($conexion->query($query)) {
			return true;
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
			return false;
		}
	}


	public static function buscarPorId(int $id)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"SELECT * FROM Pedidos WHERE id=%d",
			intval($id)
		);

		$resultado = $conexion->query($query);

		if ($resultado) {
			$fila = $resultado->fetch_assoc();
			$resultado->free();

			if ($fila) {
				return new Pedido(
					intval($fila['numero_pedido']),
					new DateTime($fila['fecha_creacion']),
					Estado::from($fila['estado']), // Cast de string al enum
					Tipo::from($fila['tipo']), // Cast de string al enum
					intval($fila['cliente_id']),
					intval($fila['cocinero_id']),
					floatval($fila['total']),
					intval($fila['id'])
				);
			}
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
			return false;
		}
	}


	public static function listarTodos()
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = "SELECT * FROM Pedidos ORDER BY id ASC";

		$resultado = $conexion->query($query);

		$pedidos = [];

		if ($resultado) {
			while ($fila = $resultado->fetch_assoc()) {
				$pedidos [] = new Pedido(
					intval($fila['numero_pedido']),
					$fila['fecha_creacion'],
					Estado::from($fila['estado']),
					Tipo::from($fila['tipo']),
					intval($fila['cliente_id']),
					intval($fila['cocinero_id']),
					floatval($fila['total']),
					intval($fila['id'])
				);
			}
			$resultado->free();
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
		}

		return $pedidos; // En este caso si hay error devuelve array vacío en vez de false
	}

	// Devolver el pedido hecho mas reciente en la fecha (DateTime) indicada o null si no ha habido ninguno en esa fecha
	public static function obtenerUltimoPedidoDelDia(DateTime $fecha)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"SELECT * FROM Pedidos
			WHERE DATE(fecha_creacion) = %s
			ORDER BY fecha_creacion DESC LIMIT 1",
			$fecha->format('Y-m-d')
		);

		$resultado = $conexion->query($query);

		if ($resultado) {
			if ($fila = $resultado->fetch_assoc()) {
				$pedido = new Pedido(
					intval($fila['numero_pedido']),
					$fila['fecha_creacion'],
					Estado::from($fila['estado_id']),
					Tipo::from($fila['tipo']),
					intval($fila['cliente_id']),
					intval($fila['cocinero_id']),
					floatval($fila['total']),
					intval($fila['id'])
				);
				$resultado->free();

				return $pedido;
			}
		}

		return null;
	}

	public static function cambiarEstado(int $id, Estado $estado)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"UPDATE Productos SET estado=%s WHERE id=%d",
			$estado,
			intval($id)
		);

		if ($conexion->query($query)) {
			return true;
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
			return false;
		}
	}
}
?>
