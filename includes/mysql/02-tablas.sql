/*
  Deshabilitar la opción "Enable foreign key checks"
  para evitar problemas a la hora de importar el script.
*/
USE `bistro_fdi`;

DROP TABLE IF EXISTS `PedidoProducto`;
DROP TABLE IF EXISTS `ProductoImagen`;
DROP TABLE IF EXISTS `Pedidos`;
DROP TABLE IF EXISTS `Productos`;
DROP TABLE IF EXISTS `Categorias`;
DROP TABLE IF EXISTS `RolesUsuario`;
DROP TABLE IF EXISTS `Usuarios`;
DROP TABLE IF EXISTS `Roles`;


-- TABLA ROLES
CREATE TABLE IF NOT EXISTS `Roles` (
  `id`        int(11)     NOT NULL AUTO_INCREMENT,
  `nombre`    varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `prioridad` int(11)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rol_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA USUARIOS
CREATE TABLE IF NOT EXISTS `Usuarios` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `nombreUsuario`  varchar(50)  COLLATE utf8mb4_general_ci NOT NULL,
  `email`          varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre`         varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos`      varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password`       varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `avatar`         varchar(255) COLLATE utf8mb4_general_ci,
  `activo`         tinyint(1)   NOT NULL DEFAULT 1,
  `fecha_creacion` datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nombreUsuario` (`nombreUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA ROLES USUARIO
CREATE TABLE IF NOT EXISTS `RolesUsuario` (
  `usuario` int(11) NOT NULL,
  `rol`     int(11) NOT NULL,
  PRIMARY KEY (`usuario`,`rol`),
  KEY `rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA CATEGORIAS
CREATE TABLE IF NOT EXISTS `Categorias` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text         COLLATE utf8mb4_general_ci NOT NULL,
  `imagen`      varchar(255) COLLATE utf8mb4_general_ci,
  `activa`      tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categoria_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA PRODUCTOS
CREATE TABLE IF NOT EXISTS `Productos` (
  `id`           int(11)       NOT NULL AUTO_INCREMENT,
  `nombre`       varchar(150)  COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion`  text          COLLATE utf8mb4_general_ci NOT NULL,
  `categoria_id` int(11)       NOT NULL,
  `precio_base`  decimal(10,2) NOT NULL,
  `iva`          int(11)       NOT NULL,
  `disponible`   tinyint(1)    NOT NULL DEFAULT 1,
  `ofertado`     tinyint(1)    NOT NULL DEFAULT 0,
  `activo`       tinyint(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_producto_categoria` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA PRODUCTO_IMAGEN
CREATE TABLE IF NOT EXISTS `ProductoImagen` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `producto_id` int(11)      NOT NULL,
  `ruta_imagen` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_imagen_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA PEDIDOS
CREATE TABLE IF NOT EXISTS `Pedidos` (
  `id`             int(11)       NOT NULL AUTO_INCREMENT,
  `numero_pedido`  int(11)       NOT NULL,
  `fecha_creacion` datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado`         enum('nuevo', 'recibido', 'en preparacion', 'cocinando', 'listo cocina', 'entregado', 'cancelado') NOT NULL DEFAULT 'Nuevo',
  `tipo`           enum('local','llevar') NOT NULL,
  `cliente_id`     int(11)       NOT NULL,
  `cocinero_id`    int(11),
  `total`          decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `fk_pedido_cliente`  (`cliente_id`),
  KEY `fk_pedido_cocinero` (`cocinero_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- TABLA PEDIDO_PRODUCTO
CREATE TABLE IF NOT EXISTS `PedidoProducto` (
  `id`              int(11)       NOT NULL AUTO_INCREMENT,
  `pedido_id`       int(11)       NOT NULL,
  `producto_id`     int(11)       NOT NULL,
  `cantidad`        int(11)       NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `preparado`       tinyint(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pp_pedido`   (`pedido_id`),
  KEY `fk_pp_producto` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
