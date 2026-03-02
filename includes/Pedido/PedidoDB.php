<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';

// Clase PedidoDB
// Capa de acceso a datos para Pedido.
// Contiene todas las operaciones SQL (INSERT, UPDATE, SELECT).
// Recibe y devuelve objetos Pedido (DTO).

class PedidoDB
{
	public static function insertar(Pedido $pedido)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"INSERT INTO Pedidos (id, numero_dia, fecha_creacion, estado_id, tipo, cliente_id, cocinero_id, total)
			VALUES ('%d', '%d', %s, %d, %s, %d, %d, %f)",

			intval($pedido->getId()),
			intval($pedido->getNumeroPedido()),
			$conexion->real_escape_string($pedido->getFechaCreacion().format("Y-m-d H:i:s")), // TODO now?
			intval($pedido->getEstadoId()),
			$conexion->real_escape_string($pedido->getTipo()),
			intval($pedido->getClienteId()),
			intval($pedido->getCocineroId()),
			floatval($pedido->getTotal()),
		);

		if ($conexion->query($query) == TRUE) {
			$pedido->setId($conexion->insert_id); # Asignar el id al pedido
			return $pedido;
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
			return null;
		}
	}

	public static function actualizar(Pedido $pedido)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"UPDATE Pedidos
			SET numero_pedido='%d', fecha_creacion='%s', estado_id='%d',
			tipo='%s', cliente_id='%d', cocinero_id='%d', total='%f'
			WHERE id=%d",

			intval($pedido->getNumeroPedido()),
			$conexion->real_escape_string($pedido->getFechaCreacion().format("Y-m-d H:i:s")), // TODO now?
			intval($pedido->getEstadoId()),
			$conexion->real_escape_string($pedido->getTipo()),
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


	public static function buscarPorId($id)
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
					$fila['fecha_creacion'],
					intval($fila['estado_id']),
					$fila['tipo'],
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

		$query = "SELECT * FROM Pedidos ORDER BY nombre ASC";

		$resultado = $conexion->query($query);

		$pedidos = [];

		if ($resultado) {
			while ($fila = $resultado->fetch_assoc()) {
				$pedidos [] = new Pedido(
					intval($fila['numero_pedido']),
					$fila['fecha_creacion'],
					intval($fila['estado_id']),
					$fila['tipo'],
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
					intval($fila['estado_id']),
					$fila['tipo'],
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

	// TODO figure out what we need here
	public static function listarPorCategoria($categoriaId)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"SELECT * FROM Pedidos WHERE categoria_id=%d ORDER BY nombre ASC",
			intval($categoriaId)
		);

		$resultado = $conexion->query($query);

		$productos = [];

		if ($resultado) {
			while ($fila = $resultado->fetch_assoc()) {
				$productos[] = new Producto(
					$fila['nombre'],
					$fila['descripcion'],
					intval($fila['categoria_id']),
					floatval($fila['precio_base']),
					intval($fila['iva']),
					(bool)$fila['disponible'],
					(bool)$fila['ofertado'],
					(bool)$fila['activo'],
					intval($fila['id'])
				);
			}
			$resultado->free();
		}
		else {
			error_log("Error BD ({$conexion->errno}): {$conexion->error}");
		}

		return $productos;
	}


	//Cambia la disponibilidad de un producto.
	// TODO Estado?
	public static function cambiarDisponibilidad($id, $disponible)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"UPDATE Productos SET disponible=%d WHERE id=%d",
			$disponible ? 1 : 0,
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


	//Cambia el estado activo/inactivo de un producto.
	public static function cambiarEstado($id, $activo)
	{
		$conexion = Aplicacion::getInstance()->getConexionBd();

		$query = sprintf(
			"UPDATE Productos SET activo=%d WHERE id=%d",
			$activo ? 1 : 0,
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
