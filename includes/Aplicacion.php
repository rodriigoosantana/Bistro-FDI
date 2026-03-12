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
