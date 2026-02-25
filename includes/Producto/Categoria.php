<?php

class Categoria
{
    //region Campos privados
    private $id;
    private $nombre;
    private $descripcion;
    private $imagen;
    private $activa;
    //endregion

    //region Constructor
    private function __construct($nombre, $descripcion, $imagen, $activa, $id = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->activa = $activa;
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

    public function getImagen()
    {
        return $this->imagen;
    }

    public function isActiva()
    {
        return (bool) $this->activa;
    }
    //endregion

    //region Métodos privados
    private function insertar()
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = sprintf(
            "INSERT INTO Categorias (nombre, descripcion, imagen, activa)
            VALUES ('%s', '%s', '%s', %d)",
            $conexion->real_escape_string($this->nombre),
            $conexion->real_escape_string($this->descripcion),
            $conexion->real_escape_string($this->imagen),
            $this->activa ? 1 : 0
        );

        if ($conexion->query($query)) {
            $this->id = $conexion->insert_id; # Asignar el ID generado por la base de datos
            return true;
        } else {
            error_log("Error al insertar categoría: " . $conexion->error);
            return false;
        }
    }

    private function actualizar()
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = sprintf(
            "UPDATE Categorias SET nombre='%s', descripcion='%s', imagen='%s', activa=%d WHERE id=%d",
            $conexion->real_escape_string($this->nombre),
            $conexion->real_escape_string($this->descripcion),
            $conexion->real_escape_string($this->imagen),
            $this->activa ? 1 : 0,
            intval($this->id)
        );

        if ($conexion->query($query)) {
            return true;
        } else {
            error_log("Error al actualizar categoría: " . $conexion->error);
            return false;
        }
    }
    //endregion

    //region Métodos públicos
    public function guardar()
    {
        if ($this->id) {
            return $this->actualizar();
        } else {
            return $this->insertar();
        }
    }
    //endregion

    //region Métodos estáticos
    public static function crear($nombre, $descripcion, $imagen, $activa)
    {
        $categoria = new Categoria($nombre, $descripcion, $imagen, $activa);

        if ($categoria->insertar()) {
            return $categoria;
        }
        return false;
    }
    public static function buscarPorId($id)
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = sprintf(
            "SELECT * FROM Categorias WHERE id=%d",
            intval($id)
        );

        $resultado = $conexion->query($query);

        if ($resultado) {
            $fila = $resultado->fetch_assoc();
            $resultado->free();

            if ($fila) {
                return new Categoria(
                    $fila['nombre'],
                    $fila['descripcion'],
                    $fila['imagen'],
                    (bool) $fila['activa'],
                    $fila['id']
                );
            } else {
                error_log("No se encontró la categoría con ID: " . $id);
                return false;
            }
        }
    }

    public static function listarTodas()
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = "SELECT * FROM Categorias ORDER BY nombre ASC";

        $resultado = $conexion->query($query);

        if ($resultado) {
            $categorias = [];

            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = new Categoria(
                    $fila['nombre'],
                    $fila['descripcion'],
                    $fila['imagen'],
                    (bool) $fila['activa'],
                    $fila['id']
                );
            }
            $resultado->free();
            return $categorias;
        } else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
            return false;
        }
    }
}

?>