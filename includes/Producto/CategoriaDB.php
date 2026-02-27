<?php

require_once RAIZ_APP . '/includes/Producto/Categoria.php';

class CategoriaDB
{
    //Inserta una nueva categoría en la base de datos.
    public static function insertar(Categoria $categoria)
    {
        // Obtener la conexión a la base de datos
        $conexion = Aplicacion::getInstance()->getConexionBd();

        //Construcción de la query (petición SQL)
        $query = sprintf(
            "INSERT INTO Categorias (nombre, descripcion, imagen, activa)
            VALUES ('%s', '%s', '%s', %d)",
            $conexion->real_escape_string($categoria->getNombre()),
            $conexion->real_escape_string($categoria->getDescripcion()),
            $conexion->real_escape_string($categoria->getImagen()),
            $categoria->isActiva() ? 1 : 0
        );

        //Eejecutar la query y manejar el resultado
        if ($conexion->query($query) == TRUE) {
            $categoria->setId($conexion->insert_id); # Asignar el ID generado por la base de datos
            return $categoria;
        }
        else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
            return null;
        }
    }

    //Actualiza una categoría existente en la base de datos.
    public static function actualizar(Categoria $categoria)
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = sprintf(
            "UPDATE Categorias
             SET nombre='%s', descripcion='%s', imagen='%s', activa=%d
             WHERE id=%d",
            $conexion->real_escape_string($categoria->getNombre()),
            $conexion->real_escape_string($categoria->getDescripcion()),
            $conexion->real_escape_string($categoria->getImagen()),
            $categoria->isActiva() ? 1 : 0,
            intval($categoria->getId())
        );

        if ($conexion->query($query) == TRUE) {
            return true;
        }
        else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
            return false;
        }
    }

    // Busca una categoría por su ID
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
                    (bool)$fila['activa'],
                    intval($fila['id'])
                );
            }
        } else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
        }

        return null;
    }

    // Lista todas las categorías ordenadas por nombre
    public static function listarTodas()
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = "SELECT * FROM Categorias ORDER BY nombre ASC";

        $resultado = $conexion->query($query);

        $categorias = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {  //Recorremos cada fila del resultado y creamos un objeto Categoria para cada una
                $categorias[] = new Categoria(
                    $fila['nombre'],
                    $fila['descripcion'],
                    $fila['imagen'],
                    (bool)$fila['activa'],
                    intval($fila['id'])
                );
            }
            $resultado->free();  //Liberamos el resultado para liberar memoria
        } else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
        }

        return $categorias;
    }

    // Cambia el estado activa/inactiva de una categoría
    public static function cambiarEstado($id, $activa)
    {
        $conexion = Aplicacion::getInstance()->getConexionBd();

        $query = sprintf(
            "UPDATE Categorias SET activa=%d WHERE id=%d",
            $activa ? 1 : 0,
            intval($id)
        );

        if ($conexion->query($query)) {
            return true;
        } else {
            error_log("Error BD ({$conexion->errno}): {$conexion->error}");
            return false;
        }
    }
}

?>