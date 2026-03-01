<?php 

require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioDB.php';
require_once RAIZ_APP . '/includes/Usuario/RolesUsuario.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';

class UsuarioService {
  public static function insertar(Usuario $usuario) {
    $usuario = UsuarioDB::insertar($usuario);
    return $usuario;
  }
  
  public static function actualizar(Usuario $usuario) {
    $usuario = UsuarioDB::actualizar($usuario);
    self::actualizarRoles($usuario);
    return $usuario;
  }
  /*
  public static function eliminar(Usuario $usuario) {
    return UsuarioDB::eliminar($usuario);
  }
  public static function mostrar(Usuario $usuario) {
    return UsuarioDB::mostrar($usuario);
  }
  */
  public static function buscarPorNombre($nombreUsuario) {
    return UsuarioDB::buscarPorNombre($nombreUsuario);
  }
  public static function listarTodos() {
    return UsuarioDB::listarTodos();
  }
  public static function login($nombreUsuario, $password) {
    $usuario = self::buscarPorNombre($nombreUsuario);

    if ($usuario && self::compruebaPassword($usuario, $password)) {
       return $usuario;
    }
    return false;
  }
  private static function compruebaPassword($usuario, $password) {
    return password_verify($password, $usuario->getPassword());
  }
  public static function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
  }
  private static function eliminarRoles($usuario) {
    $rolUsuario = new RolesUsuario($usuario->getId(), Rol::cargarRol($usuario->getId())->getId());
     if (!$rolUsuario->eliminar()) {
       return false;
     }
  }
  public static function insertarRoles($usuario, $idRol) {
    $rolUsuario = new RolesUsuario($usuario->getId(), $idRol);

    if (!$rolUsuario->insertar()) {
       return false;
    }

    return true;
  }
  private static function actualizarRoles($usuario) {
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
}
?>
