<?php
require_once RAIZ_APP . '/includes/Usuario/RolesUsuario.php';
require_once RAIZ_APP . '/includes/Usuario/Rol.php';
class Usuario
{
  //region Campos privados
  private $id;
  private $nombreUsuario;
  private $password;
  private $nombre;
  private $apellidos;
  private $email;
  private $avatar;
  //endregion

  //region Campos estaticos 
  public const ROL_GERENTE = 1;
  public const ROL_COCINERO = 2;
  public const ROL_CAMARERO = 3;
  public const ROL_CLIENTE = 4;

  //endregion

  //region Constructor

  public function __construct($nombreUsuario, $password, $nombre, $apellidos, $email, $avatar, $id = null)
  {
    $this->id = $id;
    $this->nombreUsuario = $nombreUsuario;
    $this->password = $password;
    $this->nombre = $nombre;
    $this->apellidos = $apellidos;
    $this->email = $email;
    $this->avatar = $avatar;
  }

  //endregion

  //region Propiedades

  public function getId()
  {
    return $this->id;
  }

  public function getNombreUsuario()
  {
    return $this->nombreUsuario;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getEmail()
  {
    return $this->email;
  }
  public function getApellidos()
  {
    return $this->apellidos;
  }
  public function getAvatar()
  {
    return $this->avatar;
  }
  public function getPassword()
  {
    return $this->password;
  }

  public function setId($id)
  {
    $this->id = $id;
  }
  public function setNombreUsuario($nombreUsuario)
  {
    $this->nombreUsuario = $nombreUsuario;
  }
  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function setApellidos($apellidos)
  {
    $this->apellidos = $apellidos;
  }
  public function setAvatar($avatar)
  {
    $this->avatar = $avatar;
  }
  public function setPassword($password)
  {
    $this->password = $password;
  }
}
