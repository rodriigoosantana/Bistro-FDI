<?php
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Bocetos';
$tituloHeader = 'Bocetos de las Vistas';
$contenidoPrincipal=<<<EOS
    <div id="contenedor">
        <!-- Contenido principal -->
        <main>
<h2>Bocetos de la Aplicación</h2>

      <p>
        En esta sección se presentan los bocetos de baja fidelidad
        que representan las principales pantallas de la aplicación.
      </p>
        <section id="indice-contenido" style="background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px;">
          <h3>Índice de Pantallas</h3>
          <ul>
            <li><strong>Compartido</strong>
              <ul>
                <li><a href="#login-registro">Registro e inicio de sesión</a></li>
                <li><a href="#ver-usuario">Ver Usuario</a></li>
                <li><a href="#lista-productos">Lista de Productos</a> / <a href="#ver-producto">Ver Producto</a></li>
                <li><a href="#lista-categorias">Lista de Categorías</a> / <a href="#ver-categoria">Ver Categoría</a></li>
                <li><a href="#lista-recompensas">Lista de Recompensas</a> / <a href="#ver-recompensa">Ver Recompensa</a></li>
                <li><a href="#lista-ofertas">Lista de Ofertas</a> / <a href="#ver-oferta">Ver Oferta</a></li>
              </ul>
            </li>
            <li><strong>Cliente</strong>
              <ul>
                <li><a href="#inicio-cliente">Inicio de Cliente</a></li>
                <li><a href="#nuevo-pedido">Proceso de nuevo pedido</a></li>
                <li><a href="#pedidos-cliente">Lista de pedidos en curso</a></li>
              </ul>
            </li>
            <li><strong>Gerente</strong>
              <ul>
                <li><a href="#inicio-gerente">Inicio Gerente</a></li>
                <li><a href="#lista-usuarios">Lista de Usuarios</a></li>
                <li><a href="#crear-producto">Crear Producto</a></li>
                <li><a href="#crear-categoria">Crear Categoría</a></li>
                <li><a href="#crear-oferta">Crear Oferta</a></li>
                <li><a href="#crear-recompensa">Crear Recompensa</a></li>
              </ul>
            </li>
            <li><strong>Camarero</strong>
            <ul>
              <li><a href="#inicio-camarero">Inicio Camarero</a></li>
              <li><a href="#pedidos-recibidos-camarero">Pedidos Recibidos</a></li>
              <li><a href="#pedidos-listos-camarero">Pedidos Listos Para Recoger</a></li>
            </ul>
          </li>
          <li><strong>Cocinero</strong>
            <ul>
              <li><a href="#inicio-cocinero">Inicio Cocinero</a></li>
              <li><a href="#pedidos-preparacion-cocinero">Pedidos en Preparación</a></li>
              <li><a href="#pedidos-a-preparar-cocinero">Pedidos A Preparar</a></li>
            </ul>
          </li>
        </ul>
      </section>     <section>
        <h3>Compartido (para cualquier usuario)</h3>

        <article class="boceto">
          <h4 id="login-registro">Página – Registro e inicio de sesión</h4>

          <figure>
            <img src="img/Login.png" alt="Boceto pantalla de inicio de sesión" width="600">
            <figcaption>Pantalla de inicio de sesión.</figcaption>
          </figure>

          <figure>
            <img src="img/Register.png" alt="Boceto pantalla de registro" width="600">
            <figcaption>Pantalla de registro.</figcaption>
          </figure>

          <p>
            Cuando un usuario entra al sistema
            siempre tiene que pasar por la pantalla de login.
            En el caso de que no tenga cuenta tiene que registrarse.
            Una vez pasado este proceso el usuario pasara a la pagina de inicio.
          </p>

          <p><strong>Navegación:</strong> Login | Registro → <a href="#inicio-cliente">Inicio Cliente</a> o <a href="#inicio-gerente">Inicio Gerente</a> o <a href="#inicio-camarero">Inicio Camarero</a> o <a href="#inicio-cocinero">Inicio Cocinero</a></p>

          <h4 id="ver-usuario">Página – Ver Usuario</h4>
          <figure>
            <img src="img/ViewUser.png" alt="Boceto Página Ver Usuario" width="600">
            <figcaption>Pantalla Ver Usuario</figcaption>
          </figure>

          <p>
            Muestra la información almacenada del usuario. El usuario va a poder modicar sus atributos.
            Solo el gerente puede modificar el rol del usuario. El usuario solo puede ver y modificar su propio usuario,
            mientras que el gerente lo puede hacer con todos. 
          </p>

          <p><strong>Navegación:</strong> Modificar -> Pone los campos modificables (solo el gerente puede modificar el rol)</p>



          <h4 id="lista-productos">Página – Lista de Productos</h4>
          <figure>
            <img src="img/ListaProductos.png" alt="Boceto de la Lista de Productos" width="600">
            <figcaption>Pantalla de Lista Productos</figcaption>
          </figure>

          <p>
            Muestra todos los productos que se han creado. La opción de "Crear Nuevo" solo estara disponible si el usuario
            es gerente.
          </p>

          <p><strong>Navegación:</strong> Ver -> <a href="#ver-producto">Página de Ver Producto</a></p>

          <h4 id="ver-producto">Página – Ver Producto</h4>
          <figure>
            <img src="img/VerProducto.png" alt="Boceto Página Ver Producto" width="600">
            <figcaption>Pantalla Ver Producto</figcaption>
          </figure>

          <p>
            Muestra la información almacenada del producto. La opción de modificar cualquier campo y de borrar el producto,
            aparece si el usuario es un gerente.
          </p>

          <p><strong>Navegación:</strong> Modificar (Solo Gerente) -> Pone los campos modificables</p>

          <p> Borrar (Solo Gerente) -> Borra el objeto del sistema. </p>
          <h4 id="lista-categorias">Página – Lista de Categorías</h4>
          <figure>
            <img src="img/ListaCategorias.png" alt="Boceto de la Lista de Categorias" width="600">
            <figcaption>Pantalla de Lista categorias</figcaption>
          </figure>

          <p>
            Muestra todos los categorias que se han creado. La opción de "Crear Nuevo" solo estara disponible si el usuario
            es gerente.
          </p>

          <p><strong>Navegación:</strong> Ver -> <a href="#ver-categoria">Página de Ver Categoria</a></p>

          <h4 id="ver-categoria">Página – Ver Categoría</h4>
          <figure>
            <img src="img/VerCategoria.png" alt="Boceto Página Ver categoria" width="600">
            <figcaption>Pantalla Ver categoria</figcaption>
          </figure>

          <p>
            Muestra la información almacenada del categoria. La opción de modificar cualquier campo y de borrar la categoria,
            aparece si el usuario es un gerente.
          </p>

          <p><strong>Navegación:</strong> Modificar (Solo Gerente) -> Pone los campos modificables</p>

          <p> Borrar (Solo Gerente) -> Borra el objeto del sistema. </p>

          <h4 id="lista-recompensas">Página – Lista de Recompensas</h4>
          <figure>
            <img src="img/ListaDeRecompensas.png" alt="Boceto de la Lista de Recompensas" width="600">
            <figcaption>Pantalla de Lista Recompensas</figcaption>
          </figure>

          <p>
            Muestra todos los Recompensas que se han creado. La opción de "Crear Nuevo" solo estara disponible si el usuario
            es gerente.
          </p>

          <p><strong>Navegación:</strong> Ver -> <a href="#ver-recompensa">Página de Ver Recompensa</a></p>

          <h4 id="ver-recompensa">Página – Ver Recompensa</h4>
          <figure>
            <img src="img/VerRecompensa.png" alt="Boceto Página Ver Recompensa" width="600">
            <figcaption>Pantalla Ver Recompensa</figcaption>
          </figure>

          <p>
            Muestra la información almacenada de la recompensa. La opción de modificar cualquier campo y de borrar la recompensa,
            aparece si el usuario es un gerente.
          </p>

          <p><strong>Navegación:</strong> Modificar (Solo Gerente) -> Pone los campos modificables</p>

          <p> Borrar (Solo Gerente) -> Borra el objeto del sistema. </p>

          <h4 id="lista-ofertas">Página – Lista de Ofertas</h4>
          <figure>
            <img src="img/ListaDeOfertas.png" alt="Boceto de la Lista de Ofertas" width="600">
            <figcaption>Pantalla de Lista Ofertas</figcaption>
          </figure>

          <p>
            Muestra todos los Ofertas que se han creado. La opción de "Crear Nuevo" solo estara disponible si el usuario
            es gerente.
          </p>

          <p><strong>Navegación:</strong> Ver -> <a href="#ver-oferta">Página de Ver Oferta</a></p>

          <h4 id="ver-oferta">Página – Ver Oferta</h4>
          <figure>
            <img src="img/VerOferta.png" alt="Boceto Página Ver Oferta" width="600">
            <figcaption>Pantalla Ver Oferta</figcaption>
          </figure>

          <p>
            Muestra la información almacenada de la oferta. La opción de modificar cualquier campo y de borrar la recompensa,
            aparece si el usuario es un gerente.
          </p>

          <p><strong>Navegación:</strong> Modificar (Solo Gerente) -> Pone los campos modificables</p>

          <p> Borrar (Solo Gerente) -> Borra el objeto del sistema. </p>
        </article>

      </section>

      <section>
        <h3>Cliente</h3>
        <article class="boceto">
          <h4 id="inicio-cliente">Página – Inicio de Cliente</h4>

          <figure>
            <img src="img/InicioCliente.png" alt="Boceto de inicio de cliente" width="600">
            <figcaption>Página de Inicio Cliente</figcaption>
          </figure>

          <p>
            Página de inicio de cliente. Dara acceso a todas las funcionalidades a las que tenga acceso.
            Además, se muestra la cantidad de BistroCoins que tiene el usuario disponibles para gastar.
          </p>

          <p><strong>Navegación:</strong></p>
          <p> Nuevo Pedido -> <a href="#nuevo-pedido">Página Nuevo Pedido</a> (se demostrara a continuación)</p>
          <p> Ver Pedidos -> <a href="#pedidos-cliente">Página Lista de Pedidos Cliente</a></p>
          <p> Ver Ofertas -> <a href="#lista-ofertas">Página Lista de Ofertas</a></p>
          <p> Ver Recompensas -> <a href="#lista-recompensas">Página Lista de Recompensas</a></p>


          <h4 id="nuevo-pedido">Página – Proceso de nuevo pedido</h4>
          <figure>
            <img src="img/NuevoPedidoProceso.png" alt="Boceto del proceso de un nuevo pedido" width="900">
            <figcaption>Proceso de nuevo pedido</figcaption>
          </figure>

          <p>
            Aqui se muestra el proceso entero de un nuevo pedido. Para destacar se tiene que en cualquier momento se puede cancelar el pedido y volver a la pantalla de Inicio del usuario.
          </p>

          <h4 id="pedidos-cliente">Página – Lista de pedidos de cliente</h4>
          <figure>
            <img src="img/PedidosEnCursoCliente.png" alt="Boceto del proceso de un nuevo pedido" width="600">
            <figcaption>Pedidos en curso del cliente</figcaption>
          </figure>

          <p>
            Se muestran los pedidos que el cliente tiene en curso y su estado.
          </p>


        </article>

      </section>


      <section>
        <h3>Gerente</h3>
        <article class="boceto">

          <h4 id="inicio-gerente">Página – Inicio Gerente</h4>

          <figure>
            <img src="img/InicioGerente.png" alt="Boceto pantalla gerente" width="600">
            <figcaption>Interfaz simplificada para el gerente.</figcaption>
          </figure>

          <p><strong>Navegación:</strong></p>
          <p> Pedidos -> <a href="#pedidos-cliente">Lista Pedidos</a> (Vista general) </p>
          <p> Productos -> <a href="#lista-productos">Lista Productos</a> </p>
          <p> Ofertas -> <a href="#lista-ofertas">Página Lista de Ofertas</a></p>
          <p> Recompensas -> <a href="#lista-recompensas">Página Lista de Recompensas</a></p>
          <p> Usuarios -> <a href="#lista-usuarios">Página Lista de Usuarios</a> </p>



          <h4 id="lista-usuarios">Página – Lista de Usuarios</h4>

          <figure>
            <img src="img/UserList.png" alt="Boceto pantalla gerente" width="600">
            <figcaption>Interfaz simplificada para el gerente.</figcaption>
          </figure>

          <p>Muestra la lista de usuarios creados para que el gerente los pueda manegar. </p>

          <p><strong>Navegación:</strong> Ver -> <a href="#ver-usuario">Ver Usuario</a> </p>

          <h4 id="crear-producto">Página – Crear Producto</h4>

          <figure>
            <img src="img/CrearProducto.png" alt="Boceto Crear Producto" width="600">
            <figcaption>Pagina Crear Producto</figcaption>
          </figure>

          <p>
            Permite crear un nuevo producto en el sistema. Solo tiene acceso por parte del gerente.
          </p>

          <p><strong>Navegación:</strong> Crear -> <a href="#ver-producto">Ver Producto</a> </p>

          <h4 id="crear-categoria">Página – Crear Categoría</h4>

          <figure>
            <img src="img/CrearCategoria.png" alt="Boceto Crear Categoria" width="600">
            <figcaption>Pagina Crear Categoria</figcaption>
          </figure>

          <p>
            Permite crear un nuevo categoria en el sistema. Solo tiene acceso por parte del gerente.
          </p>

          <p><strong>Navegación:</strong> Crear -> <a href="#ver-categoria">Ver Categoria</a> </p>

          <h4 id="crear-oferta">Página – Crear Oferta</h4>

          <figure>
            <img src="img/CrearOferta.png" alt="Boceto Crear Oferta" width="600">
            <figcaption>Pagina Crear Oferta</figcaption>
          </figure>

          <p>
            Permite crear un nuevo oferta en el sistema. Solo tiene acceso por parte del gerente.
          </p>

          <p><strong>Navegación:</strong> Crear -> <a href="#ver-oferta">Ver Oferta</a> </p>

          <h4 id="crear-recompensa">Página – Crear Recompensa</h4>

          <figure>
            <img src="img/CrearRecompensas.png" alt="Boceto Crear Recompensa" width="600">
            <figcaption>Pagina Crear Recompensa</figcaption>
          </figure>

          <p>
            Permite crear un nuevo recompensa en el sistema. Solo tiene acceso por parte del gerente.
          </p>

          <p><strong>Navegación:</strong> Crear -> <a href="#ver-recompensa">Ver Recompensa</a> </p>

        </article>
      </section>

      <section>
        <h3>Camarero</h3>

        <article class="boceto">
          <h4 id="inicio-camarero">Página – Inicio Camarero</h4>

          <figure>
            <img src="img/InicioCamarero.png" alt="Boceto pantalla camarero" width="600">
            <figcaption>Interfaz simplificada para el camarero.</figcaption>
          </figure>

          <p>
            Muestra los pedidos en estado "Recibido",
            "Listo cocina" y "Terminado".
            Permite cobrar y marcar como entregado.
          </p>

          <p><strong>Navegación:</strong> Login camarero → <a href="#pedidos-recibidos-camarero">Gestión de pedidos</a></p>
        </article>

        <article class="boceto">
          <h4 id="pedidos-recibidos-camarero">Página – Pedidos Recibidos</h4>

          <figure>
            <img src="img/PedidosRecibidosCamarero.png" alt="Boceto pantalla pedidos recibidos camarero" width="600">
            <figcaption>Vista de pedidos recibidos para el camarero</figcaption>
          </figure>

          <p>
            El camarero puede seleccionar un pedido recibido y hacer operaciones con el como cobrar.
            En todas las pantallas aparecera el avatar
            Se puede volver a la pantalla inicial del Cocinero pulsando en <a href="#inicio-camarero">"Atrás"</a>
          </p>

        </article>

        <article class="boceto">
          <h4 id="pedidos-listos-camarero">Página – Pedidos Listos Para Recoger</h4>

          <figure>
            <img src="img/PedidosListosParaRecogerCamarero.png" alt="Boceto pantalla pedidos para recoger del camarero" width="600">
            <figcaption>Vista de pedidos a recoger para el camarero</figcaption>
          </figure>

          <p>
            El camarero puede ver los productos terminados y los que faltan por hacer (por ejemplo, bebidas) y marcarlos como terminados si hace falta
            En todas las pantallas aparecera el avatar
            Se puede volver a la pantalla inicial del Camarero pulsando en <a href="#inicio-camarero">"Atrás"</a>
          </p>

        </article>
      </section>


      <section>
        <h3>Cocinero</h3>

        <article class="boceto">
          <h4 id="inicio-cocinero">Página – Inicio De Cocinero</h4>

          <figure>
            <img src="img/InicioCocinero.png" alt="Boceto pantalla cocina" width="600">
            <figcaption>Vista de menu para el cocinero</figcaption>
          </figure>

          <p>
            El cocinero puede seleccionar si quiere ver los pedidos en preparacion
            o los pedidos que se tiene que preparar.
            En todas las pantallas aparecera el avatar
          </p>

          <p><strong>Navegación:</strong></p>
          <p> Pedidos En Preparacion -> <a href="#pedidos-preparacion-cocinero">Pagina Pedidos En Preparacion</a></p>
          <p> Mis Pedidos A Preparar -> <a href="#pedidos-a-preparar-cocinero">Mis Pedidos a Preparar</a></p>
          
        </article>

        <article class="boceto">
          <h4 id="pedidos-preparacion-cocinero">Página – Pedidos en Preparación</h4>

          <figure>
            <img src="img/PedidosEnPreparacionCocinero.png" alt="Boceto pantalla pedidos preparacion cocinero" width="600">
            <figcaption>Vista de pedidos en preparacion para el cocinero</figcaption>
          </figure>

          <p>
            El cocinero puede seleccionar pedidos en estado
            "En preparación" y marcarlos como "Cocinando"
            o "Listo cocina".
            En todas las pantallas aparecera el avatar
            Se puede volver a la pantalla inicial del Cocinero pulsando en <a href="#inicio-cocinero">"Atrás"</a>
          </p>

        </article>

        <article class="boceto">
          <h4 id="pedidos-a-preparar-cocinero">Página – Pedidos A Preparar</h4>

          <figure>
            <img src="img/PedidosAPrepararCocinero.png" alt="Boceto pantalla pedidos a preparar cocinero" width="600">
            <figcaption>Vista de pedidos a preparar para el cocinero</figcaption>
          </figure>

          <p>
            El cocinero puede, dentro de cada pedido, marcar los productos individuales
            que estan listos para que el gerente sepa que esta listo
            En todas las pantallas aparecera el avatar
            Se puede volver a la pantalla inicial del Cocinero pulsando en <a href="#inicio-cocinero">"Atrás"</a>
          </p>

        </article>
      </section>

        </main>
    </div>
EOS;

$listaCaracteristicas = [
        "📱 Adaptado a móviles",
        "🖥 Adaptado a escritorio",
        "👨‍🍳 Interfaz simplificada por rol",
        "🧭 Navegación clara y estructurada"
];

require("includes/vistas/common/plantilla.php");
?>
