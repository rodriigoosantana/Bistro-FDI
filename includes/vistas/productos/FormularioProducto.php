<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\common\FormularioBase;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Producto\Producto;


class FormularioProducto extends FormularioBase
{
  //region Campos privados
  private ?Producto $producto; #null = crear, Producto = editar
  //endregion

  //region Constructor
  public function __construct($producto = null)
  {
    $this->producto = $producto; #Si es null es crear, si es Producto es editar
    parent::__construct(
      'formProducto',
      [
        'urlRedireccion' => RUTA_VISTAS . '/productos/productoslist.php',
        'enctype' => 'multipart/form-data'
      ]
    ); #enctype necesario para subir archivos (imágenes)
  }
  //endregion

  //region Métodos protegidos
  protected function generaCamposFormulario(&$datos)
  {
    $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
    $tituloForm          = $this->producto ? 'Editar producto' : 'Nuevo producto';

    $camposTexto     = $this->generarCamposTexto($datos);
    $camposComerciales = $this->generarCamposComerciales($datos);
    $camposEstado    = $this->generarCamposEstado($datos);
    $camposImagenes  = $this->generarCamposImagenes();

    return <<<EOF
      {$htmlErroresGlobales}

      <fieldset>
          <legend>{$tituloForm}</legend>
          <br>

          {$camposTexto}

          <br>

          {$camposComerciales}

          <br>

          {$camposEstado}
          <br>

          {$camposImagenes}
          <br>

          <div>
              <button type="submit" name="guardar">Guardar producto</button>
          </div>
      </fieldset>
EOF;
  }

  private function generarCamposTexto(array &$datos): string
  {
    $nombre      = $datos['nombre']      ?? ($this->producto ? $this->producto->getNombre()      : '');
    $descripcion = $datos['descripcion'] ?? ($this->producto ? $this->producto->getDescripcion() : '');
    $errores     = self::generaErroresCampos(['nombre', 'descripcion'], $this->errores);

    return <<<EOF
    <div>
        <label for="nombre">Nombre del producto <span class="required-mark">*</span>:</label><br>
        <input id="nombre" type="text" name="nombre" value="{$nombre}" required />
        {$errores['nombre']}
    </div>

    <br>

    <div>
        <label for="descripcion">Descripción <span class="required-mark">*</span>:</label><br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50" required>{$descripcion}</textarea>
        {$errores['descripcion']}
    </div>
    EOF;
  }

  private function generarCamposComerciales(array &$datos): string
  {
    $categoriaId = $datos['categoriaId'] ?? ($this->producto ? $this->producto->getCategoriaId() : '');
    $precioBase  = $datos['precioBase']  ?? ($this->producto ? $this->producto->getPrecioBase()  : '');
    $iva         = $datos['iva']         ?? ($this->producto ? $this->producto->getIva()         : 10);
    $errores     = self::generaErroresCampos(['categoriaId', 'precioBase', 'iva'], $this->errores);

    $categorias          = CategoriaService::listarTodas();
    $opcionesCategorias  = '<option value="">-- Selecciona una categoría --</option>';
    if ($categorias) {
      foreach ($categorias as $cat) {
        $selected           = ($cat->getId() == $categoriaId) ? 'selected' : '';
        $nombreCat          = htmlspecialchars($cat->getNombre());
        $opcionesCategorias .= "<option value=\"{$cat->getId()}\" {$selected}>{$nombreCat}</option>";
      }
    }

    $valoresIva  = [0, 4, 10, 21];
    $opcionesIva = '';
    foreach ($valoresIva as $v) {
      $selected     = ($iva == $v) ? 'selected' : '';
      $opcionesIva .= "<option value=\"{$v}\" {$selected}>{$v}%</option>\n";
    }

    return <<<EOF
    <div>
        <label for="categoriaId">Categoría <span class="required-mark">*</span>:</label><br>
        <select id="categoriaId" name="categoriaId" required>
            {$opcionesCategorias}
        </select>
        {$errores['categoriaId']}
    </div>

    <br>

    <div>
        <label for="precioBase">Precio base (€) <span class="required-mark">*</span>:</label><br>
        <input id="precioBase" type="number" name="precioBase" value="{$precioBase}" step="0.01" min="0" required />
        {$errores['precioBase']}
    </div>

    <br>

    <div>
        <label for="iva">IVA (%) <span class="required-mark">*</span>:</label><br>
        <select id="iva" name="iva" required>
          {$opcionesIva}
        </select>
        {$errores['iva']}
    </div>
    EOF;
  }

