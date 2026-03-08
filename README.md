# Bistro-FDI

Aplicación web para la gestión de un restaurante universitario, desarrollada como proyecto de la asignatura **Aplicaciones Web** (curso 2025/2026) en la Facultad de Informática de la UCM.

## Equipo
| Nombre | GitHub |
|---|---|
| Matteo Cazzola | [@Mattcazz](https://github.com/Mattcazz) |
| Marco González | [@MarcoGon27](https://github.com/MarcoGon27) |
| Nicolás Ucieda | [@Nixo371](https://github.com/Nixo371) |
| Juan David García | [@Juandaga218](https://github.com/Juandaga218) |
| Rodrigo Santana | [@rodriigoosantana](https://github.com/rodriigoosantana) |

---

## Instalación

### Requisitos
- XAMPP (PHP 8.1+, MySQL, Apache)

### Pasos

1. Clona el repositorio en la carpeta `htdocs` de XAMPP:
   ```bash
   git clone https://github.com/rodriigoosantana/Bistro-FDI.git
   ```

2. Importa los ficheros SQL en phpMyAdmin en este orden:
   ```
   sql/00-createdb.sql
   sql/01-createuser.sql
   sql/02-tablas.sql
   sql/03-datos.sql
   ```

3. Accede desde el navegador:
   ```
   http://localhost/Bistro-FDI
   ```

### Usuarios de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| `gerente1` | `password` | Gerente |
| `cocinero1` | `password` | Cocinero |
| `camarero1` | `password` | Camarero |
| `cliente1` | `password` | Cliente |

---

## Arquitectura
El proyecto sigue una arquitectura con separación de responsabilidades, respetando las siguientes normas de diseño:

```
Vista (PHP)  →  Service (lógica de negocio)  →  DB (acceso a datos)
```

1. **El Dominio no conoce HTML**: Las clases del modelo de dominio (como Entidades y Servicios) no generan, imprimen ni devuelven código HTML. Su responsabilidad exclusiva es manejar la lógica de negocio y los datos. La representación e interfaz visual se maneja únicamente en la capa de la vista (o mediante formularios).
2. **Las Vistas no conocen la BBDD**: Las vistas y plantillas de la interfaz de usuario no realizan consultas (SQL) directas a la base de datos ni manejan la conexión a la misma. Cuando requieren datos, los solicitan a la capa transaccional a través de las clases Service y consumen objetos construidos por el dominio.