<?php

namespace es\ucm\fdi\aw\vistas\productos;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\vistas\common\formularioBase;
use es\ucm\fdi\aw\Producto\ProductoService;
use es\ucm\fdi\aw\Producto\CategoriaService;
use es\ucm\fdi\aw\Producto\Producto;


class FormularioProducto extends formularioBase
{
  //region Campos privados
  private ?Producto $producto; #null = crear, Producto = editar
  //endregion

  //region Constructor
  public function __construct($producto = null)
  {
    $this->producto = $producto; #Si es null es crear, si es Producto es editar
    #Redirección a la lista de productos
    parent::__construct(
      'formProducto',
      [
        'urlRedireccion' => RUTA_VISTAS . '/productoslist.php',
        'enctype' => 'multipart/form-data'
      ]
    ); #enctype necesario para subir archivos (imágenes)
  }
  //endregion

  //region Métodos protegidos
  protected function generaCamposFormulario(&$datos)
  {
    // Valores por defecto: del producto existente o vacíos
    $nombre = $datos['nombre'] ?? ($this->producto ? $this->producto->getNombre() : '');
    $descripcion = $datos['descripcion'] ?? ($this->producto ? $this->producto->getDescripcion() : '');
    $categoriaId = $datos['categoriaId'] ?? ($this->producto ? $this->producto->getCategoriaId() : '');
    $precioBase = $datos['precioBase'] ?? ($this->producto ? $this->producto->getPrecioBase() : '');
    $iva = $datos['iva'] ?? ($this->producto ? $this->producto->getIva() : 10);
    $disponible = isset($datos['formId']) ? isset($datos['disponible']) : ($this->producto ? $this->producto->isDisponible() : true);
    $ofertado = isset($datos['formId']) ? isset($datos['ofertado']) : ($this->producto ? $this->producto->isOfertado() : false);
    $activo = isset($datos['formId']) ? isset($datos['activo']) : ($this->producto ? $this->producto->isActivo() : true);

    // Generar errores
    $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
    $erroresCampos = self::generaErroresCampos(
      ['nombre', 'descripcion', 'categoriaId', 'precioBase', 'iva'],
      $this->errores
    );

    // Cargar categorías para el select
    $categorias = CategoriaService::listarTodas();
    $opcionesCategorias = '<option value="">-- Selecciona una categoría --</option>';
    if ($categorias) {
      foreach ($categorias as $cat) {
        $selected = ($cat->getId() == $categoriaId) ? 'selected' : ''; #Si la categoría coincide con la del producto, se marca como seleccionada
        $nombreCat = htmlspecialchars($cat->getNombre());
        $opcionesCategorias .= "<option value=\"{$cat->getId()}\" {$selected}>{$nombreCat}</option>"; #Se añade la categoría al select
      }
    }

    // Checked attributes para checkboxes
    $checkedDisponible = $disponible ? 'checked' : '';
    $checkedOfertado = $ofertado ? 'checked' : '';
    $checkedActivo = $activo ? 'checked' : '';
    $disabledDisponible = (!$activo) ? 'disabled' : '';

    $tituloForm = $this->producto ? 'Editar producto' : 'Nuevo producto'; #Si el producto existe, se marca como editar, si no, como nuevo

    $htmlImagenesActuales = '';
    if ($this->producto) {
      $imagenes = ProductoService::listarImagenes($this->producto->getId()); #Guardar array de imágenes actuales
      if ($imagenes) {
        $htmlImagenesActuales .= '<p><strong>Imágenes actuales:</strong></p>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">';
        foreach ($imagenes as $img) {
          $ruta = htmlspecialchars(RUTA_APP . $img['ruta_imagen']); #Obtener ruta de la imagen
          $htmlImagenesActuales .= "<figure style=\"margin:0;\">
                    <img src=\"{$ruta}\" width=\"100\" alt=\"Imagen actual\">
                    <figcaption style=\"font-size:0.8em; text-align:center;\">Actual</figcaption>
                    </figure>"; #Mostrar imagen actual con un caption indicando que es la imagen actual
        }
        $htmlImagenesActuales .= '</div><p><small>Si subes imágenes nuevas, las actuales serán reemplazadas.</small></p>'; #Aviso al user
      }
    }

    $html = <<<EOF
      {$htmlErroresGlobales}

      <fieldset>
          <legend>{$tituloForm}</legend>
          <br>

          <div>
              <label for="nombre">Nombre del producto:</label><br> 
              <input id="nombre" type="text" name="nombre" value="{$nombre}" required />
              {$erroresCampos['nombre']}
          </div>

          <br>

          <div>
              <label for="descripcion">Descripción:</label><br>
              <textarea id="descripcion" name="descripcion" rows="4" cols="50" required>{$descripcion}</textarea>
              {$erroresCampos['descripcion']}
          </div>

          <br>

          <div>
              <label for="categoriaId">Categoría:</label><br>
              <select id="categoriaId" name="categoriaId" required>
                  {$opcionesCategorias}
              </select>
              {$erroresCampos['categoriaId']}
          </div>

          <br>

          <div>
              <label for="precioBase">Precio base (€):</label><br>
              <input id="precioBase" type="number" name="precioBase" value="{$precioBase}" step="0.01" min="0" required />
              {$erroresCampos['precioBase']}
          </div>

          <br>

          <div>
              <label for="iva">IVA (%):</label><br>
              <input id="iva" type="number" name="iva" value="{$iva}" min="0" max="100" required />
              {$erroresCampos['iva']}
          </div>

          <br>

          <div>
              <label for="disponible">
                  <input type="checkbox" id="disponible" name="disponible" value="1" {$checkedDisponible} {$disabledDisponible} />
                  Disponible
              </label>
          </div>

          <br>

          <div>
              <label for="ofertado">
                  <input type="checkbox" id="ofertado" name="ofertado" value="1" {$checkedOfertado} />
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
          <br>

          <div>
                {$htmlImagenesActuales}
                <label for="imagenes">Imágenes del producto (jpg, png, webp):</label><br>
                <input id="imagenes" type="file" name="imagenes[]" accept="image/*" multiple />
          </div>
          <br>

          <div>
              <button type="submit" name="guardar">Guardar producto</button>
          </div>
      </fieldset>
EOF;
    return $html;
  }

