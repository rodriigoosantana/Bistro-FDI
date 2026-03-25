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
TRUNCATE TABLE `Productos`;
TRUNCATE TABLE `Categorias`;
TRUNCATE TABLE `RolesUsuario`;
TRUNCATE TABLE `Usuarios`;
TRUNCATE TABLE `Roles`;

-- ROLES (prioridad: 1=máximo acceso)
INSERT INTO `Roles` (`id`, `nombre`) VALUES
(1, 'gerente'),
(2, 'cocinero'),
(3, 'camarero'),
(4, 'cliente');

-- USUARIOS
-- password -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `Usuarios` (`id`, `nombreUsuario`, `email`, `nombre`, `apellidos`, `password`, `avatar`, `activo`, `fecha_creacion`) VALUES
(1, 'gerente1', 'gerente@bistrofdi.es', 'Diego Pablo', 'Simeone', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/avatar_69aee9905917d.jpg', 1, '2026-03-09 16:38:09'),
(2, 'cocinero1', 'cocinero@bistrofdi.es', 'Carmen', 'Berzatto', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/avatar_69aeea65aa24e.webp', 1, '2026-03-09 16:38:09'),
(3, 'cocinera2', 'cocinera1@bistrofdi.es', 'Sydney', 'Adamu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/avatar_69aeea1fd1896.webp', 1, '2026-03-09 16:38:09'),
(4, 'camarero1', 'kokegoat@gmail.com', 'Koke', 'Resurrecci&oacute;n', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/avatar_69aeea882ac66.jpg', 1, '2026-03-09 16:38:09'),
(5, 'camarero2', 'cliente2@gmail.com', 'Antoine', 'Griezmann', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/avatar_69aeeae40e227.jpg', 1, '2026-03-09 16:38:09'),
(75, 'cliente1', 'cliente1@gmail.com', 'Big AKA &#039;Double B&#039;', 'Baut', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/default.jpg', 1, '2026-03-06 22:54:13'),
(76, 'cliente2', 'cliente2@gmail.com', 'Dudu', 'Tous', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '/img/uploads/avatares/default.jpg', 1, '2026-03-08 14:33:54'),
(77, '4444', '4444', '4444', '4444', '$2y$10$GW/QTB66w3YXpXyIw1fqMOUsMN7WvZSkKfk4sGgYLgSsZWnmD14EC', '/img/uploads/avatares/avatar_69adb436dea48.jpeg', 1, '2026-03-08 18:39:03'),
(78, '1212', '1212', '1122', '1212', '$2y$10$CSxv1OVR5POsCUz2znVIeezJmEtmI9HrKhQbM.SDVEHkXs/19LmUG', '/img/uploads/avatares/default.jpg', 1, '2026-03-08 18:45:22'),
(83, '3333', '3333', '3333', '3333', '$2y$10$yJoRHATJCDwjGBbPc6o5k.IHafa7bAkzc9RBOjPM.NhOIbbICB.1S', '/img/uploads/avatares/avatar_predeterminado_2.jpeg', 1, '2026-03-08 19:29:49'),
(84, '6666', '6666', '6666', '6666', '$2y$10$lnyvskkOjFF.ItFo8Tq5tODWm6SoWet8Y689IkTr/QIw3CgRdFY8i', '/img/uploads/avatares/avatar_predeterminado_1.jpeg', 1, '2026-03-08 19:37:31'),
(87, 'webo', 'webo', 'webo', 'webo', '$2y$10$DWVwlvw/Td7StEgcNbhgI.waziunMbCFDK8ZQirx4wOFtpK/3wwyu', '/img/uploads/avatares/avatar_69aed8afbcc1b.jpeg', 1, '2026-03-09 15:25:30');


-- ROLES USUARIO
INSERT INTO `RolesUsuario` (`usuario`, `rol`) VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 3),
(5, 3),
(75, 4),
(76, 4),
(77, 4),
(78, 4),
(83, 4),
(84, 4),
(87, 1);


-- CATEGORIAS
INSERT INTO `Categorias` (`id`, `nombre`, `descripcion`, `imagen`, `necesita_preparacion`) VALUES
(1, 'Bocadillos', 'Bocadillos frescos preparados al momento', '/img/uploads/categorias/categoria_1_69b3eb6edb67c.jpg', 1),
(2, 'Bebidas',    'Refrescos, agua, zumos y bebidas calientes', '/img/uploads/categorias/categoria_2_69b3eb49ad9de.jpg', 0),
(3, 'Menús',      'Menús completos con primero, segundo y postre', '/img/uploads/categorias/categoria_3_69b3eba556a97.png', 1),
(4, 'Postres',    'Dulces y postres del día', '/img/uploads/categorias/categoria_4_69afee1e55f4c.jpg', 1),
(5, 'Desayunos', 'Tostadas, bollería y desayunos completos', '/img/uploads/categorias/categoria_5_69afefdd1f8a5.jpg', 1);

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
(9, 'Fruta del día',          'Pieza de fruta de temporada',        4, 1.00, 10),
(10, 'Bocadillo de lomo',        'Lomo de cerdo con pimientos asados',            1, 3.80, 10),
(11, 'Bocadillo de queso',       'Queso manchego con tomate natural',              1, 3.00, 10),
(12, 'Bocadillo vegetal',        'Lechuga, tomate, zanahoria y mayonesa',          1, 2.80, 10),
(13, 'Bocadillo de atún',        'Atún en aceite con cebolla y pimiento',          1, 3.20, 10),
(14, 'Café con leche',           'Café espresso con leche vaporizada',             2, 1.40, 21),
(15, 'Cortado',                  'Café espresso con un toque de leche',            2, 1.30, 21),
(16, 'Zumo de naranja natural',  'Zumo exprimido al momento',                      2, 2.00, 21),
(17, 'Infusión',                 'Manzanilla, poleo o té',                         2, 1.20, 21),
(18, 'Refresco botella 1L',      'Coca-Cola, Fanta o Aquarius',                    2, 2.50, 21),
(19, 'Menú universitario',       'Plato combinado, pan, bebida y postre',          3, 6.50, 10),
(20, 'Menú vegano',              'Primero y segundo veganos, postre y agua',       3, 7.50, 10),
(21, 'Flan casero',              'Flan de huevo con caramelo',                     4, 1.80, 10),
(22, 'Tarta del día',            'Tarta casera según disponibilidad',              4, 2.50, 10),
(23, 'Arroz con leche',          'Arroz con leche espolvoreado con canela',        4, 1.80, 10),
(24, 'Tostada con aceite',       'Pan tostado con aceite de oliva virgen extra',   5, 1.20, 10),
(25, 'Tostada con tomate',       'Pan tostado con tomate rallado y aceite',        5, 1.30, 10),
(26, 'Tostada aceite y tomate',  'Pan tostado con tomate y aceite de oliva',       5, 1.30, 10),
(27, 'Tostada con mantequilla',  'Pan tostado con mantequilla y mermelada',        5, 1.40, 10),
(28, 'Croissant',                'Croissant de mantequilla recién horneado',       5, 1.50, 10),
(29, 'Napolitana de chocolate',  'Hojaldre relleno de crema de chocolate',         5, 1.60, 10),
(30, 'Desayuno completo',        'Café, tostada con tomate, zumo y fruta',         5, 4.50, 10);

-- IMÁGENES DE PRODUCTOS (Por defecto, para que aparezca algo)
INSERT INTO `ProductoImagen` (`id`, `producto_id`, `ruta_imagen`) VALUES
(1, 4, '/img/uploads/productos/producto_4_69a94d9a16216.jpg'),
(2, 3, '/img/uploads/productos/producto_3_69a94dc0c35b5.jpg'),
(3, 1, '/img/uploads/productos/producto_1_69a94ddcb011a.jpg'),
(4, 5, '/img/uploads/productos/producto_5_69a94e670a738.jpg'),
(5, 5, '/img/uploads/productos/producto_5_69a94e670bb78.jpg'),
(6, 5, '/img/uploads/productos/producto_5_69a94e670d5db.jpg'),
(7, 5, '/img/uploads/productos/producto_5_69a94e670e668.png'),
(8, 2, '/img/uploads/productos/producto_2_69aca82278e9b.jpg'),
(9, 6, '/img/uploads/productos/producto_6_69aca82e05aba.jpg'),
(10, 9, '/img/uploads/productos/producto_9_69aca8c9ac2b6.jpg'),
(11, 9, '/img/uploads/productos/producto_9_69aca8c9ad337.jpg'),
(12, 9, '/img/uploads/productos/producto_9_69aca8c9aec73.jpg'),
(13, 9, '/img/uploads/productos/producto_9_69aca8c9af735.jpg'),
(14, 8, '/img/uploads/productos/producto_8_69aca91ca2bfc.jpg'),
(15, 7, '/img/uploads/productos/producto_7_69aca97eaa07b.jpg');

-- NUEVOS PEDIDOS DE PRUEBA
-- Pedido 1: Cliente 1 (Koke) pide un Bocadillo de Jamón y un Refresco.
INSERT INTO `Pedidos` (`id`, `numero_pedido`, `estado`, `tipo`, `cliente_id`, `cocinero_id`, `total`) 
VALUES (1, 101, 'nuevo', 'local', 4, 2, 5.00);

INSERT INTO `PedidoProducto` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) 
VALUES (1, 1, 1, 3.50), -- Bocadillo de jamón
       (1, 5, 1, 1.50); -- Refresco

-- Pedido 2: Cliente 2 (Antoine) pide el Menú del día.
INSERT INTO `Pedidos` (`id`, `numero_pedido`, `estado`, `tipo`, `cliente_id`, `cocinero_id`, `total`) 
VALUES (2, 102, 'en preparacion', 'local', 5, 2, 8.50);

INSERT INTO `PedidoProducto` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) 
VALUES (2, 7, 1, 8.50); -- Menú del día

-- Pedido 3: Cliente 1 (Koke) pide para llevar dos bocadillos de calamares y dos aguas.
INSERT INTO `Pedidos` (`id`, `numero_pedido`, `estado`, `tipo`, `cliente_id`, `cocinero_id`, `total`) 
VALUES (3, 103, 'nuevo', 'llevar', 4, NULL, 10.00);

INSERT INTO `PedidoProducto` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) 
VALUES (3, 3, 2, 4.00), -- 2x Bocadillo de calamares
       (3, 4, 2, 1.00); -- 2x Agua mineral
