<?php
class Aplicacion
{
  const ATRIBUTOS_PETICION = 'attsPeticion';

  private static $instancia;

  public static function getInstance()
  {
    if (!self::$instancia instanceof self) {
      self::$instancia = new static();
    }

    return self::$instancia;
  }

  //Funciones para control de accesos.

  //No se puede importar user por dependencia circular entre config y aplicacion
  private const ROL_GERENTE = 1;
  private const ROL_COCINERO = 2;
  private const ROL_CAMARERO = 3;
  private const ROL_CLIENTE = 4;

  public static function puedeListarUsuarios()
  {
    return isset($_SESSION["rolId"]) && $_SESSION["rolId"] == self::ROL_GERENTE;
  }


  /**
   * @var array Almacena los datos de configuración de la BD
   */
  private $bdDatosConexion;

  private $inicializada = false;

  /**
   * @var \mysqli Conexión de BD.
   */
  private $conn;


  private $atributosPeticion;


  private function __construct() {}

  public function init($bdDatosConexion)
  {
    if (! $this->inicializada) {
      $this->bdDatosConexion = $bdDatosConexion;

      $this->inicializada = true;

      session_start();

      $this->atributosPeticion = $_SESSION[self::ATRIBUTOS_PETICION] ?? [];

      unset($_SESSION[self::ATRIBUTOS_PETICION]);
    }
  }

  private function compruebaInstanciaInicializada()
  {
    if (! $this->inicializada) {
      echo "Aplicacion no inicializa";

      exit();
    }
  }

  public function shutdown()
  {
    $this->compruebaInstanciaInicializada();

    if ($this->conn !== null && ! $this->conn->connect_errno) {
      $this->conn->close();
    }
  }

  public function getConexionBd()
  {
    $this->compruebaInstanciaInicializada();

    if (! $this->conn) {
      $bdHost = $this->bdDatosConexion['host'];
      $bdUser = $this->bdDatosConexion['user'];
      $bdPass = $this->bdDatosConexion['pass'];
      $bd = $this->bdDatosConexion['bd'];

      $conn = new mysqli($bdHost, $bdUser, $bdPass, $bd);

      if ($conn->connect_errno) {
        echo "Error de conexión a la BD ({$conn->connect_errno}):  {$conn->connect_error}";
        exit();
      }

      if (! $conn->set_charset("utf8mb4")) {
        echo "Error al configurar la BD ({$conn->errno}):  {$conn->error}";
        exit();
      }

      $this->conn = $conn;
    }

    return $this->conn;
  }

  public function putAtributoPeticion($clave, $valor)
  {
    $atts = null;

    if (isset($_SESSION[self::ATRIBUTOS_PETICION])) {
      $atts = &$_SESSION[self::ATRIBUTOS_PETICION];
    } else {
      $atts = array();

      $_SESSION[self::ATRIBUTOS_PETICION] = &$atts;
    }

    $atts[$clave] = $valor;
  }

  public function getAtributoPeticion($clave)
  {
    $result = $this->atributosPeticion[$clave] ?? null;

    if (is_null($result) && isset($_SESSION[self::ATRIBUTOS_PETICION])) {
      $result = $_SESSION[self::ATRIBUTOS_PETICION][$clave] ?? null;
    }

    return $result;
  }
}
