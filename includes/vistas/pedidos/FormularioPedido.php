<?php

namespace es\ucm\fdi\aw\vistas\pedidos;

use es\ucm\fdi\aw\vistas\common\FormularioBase;
use es\ucm\fdi\aw\Pedido\PedidoService;
use es\ucm\fdi\aw\Pedido\PedidoDB;
use es\ucm\fdi\aw\Pedido\Pedido;
use \DateTime;
use es\ucm\fdi\aw\Pedido\Tipo;
use es\ucm\fdi\aw\Pedido\Estado;

class FormularioPedido extends FormularioBase
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

    <div class="form-pedido">
        <div class="form-header">
            <h3>{$tituloForm}</h3>
            <p>Elige cómo quieres consumir el pedido.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Tipo de pedido <span class="required-mark">*</span></label>
            <div class="pedido-opciones">
                <label class="pedido-opcion">
                    <input type="radio" name="tipo" value="local" {$checkedLocal} required />
                    <div class="pedido-opcion-body">
                        <span class="pedido-opcion-titulo">Para tomar aquí</span>
                        <span class="pedido-opcion-desc">Se sirve en mesa del local.</span>
                    </div>
                    <span class="pedido-opcion-badge">Local</span>
                </label>
                <label class="pedido-opcion">
                    <input type="radio" name="tipo" value="llevar" {$checkedLlevar} required />
                    <div class="pedido-opcion-body">
                        <span class="pedido-opcion-titulo">Para llevar</span>
                        <span class="pedido-opcion-desc">Listo para recoger en barra.</span>
                    </div>
                    <span class="pedido-opcion-badge">Takeaway</span>
                </label>
            </div>
            {$erroresCampos['tipo']}
        </div>

        <div>
            <button type="submit" name="guardar" class="btn btn-nuevo">Continuar a productos</button>
        </div>
    </div>
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
        $this->urlRedireccion = RUTA_VISTAS . '/pedidos/pedidosadd.php?tipo=' . urlencode($tipo->value);
      }
    }
  }

  //endregion
}
