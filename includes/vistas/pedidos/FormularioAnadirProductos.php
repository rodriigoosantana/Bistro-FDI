<?php
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';

class FormularioAnadirProductos extends formularioBase
{
    private $pedidoId;

    public function __construct($pedidoId)
    {
        $this->pedidoId = $pedidoId;
        parent::__construct('formAnadirProductos', ['urlRedireccion' => RUTA_VISTAS . '/pedidos/pedidoslist.php']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $categorias = CategoriaService::listarTodas();
        $productos = ProductoService::listarTodos();

        $html = "<h3>Selecciona los productos para tu pedido</h3>";
        $html .= "<input type='hidden' name='pedidoId' value='{$this->pedidoId}' />";

        foreach ($categorias as $cat) {
            $html .= "<fieldset class='categoria-productos'>";
            $html .= "<legend>{$cat->getNombre()}</legend>";
            
            $hayProductos = false;
            foreach ($productos as $prod) {
                if ($prod->getCategoriaId() === $cat->getId() && $prod->isDisponible() && $prod->isActivo()) {
                    $hayProductos = true;
                    $prodId = $prod->getId();
                    $nombre = $prod->getNombre();
                    $precio = number_format($prod->getPrecioFinal(), 2, ',', '.') . " €";
                    
                    $html .= "<div class='producto-seleccion'>";
                    $html .= "<span class='producto-nombre'>{$nombre}</span>";
                    $html .= "<span class='producto-precio'>{$precio}</span>";
                    $html .= "<div class='producto-cantidad'>";
                    $html .= "<label>Cantidad:</label>";
                    $html .= "<input type='number' name='prod_{$prodId}' value='0' min='0' class='input-cantidad' />";
                    $html .= "</div>";
                    $html .= "</div>";
                }
            }
            
            if (!$hayProductos) {
                $html .= "<p>No hay productos disponibles en esta categoría.</p>";
            }
            
            $html .= "</fieldset>";
        }

        $html .= "<div class='acciones-formulario'>";
        $html .= "<button type='submit' name='confirmar' class='btn-confirmar'>Confirmar Pedido</button>";
        $html .= "</div>";

        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $pedidoId = intval($datos['pedidoId'] ?? 0);
        $pedido = PedidoService::buscarPorId($pedidoId);

        if (!$pedido) {
            $this->errores[] = "El pedido no existe.";
            return;
        }

        $productos = ProductoService::listarTodos();
        $totalPedido = 0.0;
        $productosAnadidos = 0;

        foreach ($productos as $prod) {
            $prodId = $prod->getId();
            $campoCantidad = "prod_{$prodId}";
            if (isset($datos[$campoCantidad])) {
                $cantidad = intval($datos[$campoCantidad]);
                if ($cantidad > 0) {
                    $precioUnitario = $prod->getPrecioFinal();
                    if (PedidoDB::insertarProductoPedido($pedidoId, $prodId, $cantidad, $precioUnitario)) {
                        $totalPedido += $precioUnitario * $cantidad;
                        $productosAnadidos++;
                    } else {
                        $this->errores[] = "Error al añadir el producto: {$prod->getNombre()}";
                    }
                }
            }
        }

        if ($productosAnadidos === 0 && count($this->errores) === 0) {
            $this->errores[] = "Debes añadir al menos un producto al pedido.";
            return;
        }

        if (count($this->errores) === 0) {
            $pedido->setTotal($totalPedido);
            $pedido->setEstado(Estado::Recibido); // Cambiamos a Recibido al confirmar
            if (!PedidoService::actualizar($pedido)) {
                $this->errores[] = "Error al actualizar el total del pedido.";
            }
        }
    }
}
?>
