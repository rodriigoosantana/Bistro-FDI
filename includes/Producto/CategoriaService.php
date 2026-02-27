<?php
require_once RAIZ_APP . '/includes/Producto/Categoria.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaDB.php';

class CategoriaService
{

    public static function crear($nombre, $descripcion, $imagen, $activa)
    {
        $categoria = new Categoria($nombre, $descripcion, $imagen, $activa);
        return CategoriaDB::insertar($categoria);
        #devuelve el DTO de la categoría con id asignado si se inserta correctamente, null si falla
    }

    public static function actualizar($id, $nombre, $descripcion, $imagen, $activa)
    {
        $categoria = new Categoria($nombre, $descripcion, $imagen, $activa);
        return CategoriaDB::actualizar($categoria);
        #devuelve true si se actualiza correctamente, false si falla
    }

    public static function buscarPorId($id)
    {
        return CategoriaDB::buscarPorId($id);
        #devuelve el DTO de la categoría con el id indicado, o null si no existe
    }

    public static function listarTodas()
    {
        return CategoriaDB::listarTodas();
        #devuelve array de categorías, o array vacío si no hay categorías
    }

    public static function cambiarEstado($id, $activa)
    {
        #id de la categoría, nuevo estado activa/inactiva
        return CategoriaDB::cambiarEstado($id, $activa);
        #devuelve true si se actualiza correctamente, false si falla
    }
}


?>