<nav>
  <h3>Navegación</h3>
  <ul>
    <li><a href="<?php echo RUTA_APP . '/index.php' ?>">Página Principal</a></li>


    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_GERENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/productoslist.php' ?>">Productos</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/categoriaslist.php' ?>">Categorías</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/listaUsuarios.php' ?>">Usuarios</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_COCINERO): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_CAMARERO): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidoslist.php' ?>">Pedidos</a></li>
      <?php endif; ?>

      <?php if ($_SESSION['rolId'] === Usuario::ROL_CLIENTE): ?>
        <li><a href="<?php echo RUTA_VISTAS . '/nuevo_pedido.php' ?>">Nuevo Pedido</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/pedidoslist.php' ?>">Ver Pedidos</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/ofertaslist.php' ?>">Ver Ofertas</a></li>
        <li><a href="<?php echo RUTA_VISTAS . '/recompensas.php' ?>">Recompensas</a></li>
      <?php endif; ?>



      <li><a href="<?php echo RUTA_VISTAS . '/perfilUsuario.php?nombreUsuario=' . $_SESSION['nombreUsuario']; ?>">Mi Perfil</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/logout.php' ?>">Cerrar sesión</a></li>

    <?php else: ?>
      <li><a href="<?php echo RUTA_VISTAS . '/login.php' ?>">Iniciar sesión</a></li>
      <li><a href="<?php echo RUTA_VISTAS . '/registro.php' ?>">Registrarse</a></li>
    <?php endif; ?>


    <!-- Temporal -->
    <li><a href="detalles.php">detalles</a></li>
    <li><a href="bocetos.php">bocetos</a></li>
    <li><a href="miembros.php">miembros</a></li>
    <li><a href="planificacion.php">planificación</a></li>
    <li><a href="contacto.php">contacto</a></li>
  </ul>
</nav>
