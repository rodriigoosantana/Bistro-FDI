<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\Usuario; ?>

<nav>
  <h3>Navegación</h3>
  <ul>
    <li><a href="<?php echo RUTA_APP . '/index.php' ?>">Página inicio</a></li>

    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>

      <!-- Lo que pueden ver solo gerentes -->
      <?php if ($_SESSION['rolId'] === Usuario::ROL_GERENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/productoslist.php' ?>">Productos</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/categoriaslist.php' ?>">Categorías</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/listaUsuarios.php' ?>">Usuarios</a></li>
      <?php endif; ?>

      <!-- Lo que pueden ver solo cocineros y gerentes -->
      <?php if ($_SESSION['rolId'] <= Usuario::ROL_COCINERO): ?>
      <?php endif; ?>

      <!-- Lo que pueden ver solo camareros, cocineros y gerentes -->
      <?php if ($_SESSION['rolId'] <= Usuario::ROL_CAMARERO): ?>
      <?php endif; ?>

      <!-- Lo que pueden ver todos los usuarios registrados -->
      <?php if ($_SESSION['rolId'] <= Usuario::ROL_CLIENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/nuevo_pedido.php' ?>">Nuevo Pedido</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/pedidoslist.php' ?>">Mis Pedidos</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/perfilUsuario.php?nombreUsuario=' . $_SESSION['nombreUsuario']; ?>">Mi Perfil</a></li>
      <?php endif; ?>


      <li><a href="<?php echo RUTA_VISTAS . '/logout.php' ?>">Cerrar sesión</a></li>

    <?php else: ?> <!-- Si no está logueado -->
      <li><a href="<?php echo RUTA_VISTAS . '/login.php' ?>">Iniciar sesión</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/registro.php' ?>">Registrarse</a></li>
    <?php endif; ?>

  </ul>
</nav>
