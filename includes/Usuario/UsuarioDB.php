<?php

namespace es\ucm\fdi\aw\Usuario;

use es\ucm\fdi\aw\Usuario\Rol;
use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Usuario\UsuarioYaExisteException; #Importamos la excepción de dominio específica 

class UsuarioDB
{
  public static function insertar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "INSERT INTO Usuarios(nombreUsuario, nombre, apellidos, email, avatar, password, saldo_bistrocoins) 
         VALUES (?, ?, ?, ?, ?, ?, ?)",
    );

    $nombreUsuario	= $usuario->getNombreUsuario();
    $nombre		= $usuario->getNombre();
    $apellidos		= $usuario->getApellidos();
    $email		= $usuario->getEmail();
    $avatar		= $usuario->getAvatar();
    $password		= $usuario->getPassword();
    $saldoBistroCoins	= $usuario->getSaldoBistrocoins();

    $query->bind_param("ssssssd", $nombreUsuario, $nombre, $apellidos, $email, $avatar, $password, $saldoBistroCoins);
    try { #Intentamos insertar el usuario, si el nombre de usuario ya existe, se lanzará una excepción que capturaremos para lanzar una excepción de dominio más específica.
      $query->execute();

      $usuario->setId($conn->insert_id);
      return $usuario;
    } catch (\mysqli_sql_exception $e) { #Capturamos la excepción de MySQLi para verificar si se debe a una violación de clave única (nombre de usuario ya existente).
      if ($conn->sqlstate === '23000') { #Código de error SQLSTATE para violación de restricción de clave única, lo que indica que el nombre de usuario ya existe.
        throw new UsuarioYaExisteException($usuario->getNombreUsuario()); #Lanzamos una excepción de dominio específica para indicar que el nombre de usuario ya está en uso.
      }
      throw $e; #Si es otro tipo de error, lo relanzamos para que sea manejado por un nivel superior.
    } finally {
      $query->close();
    }
  }

  public static function eliminar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "DELETE FROM Usuarios WHERE id = ?",
    );

    $idUsuario = $usuario->getId();
    $query->bind_param("i", $idUsuario);

    $query->execute();
    $query->close();
  }

  public static function actualizar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare(
      "UPDATE Usuarios
      SET nombreUsuario = ?,
      nombre = ?,
      apellidos = ?,
      email = ?,
      avatar = ?,
      saldo_bistrocoins = ?
      WHERE id = ?",
    );

    $nombreUsuario	= $usuario->getNombreUsuario();
    $nombre		= $usuario->getNombre();
    $apellidos		= $usuario->getApellidos();
    $email		= $usuario->getEmail();
    $avatar		= $usuario->getAvatar();
    $saldoBistroCoins	= $usuario->getSaldoBistrocoins();
    $id			= $usuario->getId();

    $query->bind_param("sssssdi", $nombreUsuario, $nombre, $apellidos, $email, $avatar, $saldoBistroCoins, $id);
    $query->execute();
    $query->close();

    return $usuario;
  }

  public static function listarTodos()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("SELECT * FROM Usuarios");
    $query->execute();
    $rs = $query->get_result();

    $usuarios = [];
    while ($fila = $rs->fetch_assoc()) {
      $usuarios[] = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila['saldo_bistrocoins'], $fila["id"]);
    }
    $rs->free();
    $query->close();

    return $usuarios;
  }


  public static function buscarPorNombre($nombreUsuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = $conn->prepare("SELECT * FROM Usuarios U WHERE U.nombreUsuario=?");
    $query->bind_param("s", $nombreUsuario);
    $query->execute();
    $rs = $query->get_result();

    $fila = $rs->fetch_assoc();
    $rs->free();
    $query->close();
    if ($fila) {
      Rol::cargarRol($fila['id']);
      $user = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila['saldo_bistrocoins'], $fila['id']);
      return $user;
    }

    return null;
  }

  public static function buscarPorId(int $id): ?Usuario
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Usuarios U WHERE U.id=%d", intval($id));

    $rs = $conn->query($query);

    $fila = $rs ? $rs->fetch_assoc() : null;
    if ($rs) {
      $rs->free();
    }

    if ($fila) {
      Rol::cargarRol($fila['id']);
      return new Usuario(
        $fila['nombreUsuario'],
        $fila['password'],
        $fila['nombre'],
        $fila['apellidos'],
        $fila['email'],
        $fila['avatar'],
        $fila['saldo_bistrocoins'],
        $fila['id']
      );
    }

    return null;
  }

  public static function actualizarSaldoBistrocoins(int $id, int $saldo): bool
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Usuarios SET saldo_bistrocoins = %d WHERE id = %d",
      intval($saldo),
      intval($id)
    );

    return (bool) $conn->query($query);
  }
}
