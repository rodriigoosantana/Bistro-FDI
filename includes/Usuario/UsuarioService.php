<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioDB.php';
require_once RAIZ_APP . '/includes/Usuario/RolesUsuario.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';

class UsuarioService
{
  public static function insertar(Usuario $usuario)
  {
    $usuario = UsuarioDB::insertar($usuario);
    return $usuario;
  }
  public static function actualizar(Usuario $usuario, $idRol)
  {
    $usuario = UsuarioDB::actualizar($usuario);
    Rol::cambiarRol($usuario->getId(), $idRol);
    return $usuario;
  }

  public static function eliminar(Usuario $usuario)
  {
    self::eliminarRoles($usuario);
    UsuarioDB::eliminar($usuario);
  }
  public static function buscarPorNombre($nombreUsuario)
  {
    return UsuarioDB::buscarPorNombre($nombreUsuario);
  }
  public static function listarTodos()
  {
    return UsuarioDB::listarTodos();
  }
  public static function login($nombreUsuario, $password)
  {
    $usuario = self::buscarPorNombre($nombreUsuario);

    if ($usuario && self::compruebaPassword($usuario, $password)) {
      return $usuario;
    }
    return false;
  }
  private static function compruebaPassword($usuario, $password)
  {
    return password_verify($password, $usuario->getPassword());
  }
  public static function hashPassword($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }
  private static function eliminarRoles($usuario)
  {
    $rolUsuario = new RolesUsuario($usuario->getId(), Rol::cargarRol($usuario->getId())->getId());
    if (!$rolUsuario->eliminar()) {
      return false;
    }
  }
  public static function insertarRoles($usuario, $idRol)
  {
    $rolUsuario = new RolesUsuario($usuario->getId(), $idRol);

    if (!$rolUsuario->insertar()) {
      return false;
    }

    return true;
  }

  // Procesa un archivo y devuelve la ruta para la BD
  public static function procesarAvatar($avatar_file)
  {
    error_log('Avatar recibido: ' . print_r($avatar_file, true));
    $dir = RUTA_IMGS . '/uploads/avatares/';
    $dir = $_SERVER['DOCUMENT_ROOT'] . RUTA_IMGS . '/uploads/avatares/';

    $extension = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    $nombreArchivo = 'avatar_' . uniqid() . '.' . $extension;
    $rutaBD = '/img/uploads/avatares/' . $nombreArchivo;
    $rutaDestino = $dir . $nombreArchivo;
    if (!move_uploaded_file($avatar_file['tmp_name'], $rutaDestino)) {
      error_log("Error al mover archivo subido: " . $avatar_file['name']);
    }
    return $rutaBD;
  }

  // Coge una ruta y devuelve el archivo en si 
  public static function cargarAvatar($avatar) {}
}