  protected function procesaFormulario(&$datos)
  {
    $this->errores = []; #Se inicializan los errores

    // Validar nombre
    $nombre = trim($datos['nombre'] ?? ''); #Se obtiene el nombre del producto (si no existe, se deja vacío)
    $nombre = strip_tags($nombre);
    if (!$nombre || strlen($nombre) < 3) { #Se valida que el nombre tenga al menos 3 caracteres
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

    // Validar IVA
    $iva = intval($datos['iva'] ?? -1);
    if ($iva < 0 || $iva > 100) {
      $this->errores['iva'] = 'El IVA debe estar entre 0 y 100.';
    }

    // Checkboxes -> Si el form fue enviado, leer del POST; si no, leer del producto
    if (isset($datos['formId'])) {
      $disponible = isset($datos['disponible']);
      $ofertado = isset($datos['ofertado']);
      $activo = isset($datos['activo']);
    } else {
      $disponible = $this->producto ? $this->producto->isDisponible() : true;
      $ofertado = $this->producto ? $this->producto->isOfertado() : false;
      $activo = $this->producto ? $this->producto->isActivo() : true;
    }
    if (!$activo) {
      $disponible = false;
    }

    $imagenes = (!empty($_FILES['imagenes']['name'][0])) ? $_FILES['imagenes'] : null; #si se han subido imágenes, se obtienen del array $_FILES, si no, se dejan como null para no modificar las imágenes actuales
    $app = Aplicacion::getInstance(); #para poder usar los atributos de petición y mostrar mensajes flash

    //Bloque de persistencia: si no hay errores, se crea o actualiza el producto según corresponda
    if (count($this->errores) === 0) {
      if ($this->producto) { #Si el producto existe

        // Editar producto existente
        $this->producto = ProductoService::buscarPorId($this->producto->getId());
        if (!$this->producto) {
          $this->errores[] = 'Error: el producto no existe.';
          return;
        }

        // Actualizar campos del DTO
        if (!ProductoService::actualizar($this->producto->getId(), $nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $imagenes)) {
          $this->errores[] = 'Error al actualizar el producto.';
        }
        // Agregar mensaje flash de éxito
        else {
          $app->putAtributoPeticion('mensajes', ['Producto actualizado correctamente.']);
        }
      } else {
        // Crear nuevo producto (DTO)
        $producto = ProductoService::crear($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo, $imagenes);
        if (!$producto) {
          $this->errores[] = 'Error al crear el producto.';
        }
        // Agregar mensaje flash de éxito
        else {
          $app->putAtributoPeticion('mensajes', ['Producto creado correctamente.']);
        }
      }
    }
  }

  //endregion
}
