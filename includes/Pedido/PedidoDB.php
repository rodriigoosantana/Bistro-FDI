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

    $cocineroId = $pedido->getCocineroId() !== null ? intval($pedido->getCocineroId()) : "NULL";

    $query = sprintf(
      "INSERT INTO Pedidos (numero_pedido, fecha_creacion, estado, tipo, cliente_id, cocinero_id, total)
			VALUES (%d, '%s', '%s', '%s', %d, %s, %f)",
      intval($pedido->getNumeroPedido()),
      $conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")),
      $conexion->real_escape_string($pedido->getEstado()->value),
      $conexion->real_escape_string($pedido->getTipo()->value),
      intval($pedido->getClienteId()),
      $cocineroId,
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

    $cocineroId = $pedido->getCocineroId() !== null ? intval($pedido->getCocineroId()) : "NULL";

    $query = sprintf(
      "UPDATE Pedidos
			SET numero_pedido=%d, fecha_creacion='%s', estado='%s',
			tipo='%s', cliente_id=%d, cocinero_id=%s, total=%f
			WHERE id=%d",
      intval($pedido->getNumeroPedido()),
      $conexion->real_escape_string($pedido->getFechaCreacion()->format("Y-m-d H:i:s")),
      $conexion->real_escape_string($pedido->getEstado()->value),
      $conexion->real_escape_string($pedido->getTipo()->value),
      intval($pedido->getClienteId()),
      $cocineroId,
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

  public static function delete(int $id): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "DELETE FROM Pedidos WHERE id=%d",
      intval($id)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
  }

  public static function togglePreparadoStatus(int $productoId, int $pedidoId, bool $nuevoEstado): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE PedidoProducto SET preparado = %d WHERE producto_id = %d AND pedido_id = %d",
      $nuevoEstado ? 1 : 0,
      intval($productoId),
      intval($pedidoId)
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
          $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
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
          $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
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
          $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
          floatval($fila['total']),
          intval($fila['id'])
        );
        $resultado->free();
        return $pedido;
      }
    }

    return null;
  }

  public static function asignarCocinero(int $pedidoId, int $cocineroId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Pedidos SET cocinero_id=%d WHERE id=%d",
      intval($cocineroId),
      intval($pedidoId)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
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

   public static function insertarProductoPedido(int $pedidoId, int $productoId, int $cantidad, float $precioUnitario): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "INSERT INTO PedidoProducto (pedido_id, producto_id, cantidad, precio_unitario)
      VALUES (%d, %d, %d, %f)",
      intval($pedidoId),
      intval($productoId),
      intval($cantidad),
      floatval($precioUnitario)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
  }

  public static function actualizarProductoPedido(int $pedidoId, int $productoId, int $cantidad): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE PedidoProducto SET cantidad = %d WHERE pedido_id = %d AND producto_id = %d",
      intval($cantidad),
      intval($pedidoId),
      intval($productoId)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
  }

  public static function eliminarProductoPedido(int $pedidoId, int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "DELETE FROM PedidoProducto WHERE pedido_id = %d AND producto_id = %d",
      intval($pedidoId),
      intval($productoId)
    );

    if ($conexion->query($query)) {
      return true;
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
      return false;
    }
  }


  public static function listarPorEstados(array $estados = null, int $clienteId = null, int $cocineroId = null)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Pedidos";
    $condiciones = [];

    if ($estados && count($estados) > 0) {
      $estadosStr = implode(",", array_map(function ($estado) use ($conexion) {
        $valor = ($estado instanceof Estado) ? $estado->value : $estado;
        return "'" . $conexion->real_escape_string($valor) . "'";
      }, $estados));
      $condiciones[] = "estado IN ($estadosStr)";
    }

    if ($clienteId !== null) {
      $condiciones[] = "cliente_id = " . intval($clienteId);
    }

    if ($cocineroId !== null) {
      $condiciones[] = "cocinero_id = " . intval($cocineroId);
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
          $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
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

public static function getPedidoDesglosado(PedidoDesglosado $pedidoDesglosado)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT p.id as producto_id, p.nombre, pp.precio_unitario, pp.cantidad, pp.preparado
      FROM PedidoProducto pp
      JOIN Productos p ON pp.producto_id = p.id
      WHERE pp.pedido_id = %d",
      intval($pedidoDesglosado->getId())
    );

    $resultado = $conexion->query($query);

    $productos = [];

    if ($resultado) {
      while ($fila = $resultado->fetch_assoc()) {
        $productos[] = new ProductoEnPedido(
          intval($fila['producto_id']),
          $fila['nombre'],
          floatval($fila['precio_unitario']),
          intval($fila['cantidad']),
          boolval($fila['preparado'])
        );
      }
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    $pedidoDesglosado->setProductos($productos);
  }

  public static function productoEnPedidoNecesitaPreparacion(int $pedido_id, int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "SELECT c.necesita_preparacion
      FROM Productos p
      JOIN Categorias c ON p.categoria_id = c.id
      JOIN PedidoProducto pp ON p.id = pp.producto_id
      WHERE pp.pedido_id = %d AND p.id = %d", 
      intval($pedido_id),intval($productoId)
    );

    $resultado = $conexion->query($query);

    if ($resultado) {
      if ($fila = $resultado->fetch_assoc()) {
        return boolval($fila['necesita_preparacion']);
      }
      $resultado->free();
    } else {
      error_log("Error BD ({$conexion->errno}): {$conexion->error}");
    }

    return false;
  }
}
