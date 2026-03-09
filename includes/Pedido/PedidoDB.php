<?php

require_once RAIZ_APP . '/includes/Pedido/Pedido.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoDesglosado.php';

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
      "INSERT INTO Pedidos (numero_pedido, fecha_creacion, estado, tipo, cliente_id, cocinero_id, total)
			VALUES (%d, '%s', '%s', '%s', %d, %d, %f)",
      intval($pedido->getNumeroPedido()),
      $conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")),
      $conexion->real_escape_string($pedido->getEstado()->value),
      $conexion->real_escape_string($pedido->getTipo()->value),
      intval($pedido->getClienteId()),
      intval($pedido->getCocineroId()),
      floatval($pedido->getTotal())
    );

    if ($conexion->query($query) === true) {
      $pedido->setId($conexion->insert_id);
      return $pedido;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return null;
    }
  }

  public static function update(Pedido $pedido): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Pedidos
			SET numero_pedido=%d, fecha_creacion='%s', estado='%s',
			tipo='%s', cliente_id=%d, cocinero_id=%d, total=%f
			WHERE id=%d",
      intval($pedido->getNumeroPedido()),
      $conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")),
      $conexion->real_escape_string($pedido->getEstado()->value),
      $conexion->real_escape_string($pedido->getTipo()->value),
      intval($pedido->getClienteId()),
      intval($pedido->getCocineroId()),
      floatval($pedido->getTotal()),
      intval($pedido->getId())
    );

    if ($conexion->query($query)) {
      return true;
    } else {
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
          Estado::from($fila['estado']),
          Tipo::from($fila['tipo']),
          intval($fila['cliente_id']),
          intval($fila['cocinero_id']),
          floatval($fila['total']),
          intval($fila['id'])
        );
      }
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }
    return null;
  }


  public static function listarTodos()
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Pedidos ORDER BY id ASC";

    $resultado = $conexion->query($query);

    $pedidos = [];

    if ($resultado) {
      while ($fila = $resultado->fetch_assoc()) {
        $pedidos[] = new Pedido(
          intval($fila['numero_pedido']),
          new DateTime($fila['fecha_creacion']),
          Estado::from($fila['estado']),
          Tipo::from($fila['tipo']),
          intval($fila['cliente_id']),
          intval($fila['cocinero_id']),
          floatval($fila['total']),
          intval($fila['id'])
        );
      }
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    return $pedidos;
  }


  public static function obtenerUltimoPedidoDelDia(DateTime $fecha)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT * FROM Pedidos
      WHERE DATE(fecha_creacion) = '%s'
      ORDER BY fecha_creacion DESC LIMIT 1",
      $fecha->format('Y-m-d')
    );

    $resultado = $conexion->query($query);

    if ($resultado) {
      if ($fila = $resultado->fetch_assoc()) {
        $pedido = new Pedido(
          intval($fila['numero_pedido']),
          new DateTime($fila['fecha_creacion']),
          Estado::from($fila['estado']),
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
      "UPDATE Pedidos SET estado='%s' WHERE id=%d",
      $conexion->real_escape_string($estado->value),
      intval($id)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
  }

  public static function getPedidoDesglosado(PedidoDesglosado $pedido)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT p.nombre, pp.precio_unitario, pp.cantidad
      FROM PedidoProducto pp
      JOIN Productos p ON pp.producto_id = p.id
      WHERE pp.pedido_id = %d",
      intval($pedido->getId())
    );

    $resultado = $conexion->query($query);

    $productos = [];

    if ($resultado) {
      while ($fila = $resultado->fetch_assoc()) {
        $productos[] = new ProductoEnPedido(
          $fila['nombre'],
          floatval($fila['precio_unitario']),
          intval($fila['cantidad'])
        );
      }
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    $pedido->setProductos($productos);
  }


  public static function listarPorEstados(array $estados = null, int $clienteId = null)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Pedidos";
    $condiciones = [];

    if ($estados && count($estados) > 0) {
      $estadosStr = implode(",", array_map(function ($e) use ($conexion) {
        $valor = ($e instanceof Estado) ? $e->value : $e;
        return "'" . $conexion->real_escape_string($valor) . "'";
      }, $estados));
      $condiciones[] = "estado IN ($estadosStr)";
    }

    if ($clienteId !== null) {
      $condiciones[] = "cliente_id = " . intval($clienteId);
    }

    if (count($condiciones) > 0) {
      $query .= " WHERE " . implode(" AND ", $condiciones);
    }

    $query .= " ORDER BY id ASC";

    $resultado = $conexion->query($query);

    $pedidos = [];

    if ($resultado) {
      while ($fila = $resultado->fetch_assoc()) {
        $pedidos[] = new Pedido(
          intval($fila['numero_pedido']),
          new DateTime($fila['fecha_creacion']),
          Estado::from($fila['estado']),
          Tipo::from($fila['tipo']),
          intval($fila['cliente_id']),
          intval($fila['cocinero_id']),
          floatval($fila['total']),
          intval($fila['id'])
        );
      }
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    return $pedidos;
  }
}
