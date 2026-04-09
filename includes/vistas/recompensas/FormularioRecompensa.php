<?php

namespace es\ucm\fdi\aw\vistas\recompensas;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\common\formularioBase;
use es\ucm\fdi\aw\Recompensa\Recompensa;
use es\ucm\fdi\aw\Recompensa\RecompensaService;
use es\ucm\fdi\aw\Producto\ProductoService;

class FormularioRecompensa extends formularioBase
{
  private ?Recompensa $recompensa;

  public function __construct($recompensa = null)
  {
    $this->recompensa = $recompensa;

    parent::__construct(
      'formRecompensa',
      [
        'urlRedireccion' => RUTA_VISTAS . '/recompensas/listaRecompensas.php'
      ]
    );
  }

  protected function generaCamposFormulario(&$datos)
  {
    $productoId = $datos['productoId'] ?? ($this->recompensa ? $this->recompensa->getProductoId() : '');
    $bistrocoins = $datos['bistrocoins'] ?? ($this->recompensa ? $this->recompensa->getBistrocoinsNecesarias() : '');

    $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
    $erroresCampos = self::generaErroresCampos(
      ['productoId', 'bistrocoins'],
      $this->errores
    );

    // Cargar productos
    $productos = ProductoService::listarTodos();
    $opcionesProductos = '<option value="">-- Selecciona un producto --</option>';

    if ($productos) {
      foreach ($productos as $p) {
        $selected = ($p->getId() == $productoId) ? 'selected' : '';
        $nombre = htmlspecialchars($p->getNombre());
        $opcionesProductos .= "<option value=\"{$p->getId()}\" {$selected}>{$nombre}</option>";
      }
    }

    $tituloForm = $this->recompensa ? 'Editar recompensa' : 'Nueva recompensa';

    return <<<HTML
    {$htmlErroresGlobales}

    <fieldset>
        <legend>{$tituloForm}</legend>

        <div>
            <label for="productoId">Producto:</label><br>
            <select id="productoId" name="productoId" required>
                {$opcionesProductos}
            </select>
            {$erroresCampos['productoId']}
        </div>

        <br>

        <div>
            <label for="bistrocoins">BistroCoins necesarias:</label><br>
            <input id="bistrocoins" type="number" name="bistrocoins" value="{$bistrocoins}" min="1" required />
            {$erroresCampos['bistrocoins']}
        </div>

        <br>

        <div>
            <button type="submit">Guardar recompensa</button>
        </div>
    </fieldset>
    HTML;
  }

  protected function procesaFormulario(&$datos)
  {
    $this->errores = [];

    $productoId = intval($datos['productoId'] ?? 0);
    $bistrocoins = intval($datos['bistrocoins'] ?? 0);

    // Validaciones
    if ($productoId <= 0) {
      $this->errores['productoId'] = 'Debes seleccionar un producto.';
    }

    if ($bistrocoins <= 0) {
      $this->errores['bistrocoins'] = 'Debe ser mayor que 0.';
    }

    $existe = RecompensaService::existePorProductoId($productoId);

    if ($this->recompensa) {
      if (
        $productoId != $this->recompensa->getProductoId() &&
        $existe
      ) {
        $this->errores['productoId'] = 'Este producto ya tiene recompensa.';
      }
    } else {
      if ($existe) {
        $this->errores['productoId'] = 'Este producto ya tiene recompensa.';
      }
    }

    $app = Aplicacion::getInstance();

    if (count($this->errores) === 0) {

      if ($this->recompensa) {
        // EDITAR
        $r = new Recompensa(
          $productoId,
          $bistrocoins,
          $this->recompensa->getId()
        );

        if (!RecompensaService::actualizar($r)) {
          $this->errores[] = 'Error al actualizar la recompensa.';
        } else {
          $app->putAtributoPeticion('mensajes', ['Recompensa actualizada correctamente.']);
        }
      } else {
        // CREAR
        $r = new Recompensa($productoId, $bistrocoins);
        if (!RecompensaService::insertar($r)) {

          $this->errores[] = 'Error al crear la recompensa.';
        } else {
          $app->putAtributoPeticion('mensajes', ['Recompensa creada correctamente.']);
        }
      }
    }
  }
}
