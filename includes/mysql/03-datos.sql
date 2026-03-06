/*
  Contraseñas de prueba:
    gerente1  -> password
    cocinero1 -> password
    camarero1 -> password
    cliente1  -> password
    cliente2  -> password
*/
USE `bistro_fdi`;

TRUNCATE TABLE `PedidoProducto`;
TRUNCATE TABLE `ProductoImagen`;
TRUNCATE TABLE `Pedidos`;
TRUNCATE TABLE `EstadosPedido`;
TRUNCATE TABLE `Productos`;
TRUNCATE TABLE `Categorias`;
TRUNCATE TABLE `RolesUsuario`;
TRUNCATE TABLE `Usuarios`;
TRUNCATE TABLE `Roles`;

-- ROLES (prioridad: 1=máximo acceso)
INSERT INTO `Roles` (`id`, `nombre`, `prioridad`) VALUES
(1, 'gerente',  1),
(2, 'cocinero', 2),
(3, 'camarero', 3),
(4, 'cliente',  4);

-- USUARIOS
-- password -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `Usuarios` (`id`, `nombreUsuario`, `email`, `nombre`, `apellidos`, `password`) VALUES
(1, 'gerente1',  'gerente@bistrofdi.es',  'Diego Pablo',    'Simeone',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'cocinero1', 'cocinero@bistrofdi.es', 'Carmen', 'Berzatto',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3, 'camarero1', 'camarero@bistrofdi.es', 'Sydney',  'Adamu',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(4, 'cliente1',  'cliente1@gmail.com',    'Koke',  'Resurrección', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(5, 'cliente2',  'cliente2@gmail.com',    'Antoine',  'Griezmann',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ROLES USUARIO
INSERT INTO `RolesUsuario` (`usuario`, `rol`) VALUES
(1, 1),  -- gerente1  -> gerente
(2, 2),  -- cocinero1 -> cocinero
(3, 3),  -- camarero1 -> camarero
(4, 4),  -- cliente1  -> cliente
(5, 4);  -- cliente2  -> cliente

-- CATEGORIAS
INSERT INTO `Categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Bocadillos', 'Bocadillos frescos preparados al momento'),
(2, 'Bebidas',    'Refrescos, agua, zumos y bebidas calientes'),
(3, 'Menús',      'Menús completos con primero, segundo y postre'),
(4, 'Postres',    'Dulces y postres del día');

-- PRODUCTOS (IVA: 10% alimentación, 21% bebidas)
INSERT INTO `Productos` (`id`, `nombre`, `descripcion`, `categoria_id`, `precio_base`, `iva`) VALUES
(1, 'Bocadillo de jamón',     'Jamón ibérico con tomate y aceite', 1, 3.50, 10),
(2, 'Bocadillo de tortilla',  'Tortilla española con cebolla',      1, 3.00, 10),
(3, 'Bocadillo de calamares', 'Calamares fritos con alioli',        1, 4.00, 10),
(4, 'Agua mineral 500ml',     'Agua mineral natural',               2, 1.00, 21),
(5, 'Refresco lata',          'Pepsi, Fanta o Sprite',          2, 1.50, 21),
(6, 'Café solo',              'Café espresso',                      2, 1.20, 21),
(7, 'Menú del día',           'Primero, segundo, postre y bebida',  3, 8.50, 10),
(8, 'Yogur natural',          'Yogur natural con miel',             4, 1.50, 10),
(9, 'Fruta del día',          'Pieza de fruta de temporada',        4, 1.00, 10);

-- IMÁGENES DE PRODUCTOS (Por defecto, para que aparezca algo)
INSERT INTO `ProductoImagen` (`id`, `producto_id`, `ruta_imagen`) VALUES
(1, 4, '/img/seed/productos/producto_4_69a94d9a16216.jpg'),
(2, 3, '/img/seed/productos/producto_3_69a94dc0c35b5.jpg'),
(3, 1, '/img/seed/productos/producto_1_69a94ddcb011a.jpg'),
(4, 5, '/img/seed/productos/producto_5_69a94e670a738.jpg'),
(5, 5, '/img/seed/productos/producto_5_69a94e670bb78.jpg'),
(6, 5, '/img/seed/productos/producto_5_69a94e670d5db.jpg'),
(7, 5, '/img/seed/productos/producto_5_69a94e670e668.png');


-- ESTADOS DE PEDIDO
INSERT INTO `EstadosPedido` (`id`, `nombre`) VALUES
(1, 'nuevo'),
(2, 'recibido'),
(3, 'en_preparacion'),
(4, 'cocinando'),
(5, 'listo_cocina'),
(6, 'terminado'),
(7, 'entregado'),
(8, 'cancelado');
