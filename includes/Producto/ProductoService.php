<?php

require_once RAIZ_APP . '/includes/Producto/Producto.php';
require_once RAIZ_APP . '/includes/Producto/ProductoDB.php';
require_once RAIZ_APP . '/includes/Producto/ProductoImagenDB.php';

//Clase ProductoService (Lógica de negocio)
//Capa intermedia 
class ProductoService
{
    public static function crear($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, ?array $imagenes): ?Producto
    {
        $dto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
        $producto = ProductoDB::insertar($dto); #devuelve el DTO del producto con id asignado si se inserta correctamente, null si falla

        if ($producto && $imagenes) {
            self::guardarImagenes($producto->getId(), $imagenes);
        }

        return $producto ?? null;
    }


    public static function actualizar($id, $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, ?array $imagenes = null): bool
    {
        $producto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $id);

        $ok = ProductoDB::actualizar($producto);
        #devuelve true si se actualiza correctamente, false si falla

        if ($ok && $imagenes !== null) {
            #borrar imágenes anteriores
            $img_actuales = ProductoImagenDB::listarPorProducto($id);
            foreach ($imagenes as $img) {
                $ruta = RAIZ_APP . $img['ruta_imagen'];
                if (file_exists($ruta)) {
                    unlink($ruta); #elimina la imagen del servidor
                }
            }

            ProductoImagenDB::borrarPorProducto($id); #elimina registros de imágenes en BD

            self::guardarImagenes($id, $imagenes); #guarda nuevas imágenes en BD
        }

        return $ok;
    }

    // Elimina un producto y todas sus imágenes (BD y disco).
    // Devuelve true si se elimina correctamente, false si falla.
    public static function eliminar(int $id): bool
    {
        // Borrar imágenes del disco antes de eliminar el registro
        $imagenes = ProductoImagenDB::listarPorProducto($id);
        foreach ($imagenes as $img) {
            $rutaFisica = RAIZ_APP . $img['ruta_imagen'];
            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }
        ProductoImagenDB::borrarPorProducto($id);

        return ProductoDB::eliminar($id);
    }

    public static function listarImagenes(int $productoId): array
    {
        return ProductoImagenDB::listarPorProducto($productoId);
    }

    public static function buscarPorId($id): ?Producto
    {
        return ProductoDB::buscarPorId($id);
        #devuelve el DTO del producto con el id indicado, o null si no existe
    }

    public static function listarTodos(): array
    {
        return ProductoDB::listarTodos();
        #devuelve array de productos, o array vacío si no hay productos
    }

    public static function listarPorCategoria($categoriaId): array
    {
        return ProductoDB::listarPorCategoria($categoriaId);
        #devuelve array de productos que pertenecen a la categoría indicada, o array vacío 
    }


    public static function cambiarDisponibilidad($id, $disponible): bool
    {
        #id del producto, nuevo valor de disponibilidad
        return ProductoDB::cambiarDisponibilidad($id, $disponible);
        #devuelve true si se actualiza correctamente, false si falla
    }

    public static function cambiarEstado($id, $activo): bool
    {
        #id del producto, nuevo estado activo/inactivo
        return ProductoDB::cambiarEstado($id, $activo);
        #devuelve true si se actualiza correctamente, false si falla
    }

    private static function guardarImagenes(int $productoId, array $imagenes): void
    { # $imagenesSubidas es $_FILES['imagenes'] con múltiples ficheros.
        $dir = RAIZ_APP . '/img/uploads/productos/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true); #crea el directorio si no existe
        }

        $extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $total = count($imagenes['name']);

        for ($i = 0; $i < $total; $i++) {
            #validar error de subida
            if ($imagenes['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            #validar extensión
            $extension = strtolower(pathinfo($imagenes['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($extension, $extensiones)) {
                continue;
            }

            #generar nombre único
            $nombreArchivo = 'producto_' . $productoId . '_' . uniqid() . '.' . $extension;
            $rutaDestino = $dir . $nombreArchivo;
            $rutaBD = '/img/uploads/productos/' . $nombreArchivo; #ruta relativa para almacenar en BD

            if (move_uploaded_file($imagenes['tmp_name'][$i], $rutaDestino)) {
                ProductoImagenDB::insertar($productoId, $rutaBD); #guarda ruta en BD
            } else {
                error_log("Error al mover archivo subido: " . $imagenes['name'][$i]);
            }
        }
    }
}
?>