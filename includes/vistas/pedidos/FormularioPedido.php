<?php
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';

class FormularioPedido extends formularioBase
{
    // region Campos privados
    private $pedido; # null = crear, Pedido = editar
    // endregion

    // region Constructor
    public function __construct($pedido = null)
    {
        $this->pedido = $pedido; # Si es null es crear, si es Pedido es editar
        parent::__construct('formPedido'); 
    }
    // endregion

    // region Métodos protegidos
    protected function generaCamposFormulario(&$datos)
    {
        // Valores por defecto: del pedido existente o vacíos
        $tipo = $datos['tipo'] ?? ($this->pedido ? $this->pedido->getTipo()->value : 'local');

        // Generar errores
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['tipo'],
            $this->errores
        );

        $tituloForm = $this->pedido ? 'Editar pedido' : 'Nuevo pedido';

        $checkedLocal = ($tipo === 'local') ? 'checked' : '';
        $checkedLlevar = ($tipo === 'llevar') ? 'checked' : '';

        $html = <<<EOF
    {$htmlErroresGlobales}

    <fieldset>
        <legend>{$tituloForm}</legend>
        <br>

        <div>
            <label>Tipo de pedido:</label><br>
            <label>
                <input type="radio" name="tipo" value="local" {$checkedLocal} />
                Para tomar aquí
            </label>
            <br>
            <label>
                <input type="radio" name="tipo" value="llevar" {$checkedLlevar} />
                Para llevar
            </label>
            {$erroresCampos['tipo']}
        </div>

        <br>

        <div>
            <button type="submit" name="guardar">Continuar a productos</button>
        </div>
    </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $tipoVal = $datos['tipo'] ?? 'local';
        $tipo = Tipo::from($tipoVal);

        if (count($this->errores) === 0) {
            if ($this->pedido) {
                // Editar pedido existente
                $this->pedido->setTipo($tipo);
                if (!PedidoService::actualizar($this->pedido)) {
                    $this->errores[] = 'Error al actualizar el pedido.';
                } else {
                    $this->urlRedireccion = RUTA_VISTAS . '/pedidos/pedidoslist.php';
                }
            } else {
                // Crear nuevo pedido
                $numero_pedido = 1;
                $fecha_creacion = new DateTime('now');
                $ultimo_pedido_hoy = PedidoService::obtenerUltimoPedidoDelDia($fecha_creacion);
                if ($ultimo_pedido_hoy !== null) {
                    $numero_pedido = $ultimo_pedido_hoy->getNumeroPedido() + 1;
                }

                $estado = Estado::Nuevo;
                $cliente_id = $_SESSION['userId'];
                $cocinero_id = null; // Aún no tiene cocinero asignado
                $total = 0.0;

                $dto = new Pedido($numero_pedido, $fecha_creacion, $estado, $tipo, $cliente_id, $cocinero_id, $total);
                $pedido = PedidoService::crear($dto);
                if (!$pedido || !$pedido->getId()) {
                    $this->errores[] = 'Error al crear el pedido o obtener su ID.';
                } else {
                    $this->urlRedireccion = RUTA_VISTAS . '/pedidos/anadir_productos.php?id=' . $pedido->getId();
                }
            }
        }
    }

    //endregion
}
?>
