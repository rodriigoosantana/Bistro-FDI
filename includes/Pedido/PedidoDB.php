<?php

namespace es\ucm\fdi\aw\Pedido;

use es\ucm\fdi\aw\Pedido\Pedido;
use es\ucm\fdi\aw\Pedido\PedidoDesglosado;
use es\ucm\fdi\aw\Aplicacion;
use \DateTime;

// Clase PedidoDB
// Capa de acceso a datos para Pedido.
// Contiene todas las operaciones SQL (INSERT, UPDATE, SELECT).
// Recibe y devuelve objetos Pedido (DTO).

class PedidoDB
{
  public static function insert(Pedido $pedido)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "INSERT INTO Pedidos (numero_pedido, fecha_creacion, estado, tipo, cliente_id, cocinero_id, total, descuento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $numeroPedido = intval($pedido->getNumeroPedido());
    $fechaCreacion = $pedido->getFechaCreacion()->format("Y-m-d H:i:s");
    $estado = $pedido->getEstado()->value;
    $tipo = $pedido->getTipo()->value;
    $clienteId = intval($pedido->getClienteId());
    $cocineroId = $pedido->getCocineroId() !== null ? intval($pedido->getCocineroId()) : null;
    $total = floatval($pedido->getTotal());
    $descuento = floatval($pedido->getDescuento());

    $query->bind_param("isssiidd", $numeroPedido, $fechaCreacion, $estado, $tipo, $clienteId, $cocineroId, $total, $descuento);

