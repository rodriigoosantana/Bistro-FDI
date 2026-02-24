<?php
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Detalles';
$tituloHeader = 'Detalles del Proyecto';
$contenidoPrincipal=<<<EOS
    <div id="contenedor">
        <!-- Contenido principal -->
        <main>
         <section>
                <h2>Introducción</h2>
                <p>
                    Bistro FDI es una aplicación web integral diseñada para modernizar y optimizar la gestión
                    del restaurante universitario. La aplicación facilita la interacción entre clientes y personal
                    mediante una plataforma que permite realizar y gestionar pedidos, administrar productos y ofertas, e
                    implementar un sistema de fidelización
                    de clientes.
                </p>
            </section>

            <!-- Tipos de usuarios -->
            <section>
                <h2>Tipos de Usuarios</h2>

                <article>
                    <h3>Cliente</h3>
                    <p>
                        Usuario consumidor de los productos y servicios ofrecidos por Bistro FDI.
                    </p>
                    <p><strong>Principales Acciones:</strong></p>
                    <ul>
                        <li>Registrarse y gestionar su perfil personal</li>
                        <li>Navegar por el catálogo de productos organizados por categorías</li>
                        <li>Realizar pedidos y consultar su estado </li>
                        <li>Aplicar ofertas y descuentos disponibles</li>
                        <li>Consultar y canjear BistroCoins del programa de fidelización</li>
                    </ul>
                </article>

                <article>
                    <h3>Cocinero</h3>
                    <p>
                        Personal del Bistro FDI encargado de la preparación de los pedidos.
                    </p>
                    <p><strong>Principales Acciones:</strong></p>
                    <ul>
                        <li>Visualizar pedidos pendientes de preparación</li>
                        <li>Asignarse pedidos para comenzar su preparación</li>
                        <li>Marcar productos como preparados</li>
                        <li>Finalizar pedidos cuando estén listos</li>
                        <li>Consultar detalles específicos de cada producto del pedido</li>
                    </ul>
                </article>

                <article>
                    <h3>Camarero</h3>
                    <p>
                        Personal del Bistro FDI responsable de la entrega de pedidos a los clientes y, cuando
                        corresponda, del cobro de los mismos.
                    </p>
                    <p><strong>Principales Acciones:</strong></p>
                    <ul>
                        <li>Confirmar pagos de pedidos cuando el cliente paga</li>
                        <li>Preparar pedidos para llevar y añadir productos adicionales no preparados en cocina</li>
                        <li>Entregar pedidos a los clientes</li>
                        <li>Cambiar el estado de los pedidos </li>
                    </ul>
                </article>

                <article>
                    <h3>Gerente</h3>
                    <p>
                        Personal del Bistro FDI encargado de la gestión administrativa completa del restaurante.
                    </p>
                    <p><strong>Principales Acciones:</strong></p>
                    <ul>
                        <li>Gestionar categorías de productos</li>
                        <li>Gestionar productos de la carta </li>
                        <li>Administrar ofertas especiales y promociones</li>
                        <li>Configurar recompensas del programa de fidelización</li>
                        <li>Gestionar usuarios y asignar roles al personal</li>
                        <li>Consultar el estado general de todos los pedidos</li>
                    </ul>
                </article>
            </section>

            <!-- Funcionalidades -->
            <section>
                <h2>Funcionalidades del Sistema</h2>

                <article>
                    <h3>Funcionalidad 1: Gestión de Usuarios</h3>
                    <p>
                        Sistema de registro, autenticación y gestión de perfiles de usuario. Permite la creación
                        de cuentas tanto para clientes como para el personal del restaurante, con diferentes
                        niveles de acceso según el rol asignado.
                    </p>
                    <p><strong>Características:</strong></p>
                    <ul>
                        <li>Registro de nuevos usuarios </li>
                        <li>Sistema de login con nombre de usuario y contraseña</li>
                        <li>Gestión de perfiles: nombre, apellidos, email</li>
                        <li>Sistema de roles</li>
                    </ul>
                </article>

                <article>
                    <h3>Funcionalidad 2: Gestión de Productos</h3>
                    <p>
                        Permite al gerente administrar el catálogo completo de productos y categorías ofrecidos
                        en el restaurante, incluyendo precios, disponibilidad e imágenes.
                    </p>
                    <p><strong>Características:</strong></p>
                    <ul>
                        <li>Gestión de categorías con nombre, descripción e imagen</li>
                        <li>Gestión de productos con múltiples imágenes</li>
                        <li>Configuración de precios </li>
                        <li>Indicación de disponibilidad de productos</li>
                    </ul>
                </article>

                <article>
                    <h3>Funcionalidad 3: Gestión de Pedidos</h3>
                    <p>
                        Funcionalidad central del sistema que permite a los clientes realizar pedidos y al personal
                        del restaurante gestionar su preparación y entrega. Incluye múltiples estados y flujos de
                        trabajo adaptados a cada rol.
                    </p>
                    <p><strong>Estados de un Pedido</strong></p>
                    <ul>
                        <li><strong>Nuevo:</strong> Pedido en proceso de creación</li>
                        <li><strong>Recibido:</strong> Pedido confirmado pero no pagado</li>
                        <li><strong>En preparación:</strong> Pedido pagado, esperando ser asignado a un cocinero</li>
                        <li><strong>Cocinando:</strong> Pedido siendo preparado por un cocinero</li>
                        <li><strong>Listo cocina:</strong> Pedido terminado en cocina, esperando al camarero</li>
                        <li><strong>Terminado:</strong> Pedido listo para entregar al cliente</li>
                        <li><strong>Entregado:</strong> Pedido recogido por el cliente</li>
                        <li><strong>Cancelado:</strong> Pedido cancelado antes del pago</li>
                    </ul>
                    <p><strong>Características para Clientes:</strong></p>
                    <ul>
                        <li>Selección de tipo de pedido: "Local" o "Para llevar"</li>
                        <li>Navegación por categorías y productos</li>
                        <li>Carrito de compra con modificación de cantidades</li>
                        <li>Confirmación o cancelación del pedido</li>
                        <li>Pago online o solicitud de pago al camarero</li>
                        <li>Seguimiento del estado del pedido en tiempo real</li>
                    </ul>
                    <p><strong>Características para Camareros:</strong></p>
                    <ul>
                        <li>Confirmar pagos de pedidos en estado "Recibido"</li>
                        <li>Preparar pedidos finales (añadir bebidas, empaquetar para llevar)</li>
                        <li>Confirmar entrega de pedidos a clientes</li>
                    </ul>
                </article>

                <article>
                    <h3>Funcionalidad 4: Preparación de Pedidos</h3>
                    <p>
                        Interfaz específica para cocineros que permite gestionar la preparación de pedidos de
                        manera eficiente, con seguimiento detallado de cada producto y visibilidad para el gerente.
                    </p>
                    <p><strong>Características:</strong></p>
                    <ul>
                        <li>Visualización de pedidos en estado "En preparación"</li>
                        <li>Asignación de pedidos a cocineros específicos</li>
                        <li>Marcado individual de productos como preparados</li>
                        <li>Finalización de pedidos completos</li>
                    </ul>
                </article>

                <article>
                    <h3>Funcionalidad 5: Gestión de Ofertas</h3>
                    <p>
                        Sistema de promociones que permite crear packs de productos con descuentos porcentuales
                        aplicables durante períodos específicos.
                    </p>
                    <p><strong>Características:</strong></p>
                    <ul>
                        <li>Creación de ofertas con nombre y descripción</li>
                        <li>Definición de productos y cantidades requeridas</li>
                        <li>Configuración de fechas de inicio y fin de la oferta</li>
                        <li>Establecimiento de porcentaje de descuento</li>
                        <li>Cálculo automático del precio final de la oferta</li>
                        <li>Aplicación múltiple de ofertas en un mismo pedido</li>
                        <li>Registro del descuento aplicado en cada pedido</li>
                        <li>Consulta de ofertas activas para clientes</li>
                    </ul>
                </article>

                <article>
                    <h3>Funcionalidad 6: Gestión de Recompensas</h3>
                    <p>
                        Programa de fidelización mediante BistroCoins que los clientes acumulan con sus compras
                        y pueden canjear por productos gratuitos.
                    </p>
                    <p><strong>Características:</strong></p>
                    <ul>
                        <li>Acumulación automática de BistroCoins: 1 coin por cada euro gastado</li>
                        <li>Creación de recompensas asociadas a productos específicos</li>
                        <li>Definición del coste en BistroCoins de cada recompensa</li>
                        <li>Consulta de saldo de BistroCoins en el perfil del cliente</li>
                        <li>Visualización destacada de recompensas disponibles según saldo</li>
                        <li>Añadir recompensas al carrito de compra</li>
                        <li>Registro diferenciado de productos obtenidos por recompensa</li>
                        <li>Deducción de BistroCoins tras confirmar el pago del pedido</li>
                    </ul>
                </article>
            </section>

        </main>
    </div>
EOS;

$listaCaracteristicas = [
  "🕴️ Gestión de usuarios",                
  "🥐 Gestión de productos",
  "🍽️ Gestión de pedidos",               
  "🍳 Preparación de pedidos",
  "🏷️ Gestión de ofertas",               
  "🎁 Gestión de recompensas"
];

require("includes/vistas/common/plantilla.php");
?>
