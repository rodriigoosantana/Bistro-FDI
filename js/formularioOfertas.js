document.addEventListener('DOMContentLoaded', function () {
    const contenedor = document.getElementById('lineas-oferta');
    if (!contenedor) return; // no estamos en este formulario

    const PRECIOS = JSON.parse(contenedor.dataset.precios || '{}');

    function precioSinDescuento() {
        let total = 0;
        contenedor.querySelectorAll('.linea-oferta').forEach(row => {
            const pid  = parseInt(row.querySelector('select').value) || 0;
            const cant = parseInt(row.querySelector('input[type=number]').value) || 0;
            if (PRECIOS[pid] && cant > 0) total += PRECIOS[pid] * cant;
        });
        return total;
    }

    function recalcularPack() {
        const sin = precioSinDescuento();
        document.getElementById('precio-sin').textContent =
            sin > 0 ? sin.toFixed(2) + ' €' : '—';

        const pctInput = document.getElementById('descuento-pct-ui');
        if (pctInput.value !== '' && sin > 0) {
            const pct = parseFloat(pctInput.value) / 100;
            document.getElementById('precio-final-ui').value = (sin * (1 - pct)).toFixed(2);
            document.getElementById('descuento').value = pct.toFixed(6);
        }
    }

    document.getElementById('precio-final-ui').addEventListener('input', function () {
        const sin = precioSinDescuento();
        if (sin <= 0) return;
        const final = parseFloat(this.value);
        if (isNaN(final) || final < 0) return;
        const pct = (sin - final) / sin;
        document.getElementById('descuento-pct-ui').value = (pct * 100).toFixed(2);
        document.getElementById('descuento').value = pct.toFixed(6);
    });

    document.getElementById('descuento-pct-ui').addEventListener('input', function () {
        const sin = precioSinDescuento();
        const pct = parseFloat(this.value) / 100;
        if (isNaN(pct) || pct < 0 || pct > 1) return;
        if (sin > 0) {
            document.getElementById('precio-final-ui').value = (sin * (1 - pct)).toFixed(2);
        }
        document.getElementById('descuento').value = pct.toFixed(6);
    });

    function anadirLinea() {
        const tpl   = document.getElementById('tpl-linea');
        const clone = tpl.content.cloneNode(true);
        clone.querySelectorAll('select, input[type=number]').forEach(el => {
            el.addEventListener('change', recalcularPack);
            el.addEventListener('input',  recalcularPack);
        });
        contenedor.appendChild(clone);
    }

    // listener del botón "+ Añadir producto"
    const btnAnadir = document.getElementById('btn-anadir-linea');
    if (btnAnadir) btnAnadir.addEventListener('click', anadirLinea);

    // delegación de eventos para los botones "✕" de cada línea:
    // cubre tanto las líneas iniciales como las añadidas dinámicamente
    contenedor.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-rm-linea')) {
            const fila = e.target.closest('.linea-oferta');
            if (fila) {
                fila.remove();
                recalcularPack();
            }
        }
    });

    // listeners para las líneas que vienen ya renderizadas desde PHP
    contenedor.querySelectorAll('.linea-oferta').forEach(row => {
        row.querySelectorAll('select, input[type=number]').forEach(el => {
            el.addEventListener('change', recalcularPack);
            el.addEventListener('input',  recalcularPack);
        });
    });

    recalcularPack();
});