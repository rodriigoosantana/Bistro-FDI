<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\Producto\ProductoService;

class GenerarAddProductos
{
    public static function generar(
        array  $listaCategorias,
        array  $listaProductos,
        array  $cantidadesEnCarrito,
        int    $totalUnidadesCarrito,
        ?int   $categoriaFiltro,
        array  $categoriasConProductos,
        array  $parametrosBaseFiltro,
        string $hiddenPedidoOTipo,
        string $tituloPedido,
        string $urlMiCarrito,
        string $mensajeExito,
        string $mensajeError
    ): string {
        $htmlNotificacionExito  = $mensajeExito ? "<p class='msg-success'>{$mensajeExito}</p>" : '';
        $htmlNotificacionError  = $mensajeError ? "<p class='msg-error'>{$mensajeError}</p>"   : '';
        $htmlNavCategorias      = self::generarNavCategorias($listaCategorias, $categoriaFiltro, $categoriasConProductos, $parametrosBaseFiltro);
        $htmlNavegadorProductos = self::generarNavegadorProductos($listaCategorias, $listaProductos, $cantidadesEnCarrito, $categoriaFiltro, $hiddenPedidoOTipo);
        $urlJsPedidos           = RUTA_JS . '/pedidos.js';

        return <<<EOS
        <section id="pedido-shopping">
            <div class="header-shopping">
                <h2>{$tituloPedido}</h2>
                <a href="{$urlMiCarrito}" class="btn btn-ver" id="btn-mi-carrito" data-total-unidades="{$totalUnidadesCarrito}">Ir a mi carrito ({$totalUnidadesCarrito})</a>
            </div>
            {$htmlNotificacionExito}
            {$htmlNotificacionError}

            <div class="product-browser">
                {$htmlNavCategorias}
                {$htmlNavegadorProductos}
            </div>
        </section>
        <script src="{$urlJsPedidos}"></script>
        EOS;
    }

    private static function generarNavCategorias(
        array $listaCategorias,
        ?int  $categoriaFiltro,
        array $categoriasConProductos,
        array $parametrosBaseFiltro
    ): string {
        $queryTodas = http_build_query($parametrosBaseFiltro);
        $urlTodas   = RUTA_VISTAS . '/pedidos/pedidosadd.php' . ($queryTodas !== '' ? '?' . $queryTodas : '');
        $claseTodas = $categoriaFiltro === null ? 'btn btn-ver' : 'btn btn-volver';

        $enlaces = '<a href="' . htmlspecialchars($urlTodas) . '" class="' . $claseTodas . '">Todas</a>';

        foreach ($listaCategorias as $categoria) {
            if (!$categoria->isActiva() || !isset($categoriasConProductos[$categoria->getId()])) {
                continue;
            }

            $params              = $parametrosBaseFiltro;
            $params['categoria'] = $categoria->getId();
            $urlCat              = RUTA_VISTAS . '/pedidos/pedidosadd.php?' . http_build_query($params);
            $claseBtn            = $categoriaFiltro === $categoria->getId() ? 'btn btn-ver' : 'btn btn-volver';
            $nombre              = htmlspecialchars($categoria->getNombre());
            $enlaces            .= ' <a href="' . htmlspecialchars($urlCat) . '" class="' . $claseBtn . '">' . $nombre . '</a>';
        }

        return '<div class="nav-categorias">' . $enlaces . '</div>';
    }

    private static function generarNavegadorProductos(
        array  $listaCategorias,
        array  $listaProductos,
        array  $cantidadesEnCarrito,
        ?int   $categoriaFiltro,
        string $hiddenPedidoOTipo
    ): string {
        $html = '';
        foreach ($listaCategorias as $categoria) {
            if (!$categoria->isActiva()) continue;
            if ($categoriaFiltro !== null && $categoria->getId() !== $categoriaFiltro) continue;

            $productosHtml = '';
            foreach ($listaProductos as $producto) {
                if ($producto->getCategoriaId() === $categoria->getId()
                    && $producto->isDisponible()
                    && $producto->isActivo()
                ) {
                    $productosHtml .= self::generarTarjetaProducto($producto, $cantidadesEnCarrito, $hiddenPedidoOTipo);
                }
            }

            if ($productosHtml !== '') {
                $nombreCat = htmlspecialchars($categoria->getNombre());
                $html     .= <<<HTML
                <div class="categoria-section">
                    <h3>{$nombreCat}</h3>
                    <div class="productos-grid">
                        {$productosHtml}
                    </div>
                </div>
                HTML;
            }
        }
        return $html;
    }

    private static function generarTarjetaProducto(
        $producto,
        array  $cantidadesEnCarrito,
        string $hiddenPedidoOTipo
    ): string {
        $idProducto       = $producto->getId();
        $nombreProducto   = htmlspecialchars($producto->getNombre());
        $precioFormateado = number_format($producto->getPrecioFinal(), 2, ',', '.') . ' €';
        $htmlImagen       = self::generarImagenProducto($idProducto, $nombreProducto);
        $urlVer           = htmlspecialchars(RUTA_VISTAS . '/productos/productosdetail.php?id=' . $idProducto);
        $cantidadActual   = intval($cantidadesEnCarrito[$idProducto] ?? 0);
        $mostrarBotonAdd  = $cantidadActual <= 0 ? '' : ' is-hidden';
        $mostrarInput     = $cantidadActual >  0 ? '' : ' is-hidden';

        return <<<HTML
        <div class="producto-card">
            <div class="tarjeta-imagen">{$htmlImagen}</div>
            <div class="producto-info">
                <strong>{$nombreProducto}</strong>
                <span class="precio">{$precioFormateado}</span>
            </div>
            <a href="{$urlVer}" class="btn btn-ver">Ver</a>
            <form method="POST" class="form-add-cart">
                {$hiddenPedidoOTipo}
                <input type="hidden" name="productoId" value="{$idProducto}" />
                <input type="hidden" name="accion" value="set_cantidad" />
                <button type="submit" class="btn btn-nuevo btn-add-cart{$mostrarBotonAdd}">Anadir al carrito</button>
                <input type="number" name="cantidad" value="{$cantidadActual}" min="0" class="input-mini input-cantidad-cart{$mostrarInput}" />
            </form>
        </div>
        HTML;
    }

    private static function generarImagenProducto(int $productoId, string $nombreProducto): string
    {
        $imagenes = ProductoService::listarImagenes($productoId);

        if (!$imagenes) {
            return '<div class="tarjeta-sin-imagen"><em>Sin imagen</em></div>';
        }

        $primeraRuta = htmlspecialchars(RUTA_APP . $imagenes[0]['ruta_imagen']);

        if (count($imagenes) === 1) {
            return "<img class=\"tarjeta-img-unica\" src=\"{$primeraRuta}\" alt=\"{$nombreProducto}\">";
        }

        $rutas        = array_map(fn($img) => htmlspecialchars(RUTA_APP . $img['ruta_imagen']), $imagenes);
        $dataImagenes = htmlspecialchars(json_encode($rutas));
        $dotsHtml     = '';
        foreach ($imagenes as $i => $img) {
            $active    = $i === 0 ? ' active' : '';
            $dotsHtml .= "<span class=\"slider-dot{$active}\"></span>";
        }

        return '<div class="slider-wrap tarjeta-slider" data-imagenes="' . $dataImagenes . '" data-auto="true">'
            . '<img class="slider-img" src="' . $primeraRuta . '" alt="' . $nombreProducto . '">'
            . '<div class="slider-dots">' . $dotsHtml . '</div>'
            . '</div>';
    }
}
