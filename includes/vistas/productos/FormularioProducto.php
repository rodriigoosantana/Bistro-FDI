<?php
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Producto/ProductoService.php';
require_once RAIZ_APP . '/includes/Producto/Categoria.php';

class FormularioProducto extends formularioBase
{
    //region Campos privados
    private $producto; #null = crear, Producto = editar
    //endregion

    //region Constructor
    public function __construct($producto = null)
    {
        $this->producto = $producto; #Si es null es crear, si es Producto es editar
        parent::__construct('formProducto', ['urlRedireccion' => RUTA_VISTAS . '/productoslist.php']); #Redirección a la lista de productos
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
        $categorias = Categoria::listarTodas();
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

        $tituloForm = $this->producto ? 'Editar producto' : 'Nuevo producto'; #Si el producto existe, se marca como editar, si no, como nuevo

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
              <label>
                  <input type="checkbox" name="disponible" value="1" {$checkedDisponible} />
                  Disponible
              </label>
          </div>

          <br>

          <div>
              <label>
                  <input type="checkbox" name="ofertado" value="1" {$checkedOfertado} />
                  Ofertado
              </label>
          </div>

          <br>

          <div>
              <label>
                  <input type="checkbox" name="activo" value="1" {$checkedActivo} />
                  Activo
              </label>
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
        $nombre = filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS); #Se limpia el nombre
        if (!$nombre || strlen($nombre) < 3) { #Se valida que el nombre tenga al menos 3 caracteres
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        // Validar descripción
        $descripcion = trim($datos['descripcion'] ?? '');
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$descripcion || strlen($descripcion) < 8) {
            $this->errores['descripcion'] = 'La descripción debe tener al menos 8 caracteres.';
        }

        // Validar categoría
        $categoriaId = intval($datos['categoriaId'] ?? 0);
        if ($categoriaId <= 0) {
            $this->errores['categoriaId'] = 'Debes seleccionar una categoría.';
        }
        else {
            $cat = Categoria::buscarPorId($categoriaId);
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

        // Checkboxes
        $disponible = isset($datos['disponible']) ? true : false;
        $ofertado = isset($datos['ofertado']) ? true : false;
        $activo = isset($datos['activo']) ? true : false;

        if (count($this->errores) === 0) { #Si no hay errores
            if ($this->producto) { #Si el producto existe
                // Editar producto existente
                $this->producto = ProductoService::buscarPorId($this->producto->getId());
                if (!$this->producto) {
                    $this->errores[] = 'Error: el producto no existe.';
                    return;
                }
                // Actualizar campos del DTO
                $this->producto->setNombre($nombre);
                $this->producto->setDescripcion($descripcion);
                $this->producto->setCategoriaId($categoriaId);
                $this->producto->setPrecioBase($precioBase);
                $this->producto->setIva($iva);
                $this->producto->setDisponible($disponible);
                $this->producto->setOfertado($ofertado);
                $this->producto->setActivo($activo);

                if (!ProductoService::actualizar($this->producto)) {
                    $this->errores[] = 'Error al actualizar el producto.';
                }
            }
            else {
                // Crear nuevo producto (DTO)
                $dto = new Producto($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
                $producto = ProductoService::crear($dto);
                if (!$producto) {
                    $this->errores[] = 'Error al crear el producto.';
                }
            }
        }
    }

//endregion
}
?>
