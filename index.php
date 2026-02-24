<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Bistro FDI';

$contenidoPrincipal=<<<EOS
    <div id="contenedor">
        <!-- Contenido principal -->
        <main>
            <section>
                <h2>
                    Aplicación Web para la Gestión de un Restaurante Universitario
                </h2>

                <p>
                    Bistro FDI es una aplicación web innovadora diseñada para
                    revolucionar la experiencia gastronómica en el campus de la
                    facultad. Permite a los clientes realizar pedidos de forma rápida y
                    sencilla desde sus dispositivos móviles o equipos de escritorio,
                    realizar el seguimiento en tiempo real del estado de sus pedidos, y
                    beneficiarse de un programa de fidelización con recompensas.
                    Simultáneamente, optimiza la gestión operativa del restaurante,
                    facilitando al personal de cocina la organización eficiente de los
                    pedidos, a los camareros la entrega ágil de los mismos, y al gerente
                    la administración completa del menú, ofertas especiales y recursos.
                    Con Bistro FDI, conseguimos reducir tiempos de espera, mejorar la
                    satisfacción del cliente y maximizar la eficiencia del servicio.
                </p>
            </section>
        </main>
    </div>
EOS;

$listaCaracteristicas = [
   "📦 Sistema de pedidos online",
   "⏱️ Seguimiento en tiempo real",
   "👨‍🍳 Gestión para el personal",
   "🎁 Programa de fidelización",
   "🏷️ Ofertas y descuentos"
];

require("includes/vistas/common/plantilla.php");
?>
