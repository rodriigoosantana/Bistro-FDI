<?php 

require_once RAIZ_APP . '/includes/Usuario/Usuario.php';
require_once RAIZ_APP . '/includes/Usuario/UsuarioDB.php';
require_once RAIZ_APP . '/includes/Usuario/RolesUsuario.php';

class UsuarioService {
  public static function insertar(Usuario $usuario) {
    $usuario = UsuarioDB::insertar($usuario);
    self::insertarRoles($usuario);
    return $usuario;
  }
  /*
  public static function actualizar(Usuario $usuario) {
    return UsuarioDB::actualizar($usuario);
  }
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
  private static function insertarRoles($usuario) {
    $rolUsuario = new RolesUsuario($usuario->getId(), $usuario->getRol());

    if (!$rolUsuario->insertar()) {
       return false;
    }

    return true;
  }
}
?>
