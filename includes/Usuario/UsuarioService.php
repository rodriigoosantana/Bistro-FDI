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

  public static function actualizar(Usuario $usuario)
  {
    $usuario = UsuarioDB::actualizar($usuario);
    self::actualizarRoles($usuario);
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
  private static function actualizarRoles($usuario)
  {
    $rol = Rol::cargarRol($usuario->getId());

    if (!$rol) {
      // Manejar caso: usuario sin rol
      error_log("Usuario {$usuario->getId()} no tiene rol asignado");
      return false; // o lanzar excepción
    }

    $rolUsuario = new RolesUsuario($usuario->getId(), $rol->getId());
    $rolUsuario->eliminar();
    $rolUsuario->insertar();
  }

  // Procesa un archivo y devuelve la ruta para la BD
  public static function procesarAvatar($usuario_id, $avatar_file)
  {
    error_log('Avatar recibido: ' . print_r($avatar_file, true));
    $dir = RUTA_IMGS . '/seed/avatares/';

    if (!is_dir($dir)) {
      mkdir($dir, 0755, true); #crea el directorio si no existe
    }
    $extension = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
    $nombreArchivo = 'avatar_' . $usuario_id . '_' . uniqid() . '.' . $extension;
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