  private function generarCamposEstado(array &$datos): string
  {
    $disponible = isset($datos['formId']) ? isset($datos['disponible']) : ($this->producto ? $this->producto->isDisponible() : true);
    $ofertado   = isset($datos['formId']) ? isset($datos['ofertado'])   : ($this->producto ? $this->producto->isOfertado()   : false);
    $activo     = isset($datos['formId']) ? isset($datos['activo'])     : ($this->producto ? $this->producto->isActivo()     : true);

    $checkedDisponible  = $disponible ? 'checked' : '';
    $checkedOfertado    = $ofertado   ? 'checked' : '';
    $checkedActivo      = $activo     ? 'checked' : '';
    $disabledOfertado   = 'disabled'; # ofertado se gestiona automáticamente desde OfertaService
    $disabledDisponible = (!$activo)  ? 'disabled' : '';

    return <<<EOF
    <div>
        <label for="disponible">
            <input type="checkbox" id="disponible" name="disponible" value="1" {$checkedDisponible} {$disabledDisponible} />
            Disponible
        </label>
    </div>

    <br>

    <div>
        <label for="ofertado">
            <input type="checkbox" id="ofertado" name="ofertado" value="1" {$checkedOfertado} {$disabledOfertado} />
            Ofertado
        </label>
    </div>

    <br>

    <div>
        <label for="activo">
            <input type="checkbox" id="activo" name="activo" value="1" {$checkedActivo} />
            Activo
        </label>
    </div>
    EOF;
  }

  private function generarCamposImagenes(): string
  {
    $htmlImagenesActuales = '';
    if ($this->producto) {
      $imagenes = ProductoService::listarImagenes($this->producto->getId());
      if ($imagenes) {
        $htmlImagenesActuales .= '<p><strong>Imágenes actuales:</strong></p>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">';
        foreach ($imagenes as $img) {
          $ruta                  = htmlspecialchars(RUTA_APP . $img['ruta_imagen']);
          $htmlImagenesActuales .= "<figure style=\"margin:0;\">
                    <img src=\"{$ruta}\" width=\"100\" alt=\"Imagen actual\">
                    <figcaption style=\"font-size:0.8em; text-align:center;\">Actual</figcaption>
                    </figure>";
        }
        $htmlImagenesActuales .= '</div><p><small>Si subes imágenes nuevas, las actuales serán reemplazadas.</small></p>';
      }
    }

    return <<<EOF
    <div>
          {$htmlImagenesActuales}
          <label for="imagenes">Imágenes del producto (jpg, png, webp):</label><br>
          <input id="imagenes" type="file" name="imagenes[]" accept="image/*" multiple />
    </div>
    EOF;
  }

  protected function procesaFormulario(&$datos)
  {
    $this->errores = []; #Se inicializan los errores

    // Validar nombre
    $nombre = trim($datos['nombre'] ?? '');
    $nombre = strip_tags($nombre);
    if (!$nombre || strlen($nombre) < 3) {
      $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
    }

    // Validar descripción
    $descripcion = trim($datos['descripcion'] ?? '');
    $descripcion = strip_tags($descripcion);
    if (!$descripcion || strlen($descripcion) < 8) {
      $this->errores['descripcion'] = 'La descripción debe tener al menos 8 caracteres.';
    }

    // Validar categoría
    $categoriaId = intval($datos['categoriaId'] ?? 0);
    if ($categoriaId <= 0) {
      $this->errores['categoriaId'] = 'Debes seleccionar una categoría.';
    } else {
      $cat = CategoriaService::buscarPorId($categoriaId);
      if (!$cat) {
        $this->errores['categoriaId'] = 'La categoría seleccionada no existe.';
      }
    }

    // Validar precio base
    $precioBase = floatval($datos['precioBase'] ?? 0);
    if ($precioBase <= 0) {
      $this->errores['precioBase'] = 'El precio debe ser mayor que 0.';
    }

    // Validar IVA: solo valores legales en España
    $iva = intval($datos['iva'] ?? -1);
    if (!in_array($iva, [0, 4, 10, 21])) {
      $this->errores['iva'] = 'El IVA debe ser 0%, 4%, 10% o 21%.';
    }

    // Checkboxes -> Si el form fue enviado, leer del POST; si no, leer del producto
    if (isset($datos['formId'])) {
      $disponible = isset($datos['disponible']);
      # ofertado no se lee del POST porque es de solo lectura; ofertado lo controla OfertaService automáticamente
      $ofertado = $this->producto ? $this->producto->isOfertado() : false;
      $activo   = isset($datos['activo']);
    } else {
      $disponible = $this->producto ? $this->producto->isDisponible() : true;
      $ofertado   = $this->producto ? $this->producto->isOfertado()   : false;
      $activo     = $this->producto ? $this->producto->isActivo()     : true;
    }
    if (!$activo) {
      $disponible = false;
    }

    $imagenes = (!empty($_FILES['imagenes']['name'][0])) ? $_FILES['imagenes'] : null;
    $app      = Aplicacion::getInstance();

    if (count($this->errores) === 0) {
      if ($this->producto) {

        $this->producto = ProductoService::buscarPorId($this->producto->getId());
        if (!$this->producto) {
          $this->errores[] = 'Error: el producto no existe.';
          return;
        }

        if (!ProductoService::actualizar($this->producto->getId(), $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $imagenes)) {
          $this->errores[] = 'Error al actualizar el producto.';
        } else {
          $app->putAtributoPeticion('mensajes', ['Producto actualizado correctamente.']);
        }
      } else {
        $producto = ProductoService::crear($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $imagenes);
        if (!$producto) {
          $this->errores[] = 'Error al crear el producto.';
        } else {
          $app->putAtributoPeticion('mensajes', ['Producto creado correctamente.']);
        }
      }
    }
  }

  //endregion
}