    $query->execute();
    $pedido->setId($conexion->insert_id);
    $query->close();
    return $pedido;
  }

  public static function update(Pedido $pedido): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "UPDATE Pedidos
			SET numero_pedido=?, fecha_creacion=?, estado=?,
			tipo=?, cliente_id=?, cocinero_id=?, total=?, descuento=?
			WHERE id=?"
    );

    $numeroPedido = intval($pedido->getNumeroPedido());
    $fechaCreacion = $pedido->getFechaCreacion()->format("Y-m-d H:i:s");
    $estado = $pedido->getEstado()->value;
    $tipo = $pedido->getTipo()->value;
    $clienteId = intval($pedido->getClienteId());
    $cocineroId = $pedido->getCocineroId() !== null ? intval($pedido->getCocineroId()) : null;
    $total = floatval($pedido->getTotal());
    $descuento = floatval($pedido->getDescuento());
    $id = intval($pedido->getId());

    $query->bind_param("isssiiddi", $numeroPedido, $fechaCreacion, $estado, $tipo, $clienteId, $cocineroId, $total, $descuento, $id);

    $query->execute();
    $query->close();

    return true;
  }

  public static function delete(int $id): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("DELETE FROM Pedidos WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->close();

    return true;
  }

  public static function togglePreparadoStatus(int $productoId, int $pedidoId, bool $nuevoEstado): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("UPDATE PedidoProducto SET preparado = ? WHERE producto_id = ? AND pedido_id = ?");
    $estadoPreparado = $nuevoEstado ? 1 : 0;
    $query->bind_param("iii", $estadoPreparado, $productoId, $pedidoId);

    $query->execute();
    $query->close();
    return true;
  }

  public static function buscarPorId(int $id)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM Pedidos WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $resultado = $query->get_result();

    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $query->close();

    if ($fila) {
      return new Pedido(
        intval($fila['numero_pedido']),
        new DateTime($fila['fecha_creacion']),
        Estado::from($fila['estado']),
        Tipo::from($fila['tipo']),
        intval($fila['cliente_id']),
        $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
        floatval($fila['total']),
        intval($fila['id']),
        floatval($fila['descuento'])
      );
    }

    return null;
  }


  public static function listarTodos()
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("SELECT * FROM Pedidos ORDER BY id ASC");
    $query->execute();
    $resultado = $query->get_result();

    $pedidos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $pedidos[] = new Pedido(
        intval($fila['numero_pedido']),
        new DateTime($fila['fecha_creacion']),
        Estado::from($fila['estado']),
        Tipo::from($fila['tipo']),
        intval($fila['cliente_id']),
        $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
        floatval($fila['total']),
        intval($fila['id']),
        floatval($fila['descuento'])
      );
    }
    $resultado->free();
    $query->close();

    return $pedidos;
  }


  public static function obtenerUltimoPedidoDelDia(DateTime $fecha)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "SELECT * FROM Pedidos
      WHERE DATE(fecha_creacion) = ?
      ORDER BY fecha_creacion DESC LIMIT 1"
    );
    $fechaFormateada = $fecha->format('Y-m-d');
    $query->bind_param("s", $fechaFormateada);
    $query->execute();
    $resultado = $query->get_result();

    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $query->close();

    if (!$fila) {
      return null;
    }

    $pedido = new Pedido(
      intval($fila['numero_pedido']),
      new DateTime($fila['fecha_creacion']),
      Estado::from($fila['estado']),
      Tipo::from($fila['tipo']),
      intval($fila['cliente_id']),
      $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
      floatval($fila['total']),
      intval($fila['id']),
      floatval($fila['descuento'])
    );


    return $pedido;
  }

  public static function asignarCocinero(int $pedidoId, int $cocineroId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("UPDATE Pedidos SET cocinero_id=? WHERE id=?");
    $query->bind_param("ii", $cocineroId, $pedidoId);

    $query->execute();
    $query->close();
    return true;
  }

  public static function cambiarEstado(int $id, Estado $estado)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("UPDATE Pedidos SET estado=? WHERE id=?");
    $estadoValor = $estado->value;
    $query->bind_param("si", $estadoValor, $id);
    $query->execute();
    $query->close();

    return true;
  }

  public static function insertarProductoPedido(int $pedidoId, int $productoId, int $cantidad, float $precioUnitario): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "INSERT INTO PedidoProducto (pedido_id, producto_id, cantidad, precio_unitario)
      VALUES (?, ?, ?, ?)"
    );
    $query->bind_param("iiid", $pedidoId, $productoId, $cantidad, $precioUnitario);
    $query->execute();
    $query->close();

    return true;
  }

  public static function actualizarProductoPedido(int $pedidoId, int $productoId, int $cantidad): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("UPDATE PedidoProducto SET cantidad = ? WHERE pedido_id = ? AND producto_id = ?");
    $query->bind_param("iii", $cantidad, $pedidoId, $productoId);
    $query->execute();
    $query->close();

    return true;
  }

  public static function actualizarProductoBitCoineado(int $pedidoId, int $productoId, int $bc): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE PedidoProducto SET bistro_coineado = %d WHERE pedido_id = %d AND producto_id = %d",
      intval($bc),
      intval($pedidoId),
      intval($productoId)
    );

    $conexion->query($query);

    return true;
  }

  public static function eliminarProductoPedido(int $pedidoId, int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare("DELETE FROM PedidoProducto WHERE pedido_id = ? AND producto_id = ?");
    $query->bind_param("ii", $pedidoId, $productoId);
    $query->execute();
    $query->close();

    return true;
  }


  public static function listarPorEstados(array $estados = null, int $clienteId = null, int $cocineroId = null)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = "SELECT * FROM Pedidos";
    $condiciones = [];
    $types = "";
    $params = [];

    if ($estados && count($estados) > 0) {
      $placeholders = implode(",", array_fill(0, count($estados), "?"));
      $condiciones[] = "estado IN ($placeholders)";
      foreach ($estados as $estado) {
        $valor = ($estado instanceof Estado) ? $estado->value : $estado;
        $types .= "s";
        $params[] = $valor;
      }
    }

    if ($clienteId !== null) {
      $condiciones[] = "cliente_id = ?";
      $types .= "i";
      $params[] = $clienteId;
    }

    if ($cocineroId !== null) {
      $condiciones[] = "cocinero_id = ?";
      $types .= "i";
      $params[] = $cocineroId;
    }

    if (count($condiciones) > 0) {
      $query .= " WHERE " . implode(" AND ", $condiciones);
    }

    $query .= " ORDER BY id ASC";

    $queryStmt = $conexion->prepare($query);
    if ($types !== "") {
      $bindParams = [$types];
      foreach ($params as $key => &$value) {
        $bindParams[] = &$value;
      }
      call_user_func_array([$queryStmt, 'bind_param'], $bindParams);
    }
    $queryStmt->execute();
    $resultado = $queryStmt->get_result();

    $pedidos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $pedidos[] = new Pedido(
        intval($fila['numero_pedido']),
        new DateTime($fila['fecha_creacion']),
        Estado::from($fila['estado']),
        Tipo::from($fila['tipo']),
        intval($fila['cliente_id']),
        $fila['cocinero_id'] !== null ? intval($fila['cocinero_id']) : null,
        floatval($fila['total']),
        intval($fila['id']),
        floatval($fila['descuento'])
      );
    }
    $resultado->free();
    $queryStmt->close();

    return $pedidos;
  }

  public static function getPedidoDesglosado(PedidoDesglosado $pedidoDesglosado)
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "SELECT p.id as producto_id, p.nombre, pp.precio_unitario, pp.cantidad, pp.preparado, pp.bistro_coineado
      FROM PedidoProducto pp
      JOIN Productos p ON pp.producto_id = p.id
      WHERE pp.pedido_id = ?"
    );
    $pedidoId = intval($pedidoDesglosado->getId());
    $query->bind_param("i", $pedidoId);
    $query->execute();
    $resultado = $query->get_result();

    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
      $productos[] = new ProductoEnPedido(
        intval($fila['producto_id']),
        $fila['nombre'],
        floatval($fila['precio_unitario']),
        intval($fila['cantidad']),
        boolval($fila['preparado']),
        boolval($fila['bistro_coineado'] ?? 0)
      );
    }
    $resultado->free();
    $query->close();

    $pedidoDesglosado->setProductos($productos);
  }

  public static function productoEnPedidoNecesitaPreparacion(int $pedido_id, int $productoId): bool
  {
    $conexion = Aplicacion::getInstance()->getConexionBd();

    $query = $conexion->prepare(
      "SELECT c.necesita_preparacion
      FROM Productos p
      JOIN Categorias c ON p.categoria_id = c.id
      JOIN PedidoProducto pp ON p.id = pp.producto_id
      WHERE pp.pedido_id = ? AND p.id = ?"
    );
    $query->bind_param("ii", $pedido_id, $productoId);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado) {
      if ($fila = $resultado->fetch_assoc()) {
        $resultado->free();
        $query->close();
        return boolval($fila['necesita_preparacion']);
      }
      $resultado->free();
    } 

    $query->close();

    return false;
  }
}
