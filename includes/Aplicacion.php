<?php

namespace es\ucm\fdi\aw;

use mysqli;

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

  //Funciones para control de accesos -> en vez de que cada vista consulte
  //$_SESSION['rolId'] directamente, se centraliza el control de acceso a través de funciones como esta.

  //No se puede importar user por dependencia circular entre config y aplicacion
  private const ROL_GERENTE = 1;
  private const ROL_COCINERO = 2;
  private const ROL_CAMARERO = 3;
  private const ROL_CLIENTE = 4;

  public static function puedeListarUsuarios()
  {
    return isset($_SESSION["rolId"]) && $_SESSION["rolId"] == self::ROL_GERENTE;
  }

  public static function estaLogueado(): bool
  {
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
  }

  public static function esGerente(): bool
  {
    return isset($_SESSION['rolId']) && $_SESSION['rolId'] === self::ROL_GERENTE;
  }

  public static function esCocinero(): bool
  {
    return isset($_SESSION['rolId']) && $_SESSION['rolId'] === self::ROL_COCINERO;
  }

  public static function esCamarero(): bool
  {
    return isset($_SESSION['rolId']) && $_SESSION['rolId'] === self::ROL_CAMARERO;
  }

  public static function esCliente(): bool
  {
    return isset($_SESSION['rolId']) && $_SESSION['rolId'] === self::ROL_CLIENTE;
  }

  public static function getRolId(): ?int
  {
    return $_SESSION['rolId'] ?? null;
  }

  public static function getUserId(): ?int
  {
    return $_SESSION['userId'] ?? null;
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

  //Método de inicialización de la aplicación, que se llama desde el gestor global antes de procesar cualquier petición.
  public function init($bdDatosConexion)
  {
    if (! $this->inicializada) {
      $this->bdDatosConexion = $bdDatosConexion;

      $this->inicializada = true;

      session_start();

      //Paso 8.1: Implementación de atributos de petición -> cargar los atributos de petición desde la sesión, si existen
      $this->atributosPeticion = $_SESSION[self::ATRIBUTOS_PETICION] ?? []; 
      unset($_SESSION[self::ATRIBUTOS_PETICION]); # limpiar los atributos de petición de la sesión para evitar que persistan entre peticiones
    }
  }

  private function compruebaInstanciaInicializada()
  {
    if (! $this->inicializada) {
      // Lanzar excepción en lugar de echo + exit
      // El gestor global la captura y muestra pçagina de error
      throw new \RuntimeException(
        'Aplicacion::init() no ha sido llamado antes de usar la instancia.'
      );
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
      /*
      Activar excepciones en ysqli antes de abrir la conexión.
      A partir de aquí cualquier error SQL lanza \mysqli_sql_exception
      No es necesario comprobar el valor de retorno de las query en las clase DB del proyecto.
      */
      $driver = new \mysqli_driver();
      $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

      $bdHost = $this->bdDatosConexion['host'];
      $bdUser = $this->bdDatosConexion['user'];
      $bdPass = $this->bdDatosConexion['pass'];
      $bd = $this->bdDatosConexion['bd'];

      $conn = new mysqli($bdHost, $bdUser, $bdPass, $bd);

      // Con MYSQLI_REPORT_STRICT, si la conexión falla se lanza
      // \mysqli_sql_exception antes de llegar aquí, por lo que
      // ya no necesitamos comprobar connect_errno manualmente.

      // Forzar codificación UTF-8 en la conexión
      $conn->set_charset('utf8mb4');

      $this->conn = $conn;
    }

    return $this->conn;
  }

  //Paso 8.2: Implementación de atributos de petición -> funciones para almacenar y recuperar datos asociados a la petición actual
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
