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

    $query = sprintf(
      "INSERT INTO Usuarios(nombreUsuario, nombre, apellidos, email, avatar, password) 
         VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
      $conn->real_escape_string($usuario->getNombreUsuario()),
      $conn->real_escape_string($usuario->getNombre()),
      $conn->real_escape_string($usuario->getApellidos()),
      $conn->real_escape_string($usuario->getEmail()),
      $conn->real_escape_string($usuario->getAvatar()),
      $conn->real_escape_string($usuario->getPassword())
    );

    try { #Intentamos insertar el usuario, si el nombre de usuario ya existe, se lanzará una excepción que capturaremos para lanzar una excepción de dominio más específica.
      $conn->query($query);
      $usuario->setId($conn->insert_id);
      return $usuario;
    } catch (\mysqli_sql_exception $e) { #Capturamos la excepción de MySQLi para verificar si se debe a una violación de clave única (nombre de usuario ya existente).
      if ($conn->sqlstate === '23000') { #Código de error SQLSTATE para violación de restricción de clave única, lo que indica que el nombre de usuario ya existe.
        throw new UsuarioYaExisteException($usuario->getNombreUsuario()); #Lanzamos una excepción de dominio específica para indicar que el nombre de usuario ya está en uso.
      }
      throw $e; #Si es otro tipo de error, lo relanzamos para que sea manejado por un nivel superior.
    }
  }

  public static function eliminar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "DELETE FROM Usuarios WHERE id = '%s'",
      $usuario->getId()
    );

    $conn->query($query);
  }

  public static function actualizar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "UPDATE Usuarios
      SET nombreUsuario = '%s',
      nombre = '%s',
      apellidos = '%s',
      email = '%s',
      avatar = '%s'
      WHERE id = %d",
      $usuario->getNombreUsuario(),
      $usuario->getNombre(),
      $usuario->getApellidos(),
      $usuario->getEmail(),
      $usuario->getAvatar(),
      $usuario->getId()
    );

    $conn->query($query);

    return $usuario;
  }

  public static function listarTodos()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Usuarios");

    $rs = $conn->query($query);

    $usuarios = [];
    while ($fila = $rs->fetch_assoc()) {
      $usuarios[] = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila["id"]);
    }
    $rs->free();

    return $usuarios;
  }


  public static function buscarPorNombre($nombreUsuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Usuarios U WHERE U.nombreUsuario='%s'", $conn->real_escape_string($nombreUsuario));

    $rs = $conn->query($query);

    $fila = $rs->fetch_assoc();
    $rs->free();
    if ($fila) {
      Rol::cargarRol($fila['id']);
      $user = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila['id']);
      return $user;
    }

    return null;
  }
}
