<?php

require_once RAIZ_APP . '/includes/Categoria/Categoria.php';

class Producto
{
   //region Campos privados
   private $id;
   private $nombre;
   private $descripcion;
   private $categoriaId;
   private $precioBase;
   private $iva;
   private $disponible;
   private $ofertado;
   private $activo;
   //endregion

   //region Constructor
   private function __construct($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id = null)
   {
      $this->id = $id;
      $this->nombre = $nombre;
      $this->descripcion = $descripcion;
      $this->categoriaId = $categoriaId;
      $this->precioBase = $precioBase;
      $this->iva = $iva;
      $this->disponible = $disponible;
      $this->ofertado = $ofertado;
      $this->activo = $activo;
   }
   //endregion

   //region Propiedades
   public function getId()
   {
      return $this->id;
   }

   public function getNombre()
   {
      return $this->nombre;
   }

   public function getDescripcion()
   {
      return $this->descripcion;
   }

   public function getCategoriaId()
   {
      return $this->categoriaId;
   }

   public function getPrecioBase()
   {
      return $this->precioBase;
   }

   public function getPrecioFinal()
   {
      return $this->precioBase * (1 + $this->iva / 100);
   }

   public function getIva()
   {
      return $this->iva;
   }

   public function isDisponible()
   {
      return (bool) $this->disponible;
   }

   public function isOfertado()
   {
      return (bool) $this->ofertado;
   }

   public function isActivo()
   {
      return (bool) $this->activo;
   }
   //endregion

   //region Métodos privados
   private function insertar()
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "INSERT INTO producto (nombre, descripcion, categoria_id, precio_base, iva, disponible, ofertado, activo) 
       VALUES ('%s', '%s', %d, %f, %f, %d, %d, %d)",
         $conexion->real_escape_string($this->nombre),
         $conexion->real_escape_string($this->descripcion),
         intval($this->categoriaId),
         floatval($this->precioBase),
         floatval($this->iva),
         $this->disponible ? 1 : 0,
         $this->ofertado ? 1 : 0,
         $this->activo ? 1 : 0
      );

      if ($conexion->query($query) == TRUE) {
         $this->id = $conexion->insert_id;
         return true;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   private function actualizar()
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "UPDATE Productos
             SET nombre='%s', descripcion='%s', categoria_id=%d,
                 precio_base=%.2f, iva=%d, disponible=%d, ofertado=%d, activo=%d
             WHERE id=%d",
         $conexion->real_escape_string($this->nombre),
         $conexion->real_escape_string($this->descripcion),
         intval($this->categoriaId),
         floatval($this->precioBase),
         intval($this->iva),
         $this->disponible ? 1 : 0,
         $this->ofertado ? 1 : 0,
         $this->activo ? 1 : 0,
         intval($this->id)
      );

      if ($conexion->query($query)) {
         return true;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   //endregion

   //region Métodos públicos
   public function guardar()
   {
      if ($this->id === null) {
         return $this->insertar();
      } else {
         return $this->actualizar();
      }
   }
   //endregion

   //region Métodos estáticos
   public static function crear($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo)
   {
      $producto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
      if ($producto->insertar()) {
         return $producto;
      } else {
         return null;
      }
   }

   public static function buscarPorId($id)
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "SELECT * FROM producto WHERE id=%d",
         intval($id)
      );

      $resultado = $conexion->query($query);

      if ($resultado) {
         $fila = $resultado->fetch_assoc();
         #fetch_assoc devuelve un array asociativo con los datos de la fila o null si no hay más filas
         $resultado->free(); #free libera la memoria asociada al resultado

         if ($fila) {
            return new Producto(
               $fila['nombre'],
               $fila['descripcion'],
               intval($fila['categoria_id']),
               floatval($fila['precio_base']),
               intval($fila['iva']),
               (bool) $fila['disponible'],
               (bool) $fila['ofertado'],
               (bool) $fila['activo'],
               intval($fila['id'])
            );
         }
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   public static function listarTodos()
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = "SELECT * FROM producto ORDER BY nombre ASC";

      $resultado = $conexion->query($query);

      $productos = [];

      if ($resultado) {
         while ($fila = $resultado->fetch_assoc()) {
            $productos[] = new Producto(
               $fila['nombre'],
               $fila['descripcion'],
               intval($fila['categoria_id']),
               floatval($fila['precio_base']),
               intval($fila['iva']),
               (bool) $fila['disponible'],
               (bool) $fila['ofertado'],
               (bool) $fila['activo'],
               intval($fila['id'])
            );
         }
         $resultado->free();
         return $productos;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   public static function listarPorCategoria($categoriaId)
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "SELECT * FROM producto WHERE categoria_id=%d ORDER BY nombre ASC",
         intval($categoriaId)
      );

      $resultado = $conexion->query($query);

      $productos = [];

      if ($resultado) {
         while ($fila = $resultado->fetch_assoc()) {
            $productos[] = new Producto(
               $fila['nombre'],
               $fila['descripcion'],
               intval($fila['categoria_id']),
               floatval($fila['precio_base']),
               intval($fila['iva']),
               (bool) $fila['disponible'],
               (bool) $fila['ofertado'],
               (bool) $fila['activo'],
               intval($fila['id'])
            );
         }
         $resultado->free();
         return $productos;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   public static function cambiarDisponibilidad($id, $disponible)
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "UPDATE producto SET disponible=%d WHERE id=%d",
         $disponible ? 1 : 0,
         intval($id)
      );

      if ($conexion->query($query)) {
         return true;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }

   public static function cambiarEstado($id, $activo)
   {
      $conexion = Aplicacion::getInstance()->getConexionBd();

      $query = sprintf(
         "UPDATE producto SET activo=%d WHERE id=%d",
         $activo ? 1 : 0,
         intval($id)
      );

      if ($conexion->query($query)) {
         return true;
      } else {
         error_log("Error BD ({$conexion->errno}): {$conexion->error}");
         return false;
      }
   }
   //endregion
}
?>