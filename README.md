# Bistro-FDI

Aplicación web para la gestión integral de un restaurante universitario: catálogo de productos, carrito y pedidos con seguimiento de estados, sistema de ofertas y programa de recompensas. Desarrollada como proyecto de la asignatura **Aplicaciones Web** (curso 2025/2026) en la Facultad de Informática de la UCM.

## Equipo

| Nombre | GitHub | Funcionalidades desarrolladas |
|---|---|---|
| Matteo Cazzola | [@Mattcazz](https://github.com/Mattcazz) | F2 - Gestión de pedidos · F5 - Programa de recompensas |
| Marco González | [@MarcoGon27](https://github.com/MarcoGon27) | F0 - Gestión de usuarios · F5 - Programa de recompensas |
| Nicolás Ucieda | [@Nixo371](https://github.com/Nixo371) | F3 - Preparación de los pedidos  |
| Juan David García | [@Juandaga218](https://github.com/Juandaga218) | F2 - Gestión de productos y categorías · F4 - Gestión de ofertas |
| Rodrigo Santana | [@rodriigoosantana](https://github.com/rodriigoosantana) | F2 - Gestión de productos y categorías · F4 - Gestión de ofertas |

---

## Funcionalidades

- **Catálogo** de productos organizados por categorías, con imágenes, IVA configurable (0 %, 4 %, 10 %, 21 %) y borrado lógico.
- **Carrito y pedidos** con máquina de estados (*Pedido → En preparación → Terminado → Entregado → Pagado*) y flujo diferenciado por rol.
- **Ofertas** aplicables al carrito con cálculo automático de descuento por división entera sobre las cantidades.
- **Recompensas** canjeables por los clientes mediante un sistema de puntos acumulados.
- **Cuatro roles diferenciados** (gerente, cocinero, camarero, cliente), cada uno con su propia navegación y permisos.

---

## Stack técnico

- **PHP 8.1+** con namespace `es\ucm\fdi\aw` y autoloader PSR-4
- **MySQLi** con `MYSQLI_REPORT_STRICT` (excepciones automáticas, sin comprobaciones manuales de `execute()`)
- **Apache** sobre Linux (producción) / XAMPP (desarrollo local)
- **Frontend**: HTML5, CSS3 modularizado con `@import`, JavaScript vanilla

---

## Instalación

### Requisitos
- XAMPP con PHP 8.1 o superior

### Pasos

1. Clona el repositorio dentro de la carpeta `htdocs` de XAMPP en la rama de desarrollo:
   ```bash
   git clone -b develop https://github.com/rodriigoosantana/Bistro-FDI.git
   ```

2. Importa los ficheros SQL en phpMyAdmin **en este orden**:
   ```
   includes/mysql/00-create_db.sql
   includes/mysql/01-create_user.sql
   includes/mysql/02-tablas.sql
   includes/mysql/03-datos.sql
   ```

3. Accede desde el navegador:
   ```
   http://localhost/Bistro-FDI
   ```

### Usuarios de prueba

Credenciales de desarrollo incluidas en `03-datos.sql`:

| Usuario | Contraseña | Rol |
|---|---|---|
| `gerente1` | `password` | Gerente |
| `cocinero1` | `password` | Cocinero |
| `camarero1` | `password` | Camarero |
| `cliente1` | `password` | Cliente |

---

## Despliegue

La aplicación está desplegada en el VPS de la facultad: `vm013.containers.fdi.ucm.es`.

Tras el primer despliegue, es necesario otorgar permisos de escritura a Apache sobre el directorio de subidas:

```bash
sudo chown -R www-data:www-data img/uploads/
```

---

## Arquitectura

El proyecto sigue una arquitectura en tres capas con separación estricta de responsabilidades:

```
Vista (PHP)  →  Service (lógica de negocio)  →  DB (acceso a datos)
```

### Principios de diseño

1. **El dominio no conoce HTML.** Las entidades y servicios no generan, imprimen ni devuelven código HTML. Su responsabilidad es la lógica de negocio y los datos. La representación se maneja en la capa de vista o mediante formularios.
2. **Las vistas no conocen la BBDD.** Las vistas no realizan consultas SQL ni gestionan la conexión. Cuando necesitan datos, los solicitan a la capa de servicios y consumen objetos de dominio.
3. **El SQL vive únicamente en las clases `*DB`.** Ninguna consulta se escribe en un `*Service` ni en una vista. Todas las consultas usan *prepared statements* con `bind_param`.
4. **Borrado lógico.** Los productos y categorías no se eliminan físicamente: se marcan como `activo = 0`. Desactivar una categoría desactiva en cascada sus productos.
5. **Sanitización en el render.** `htmlspecialchars()` se aplica únicamente al renderizar, nunca en `procesaFormulario`.

### Patrones empleados

- **Singleton** en `Aplicacion` para el acceso centralizado a sesión, BBDD y usuario en curso.
- **Template Method** en `formularioBase`, de la que heredan todos los formularios (`FormularioProducto`, `FormularioOferta`, etc.).
- **DTO** con `PedidoDesglosado` para transportar pedidos enriquecidos a la vista sin exponer la entidad.
- **Autoloader PSR-4** registrado en `config.php`, mapeando el namespace `es\ucm\fdi\aw\` a `includes/`.

### Estructura del proyecto

```
Bistro-FDI/
├── index.php                    # Front controller
├── css/                         # Hoja de entrada + 6 parciales (@import)
├── js/
├── img/
│   └── uploads/                 # Imágenes subidas (productos, categorías, avatares)
└── includes/
    ├── Aplicacion.php           # Singleton de aplicación
    ├── config.php               # Constantes, autoloader, conexión
    ├── mysql/                   # Scripts SQL (00-03)
    ├── Producto/                # Producto, Categoría, ProductoImagen (Entidad + DB + Service)
    ├── Pedido/                  # Pedido, PedidoDesglosado, Estado, Tipo, PagoService
    ├── Oferta/                  # Oferta, OfertaProducto
    ├── Recompensa/
    ├── Usuario/                 # Usuario, Rol, RolesUsuario
    └── vistas/
        ├── common/              # plantilla.php, header, nav, aside, footer, formularioBase
        ├── productos/
        ├── pedidos/
        ├── ofertas/
        ├── recompensas/
        └── usuario/
        ├── recompensas/
        └── usuario/
```
