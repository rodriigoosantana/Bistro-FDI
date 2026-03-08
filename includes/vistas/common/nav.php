<nav>
  <h3>Navegación</h3>
  <ul>
    <li><a href="<?php echo RUTA_APP . '/index.php' ?>">Página inicio</a></li>

    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>

      <!-- Por defecto, al estar logueado puedes ver:
       - Tu perfil
       - Productos
       - Categorías
       - Pedidos       
      -->
      <li><a href="<?php echo RUTA_VISTAS . '/perfilUsuario.php?nombreUsuario=' . $_SESSION['nombreUsuario']; ?>">Mi
          perfil</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/productoslist.php' ?>">Productos</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/categoriaslist.php' ?>">Categorías</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/pedidoslist.php' ?>">Pedidos</a></li>

      <?php if ($_SESSION['rolId'] === 1): ?> <!-- GERENTE -->
        <li><a href="<?php echo RUTA_VISTAS . '/listaUsuarios.php' ?>">Usuarios</a></li>
        <li><a href="bocetos.php">Bocetos</a></li> <!-- PRÁCTICA 1 -->
        <li><a href="planificacion.php">Planificación</a></li> <!-- PRÁCTICA 1 -->
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === 2): ?> <!-- COCINERO -->

      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === 3): ?> <!-- CAMARERO -->

      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === 4): ?>

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