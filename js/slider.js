/**
 * Gestiona los sliders de imágenes en la lista de productos y en el detalle.
 *
 * Sustituye a lo que estaba en HTML como:
 *   <div class="slider-wrap"
 *        data-imagenes='["/ruta/img1.jpg", "/ruta/img2.jpg"]'
 *        data-auto="true">
 *     <img class="slider-img" src="/ruta/img1.jpg" alt="nombre">
 *     <div class="slider-dots">
 *       <span class="slider-dot active"></span>
 *       <span class="slider-dot"></span>
 *     </div>
 *   </div>
 */

document.addEventListener('DOMContentLoaded', function () {

    // Inicializar todos los sliders de la página
    var sliders = document.querySelectorAll('.slider-wrap[data-imagenes]');

    sliders.forEach(function (wrap) {
        var imagenes = JSON.parse(wrap.dataset.imagenes);
        var auto     = wrap.dataset.auto === 'true';
        var total    = imagenes.length;

        // Con una sola imagen no hace falta slider
        if (total <= 1) return;

        var img    = wrap.querySelector('.slider-img');
        var dots   = wrap.querySelectorAll('.slider-dot');
        var actual = 0;

        function goto(n) {
            actual = (n + total) % total;
            img.src = imagenes[actual];
            dots.forEach(function (d, i) {
                d.classList.toggle('active', i === actual);
            });
        }

        // Hacer cliclables los puntos
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goto(i); });
        });

        // Avance automático
        if (auto) {
            setInterval(function () { goto(actual + 1); }, 3000);
        }
    });
});