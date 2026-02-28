<?php

require_once RAIZ_APP . '/includes/Producto/Producto.php';
require_once RAIZ_APP . '/includes/Producto/ProductoDB.php';

//Clase ProductoService (Lógica de negocio)
//Capa intermedia 


class ProductoService
{
    public static function crear($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo)
    {
        $producto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
        return ProductoDB::insertar($producto);
        #devuelve el DTO del producto con id asignado si se inserta correctamente, null si falla
    }


    public static function actualizar( $id, $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo)
    {
        $producto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id);
        return ProductoDB::actualizar($producto);
        #devuelve true si se actualiza correctamente, false si falla
    }

    public static function buscarPorId($id)
    {
        return ProductoDB::buscarPorId($id);
        #devuelve el DTO del producto con el id indicado, o null si no existe
    }

    public static function listarTodos()
    {
        return ProductoDB::listarTodos();
        #devuelve array de productos, o array vacío si no hay productos
    }

    public static function listarPorCategoria($categoriaId)
    {
        return ProductoDB::listarPorCategoria($categoriaId);
        #devuelve array de productos que pertenecen a la categoría indicada, o array vacío 
    }


    public static function cambiarDisponibilidad($id, $disponible)
    {
        #id del producto, nuevo valor de disponibilidad
        return ProductoDB::cambiarDisponibilidad($id, $disponible);
        #devuelve true si se actualiza correctamente, false si falla
    }

    public static function cambiarEstado($id, $activo)
    {
        #id del producto, nuevo estado activo/inactivo
        return ProductoDB::cambiarEstado($id, $activo);
        #devuelve true si se actualiza correctamente, false si falla
    }
}
?>