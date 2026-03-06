<?php
require_once RAIZ_APP . '/includes/Usuario/Rol.php';

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

    if ($conn->query($query)) {
      $usuario->setId($conn->insert_id);
      return $usuario;
    } else {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
      return null;
    }
  }

  public static function eliminar(Usuario $usuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf(
      "DELETE FROM Usuarios WHERE id = '%s'",
      $usuario->getId()
    );

    if (!$conn->query($query)) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }
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

    if (!$conn->query($query)) {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
      return null;
    }
    return $usuario;
  }
  public static function listarTodos()
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Usuarios");

    $rs = $conn->query($query);

    if ($rs) {
      $usuarios = [];
      while ($fila = $rs->fetch_assoc()) {
        $usuarios[] = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila["id"]);
      }
      $rs->free();
      return $usuarios;
    } else {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }

    return [];
  }


  public static function buscarPorNombre($nombreUsuario)
  {
    $conn = Aplicacion::getInstance()->getConexionBd();

    $query = sprintf("SELECT * FROM Usuarios U WHERE U.nombreUsuario='%s'", $conn->real_escape_string($nombreUsuario));

    $rs = $conn->query($query);

    if ($rs) {
      $fila = $rs->fetch_assoc();

      $rs->free();

      if ($fila) {
        Rol::cargarRol($fila['id']);
        $user = new Usuario($fila['nombreUsuario'], $fila['password'], $fila['nombre'], $fila['apellidos'], $fila['email'], $fila['avatar'], $fila['id']);

        return $user;
      }
    } else {
      error_log("Error BD ({$conn->errno}): {$conn->error}");
    }

    return null;
  }
}
