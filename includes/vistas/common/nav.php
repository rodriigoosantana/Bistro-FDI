<?php require_once RAIZ_APP . '/includes/Usuario/Usuario.php'; ?>

<nav>
  <h3>Navegación</h3>
  <ul>
    <li><a href="<?php echo RUTA_APP . '/index.php' ?>">Página inicio</a></li>

    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_GERENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/productoslist.php' ?>">Productos</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/categoriaslist.php' ?>">Categorías</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/listaUsuarios.php' ?>">Usuarios</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_COCINERO): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_CAMARERO): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_CLIENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/nuevo_pedido.php' ?>">Nuevo Pedido</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidos/pedidoslist.php' ?>">Mis Pedidos</a></li>
      <?php endif; ?>


      <li><a href="<?php echo RUTA_VISTAS . '/logout.php' ?>">Cerrar sesión</a></li>

    <?php else: ?> <!-- Si no está logueado -->
      <li><a href="<?php echo RUTA_VISTAS . '/login.php' ?>">Iniciar sesión</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/registro.php' ?>">Registrarse</a></li>
    <?php endif; ?>

    <!-- Temporal -->
    <li><a href="miembros.php">Miembros del equipo</a></li> <!-- PRÁCTICA 1 -->
    <li><a href="detalles.php">Detalles del proyecto</a></li> <!-- PRÁCTICA 1 -->
    <li><a href="contacto.php">Contacto</a></li> <!-- PRÁCTICA 1 -->
  </ul>
</nav>