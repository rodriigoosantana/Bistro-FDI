<?php

namespace es\ucm\fdi\aw\vistas\ofertas;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\common\formularioBase;
use es\ucm\fdi\aw\Oferta\Oferta;
use es\ucm\fdi\aw\Oferta\OfertaService;
use es\ucm\fdi\aw\Producto\ProductoService;
use DateTime;

class FormularioOferta extends formularioBase
{
    # null si estamos creando, Oferta si estamos editando
    private ?Oferta $oferta;

    public function __construct(?Oferta $oferta = null)
    {
        $this->oferta = $oferta;
        parent::__construct(
            'formOferta',
            ['urlRedireccion' => RUTA_VISTAS . '/ofertas/ofertaslist.php']
        );
    }

    protected function generaCamposFormulario(&$datos)
    {
        # valores por defecto: del objeto existente o vacíos
        $nombre      = htmlspecialchars($datos['nombre']      ?? ($this->oferta?->getNombre()               ?? ''));
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->oferta?->getDescripcion()          ?? ''));
        $inicio      = $datos['inicio'] ?? ($this->oferta ? $this->oferta->getInicio()->format('Y-m-d') : '');
        $fin         = $datos['fin']    ?? ($this->oferta ? $this->oferta->getFin()->format('Y-m-d')    : '');

        # descuento: almacenamos como decimal (0.215), mostramos como porcentaje (21.5)
        $descuento   = floatval($datos['descuento'] ?? ($this->oferta?->getDescuento() ?? 0));
        $descuentoPct = $descuento > 0 ? number_format($descuento * 100, 2, '.', '') : '';

        # errores de cada campo
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $errores = self::generaErroresCampos(
            ['nombre', 'descripcion', 'inicio', 'fin', 'descuento', 'lineas'],
            $this->errores
        );

        # cargamos los productos activos para el selector de líneas
        $productos = ProductoService::listarActivos();
        $opcionesProductos = '<option value="">-- Producto --</option>';
        $preciosJs = '{}'; # objeto JS: { id: precioConIva, ... }
        if ($productos) {
            $mapa = [];
            foreach ($productos as $p) {
                $nomP = htmlspecialchars($p->getNombre());
                $opcionesProductos .= "<option value=\"{$p->getId()}\">{$nomP}</option>";
                $mapa[$p->getId()] = round($p->getPrecioFinal(), 4);
            }
            $preciosJs = json_encode($mapa);
        }

        # líneas existentes (edición) o una línea vacía (creación)
        $lineasHtml = '';
        if ($this->oferta && !isset($datos['formId'])) {
            # modo edición sin reenvío: cargamos las líneas guardadas
            $lineasGuardadas = OfertaService::listarLineasDeOferta($this->oferta->getId());
            foreach ($lineasGuardadas as $l) {
                $lineasHtml .= $this->htmlLinea($opcionesProductos, $l->getProductoId(), $l->getCantidad());
            }
        } elseif (isset($datos['lineas_producto'])) {
            # reenvío del formulario con errores: recuperar lo enviado
            $prods = $datos['lineas_producto'] ?? [];
            $cants = $datos['lineas_cantidad'] ?? [];
            foreach ($prods as $i => $pid) {
                $lineasHtml .= $this->htmlLinea($opcionesProductos, intval($pid), intval($cants[$i] ?? 1));
            }
        } else {
            # formulario nuevo: una línea vacía
            $lineasHtml = $this->htmlLinea($opcionesProductos);
        }

        return <<<HTML
            {$htmlErroresGlobales}

            <div class="form-oferta">

                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre"
                           value="{$nombre}" maxlength="150" required>
                    {$errores['nombre']}
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" rows="3"
                              required>{$descripcion}</textarea>
                    {$errores['descripcion']}
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inicio">Fecha de inicio *</label>
                        <input type="date" id="inicio" name="inicio"
                               value="{$inicio}" required>
                        {$errores['inicio']}
                    </div>
                    <div class="form-group">
                        <label for="fin">Fecha de fin *</label>
                        <input type="date" id="fin" name="fin"
                               value="{$fin}" required>
                        {$errores['fin']}
                    </div>
                </div>

                <!-- productos del pack con botón para añadir líneas -->
                <div class="form-group">
                    <label>Productos del pack *</label>
                    {$errores['lineas']}
                    <div id="lineas-oferta" class="lineas-oferta">
                        {$lineasHtml}
                    </div>
                    <button type="button" class="btn btn-nuevo"
                            onclick="añadirLinea()">+ Añadir producto</button>
                </div>

                <!-- caja de cálculo automático precio/descuento -->
                <div class="calc-box">
                    <label>Precio y descuento</label>
                    <p class="calc-label">
                        Precio del pack sin descuento (con IVA):
                        <strong id="precio-sin">—</strong>
                    </p>
                    <div class="calc-row">
                        <div>
                            <label for="precio-final-ui">Precio final (€)</label>
                            <input type="number" id="precio-final-ui"
                                   min="0" step="0.01" placeholder="ej: 2.00">
                        </div>
                        <span class="calc-label">⟷</span>
                        <div>
                            <label for="descuento-pct-ui">Descuento (%)</label>
                            <input type="number" id="descuento-pct-ui"
                                   min="0" max="100" step="0.01"
                                   placeholder="ej: 21.50"
                                   value="{$descuentoPct}">
                        </div>
                    </div>
                    <!-- campo real que se envía al servidor (0.215 = 21.5%) -->
                    <input type="hidden" id="descuento" name="descuento"
                           value="{$descuento}">
                    {$errores['descuento']}
                </div>

                <button type="submit" class="btn btn-nuevo">
                    {$this->textoBoton()}
                </button>
            </div>

            <!-- options plantilla para clonar nuevas líneas -->
            <template id="tpl-linea">
                <div class="linea-oferta">
                    <select name="lineas_producto[]" required>
                        {$opcionesProductos}
                    </select>
                    <input type="number" name="lineas_cantidad[]"
                           min="1" value="1" required>
                    <button type="button" class="btn-rm-linea"
                            onclick="this.closest('.linea-oferta').remove();
                                     recalcularPack();">✕</button>
                </div>
            </template>

            <script>
            // mapa producto_id → precio con iva (generado desde PHP)
            const PRECIOS = {$preciosJs};

            // devuelve la suma de precios del pack según lo seleccionado en el formulario
            function precioSinDescuento() {
                let total = 0;
                document.querySelectorAll('#lineas-oferta .linea-oferta').forEach(row => {
                    const pid  = parseInt(row.querySelector('select').value) || 0;
                    const cant = parseInt(row.querySelector('input[type=number]').value) || 0;
                    if (PRECIOS[pid] && cant > 0) total += PRECIOS[pid] * cant;
                });
                return total;
            }

            // actualiza el texto del precio sin descuento y recalcula los campos
            function recalcularPack() {
                const sin = precioSinDescuento();
                document.getElementById('precio-sin').textContent =
                    sin > 0 ? sin.toFixed(2) + ' €' : '—';

                // si ya hay un porcentaje de descuento, recalcular el precio final
                const pctInput = document.getElementById('descuento-pct-ui');
                if (pctInput.value !== '' && sin > 0) {
                    const pct = parseFloat(pctInput.value) / 100;
                    document.getElementById('precio-final-ui').value =
                        (sin * (1 - pct)).toFixed(2);
                    document.getElementById('descuento').value =
                        pct.toFixed(6);
                }
            }

            // cuando el usuario cambia el precio final, calculamos el porcentaje
            document.getElementById('precio-final-ui').addEventListener('input', function () {
                const sin = precioSinDescuento();
                if (sin <= 0) return;
                const final = parseFloat(this.value);
                if (isNaN(final) || final < 0) return;
                const pct = (sin - final) / sin;
                document.getElementById('descuento-pct-ui').value =
                    (pct * 100).toFixed(2);
                document.getElementById('descuento').value =
                    pct.toFixed(6);
            });

            // cuando el usuario cambia el porcentaje, calculamos el precio final
            document.getElementById('descuento-pct-ui').addEventListener('input', function () {
                const sin = precioSinDescuento();
                const pct = parseFloat(this.value) / 100;
                if (isNaN(pct) || pct < 0 || pct > 1) return;
                if (sin > 0) {
                    document.getElementById('precio-final-ui').value =
                        (sin * (1 - pct)).toFixed(2);
                }
                document.getElementById('descuento').value =
                    pct.toFixed(6);
            });

            // añade una nueva línea de producto al pack
            function añadirLinea() {
                const tpl   = document.getElementById('tpl-linea');
                const clone = tpl.content.cloneNode(true);
                // vincular eventos de cambio para recalcular
                clone.querySelectorAll('select, input[type=number]').forEach(el => {
                    el.addEventListener('change', recalcularPack);
                    el.addEventListener('input',  recalcularPack);
                });
                document.getElementById('lineas-oferta').appendChild(clone);
            }

            // vincular eventos a las líneas ya presentes al cargar la página
            document.querySelectorAll('#lineas-oferta .linea-oferta').forEach(row => {
                row.querySelectorAll('select, input[type=number]').forEach(el => {
                    el.addEventListener('change', recalcularPack);
                    el.addEventListener('input',  recalcularPack);
                });
            });

            // calcular al cargar la página (útil en edición)
            recalcularPack();
            </script>
        HTML;
    }

    protected function procesaFormulario(&$datos)
    {
        # validar nombre
        $nombre = trim($datos['nombre'] ?? '');
        $nombre = htmlspecialchars($nombre);
        if (strlen($nombre) < 3) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        # validar descripción
        $descripcion = trim($datos['descripcion'] ?? '');
        $descripcion = strip_tags($descripcion);
        if (strlen($descripcion) < 5) {
            $this->errores['descripcion'] = 'La descripción debe tener al menos 5 caracteres.';
        }

        # validar fechas
        $inicio = DateTime::createFromFormat('Y-m-d', $datos['inicio'] ?? '');
        $fin    = DateTime::createFromFormat('Y-m-d', $datos['fin']    ?? '');
        if (!$inicio) {
            $this->errores['inicio'] = 'Fecha de inicio no válida.';
        }
        if (!$fin) {
            $this->errores['fin'] = 'Fecha de fin no válida.';
        }
        if ($inicio && $fin && $fin < $inicio) {
            $this->errores['fin'] = 'La fecha de fin debe ser igual o posterior a la de inicio.';
        }

        # validar descuento (0 ≤ d ≤ 1)
        $descuento = floatval($datos['descuento'] ?? -1);
        if ($descuento < 0 || $descuento > 1) {
            $this->errores['descuento'] = 'El descuento debe estar entre 0 % y 100 %.';
        }

        # validar líneas: al menos una, con producto y cantidad ≥ 1
        $lineasProducto  = $datos['lineas_producto'] ?? [];
        $lineasCantidad  = $datos['lineas_cantidad']  ?? [];
        $lineas          = [];

        if (empty($lineasProducto)) {
            $this->errores['lineas'] = 'Debes añadir al menos un producto al pack.';
        } else {
            # detectar productos duplicados
            $idsVistos = [];
            foreach ($lineasProducto as $i => $pid) {
                $pid  = intval($pid);
                $cant = intval($lineasCantidad[$i] ?? 0);
                if ($pid <= 0 || $cant < 1) continue; # ignorar líneas vacías
                if (in_array($pid, $idsVistos)) {
                    $this->errores['lineas'] = 'No puede haber dos líneas con el mismo producto.';
                    break;
                }
                $idsVistos[] = $pid;
                $lineas[]    = ['producto_id' => $pid, 'cantidad' => $cant];
            }
            if (empty($lineas)) {
                $this->errores['lineas'] = 'Debes añadir al menos una línea válida.';
            }
        }

        # si hay errores no persistimos
        if (count($this->errores) > 0) return;

        $app = Aplicacion::getInstance();

        if ($this->oferta) {
            # edición: actualizar oferta y recalcular ofertado de productos
            OfertaService::actualizar(
                $this->oferta->getId(),
                $nombre, $descripcion, $inicio, $fin, $descuento, $lineas
            );
            $app->putAtributoPeticion('mensajes', ['Oferta actualizada correctamente.']);
        } else {
            # creación: nueva oferta con sus líneas
            OfertaService::crear($nombre, $descripcion, $inicio, $fin, $descuento, $lineas);
            $app->putAtributoPeticion('mensajes', ['Oferta creada correctamente.']);
        }
    }

    # texto del botón según si es creación o edición
    private function textoBoton(): string
    {
        return $this->oferta ? 'Guardar cambios' : 'Crear oferta';
    }

    # genera el html de una fila de producto para el pack
    # si se pasan $pidSeleccionado y $cantidad se prerellenan (modo edición o reenvío)
    private function htmlLinea(string $opciones, int $pidSeleccionado = 0, int $cantidad = 1): string
    {
        # reemplazar la opción seleccionada si hay pid preseleccionado
        $opcionesConSelect = $opciones;
        if ($pidSeleccionado > 0) {
            $opcionesConSelect = str_replace(
                "value=\"{$pidSeleccionado}\"",
                "value=\"{$pidSeleccionado}\" selected",
                $opciones
            );
        }

        return <<<LIN
            <div class="linea-oferta">
                <select name="lineas_producto[]" required>
                    {$opcionesConSelect}
                </select>
                <input type="number" name="lineas_cantidad[]"
                       min="1" value="{$cantidad}" required>
                <button type="button" class="btn-rm-linea"
                        onclick="this.closest('.linea-oferta').remove();
                                 recalcularPack();">✕</button>
            </div>
        LIN;
    }
}