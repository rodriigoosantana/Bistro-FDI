<?php
require_once RAIZ_APP . '/includes/vistas/common/FormularioBase.php';
require_once RAIZ_APP . '/includes/Producto/CategoriaService.php';

class FormularioCategoria extends FormularioBase
{
    # Region campos privados 
    private ?Categoria $categoria;
    # Endregion

    # Region constructor
    public function __construct(Categoria $categoria = null)
    {
        $this->categoria = $categoria; # Si es null es crear, si es Categoria es editar
        parent::__construct(
            'formCategoria', 
            [
                'urlRediraccion' => RUTA_VISTAS . '/categoriaslist.php',
                'enctype' => 'multipart/form-data']);
    }# Enctype necesario para subir archivos (imágenes)
    # Endregion

    # Region métodos protegidos
    protected function generaCamposFormulario(&$datos): string
    {
    
     # Valores por defecto: de la categoría existente o vacíos
        $nombre      = $datos['nombre'] ?? ($this->categoria ? $this->categoria->getNombre()      : '');
        $descripcion = $datos['descripcion'] ?? ($this->categoria ? $this->categoria->getDescripcion() : '');
        $activa      = isset($datos['formId']) ? isset($datos['activa']) : ($this->categoria ? $this->categoria->isActiva() : true);

        # Generar errores
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['nombre', 'descripcion', 'imagen'],
            $this->errores
        );

        # Checkbox
        $checkedActiva = $activa ? 'checked' : '';

        # Título del formulario
        $tituloForm = $this->categoria ? 'Editar categoría' : 'Nueva categoría';

        # Imagen actual (solo en edición)
        $htmlImagenActual = '';
        if ($this->categoria && $this->categoria->getImagen()) {
            $rutaImagen = RUTA_APP . htmlspecialchars($this->categoria->getImagen());
            $htmlImagenActual = <<<IMG
            <div>
                <p><strong>Imagen actual:</strong></p>
                <img src="{$rutaImagen}" alt="Imagen de la categoría" width="100" />
                <p><small>Si subes una imagen nueva, esta será reemplazada.</small></p>
            </div>
            <br>
IMG;
        }

        $html = <<<EOF
        {$htmlErroresGlobales}

        <fieldset>
            <legend>{$tituloForm}</legend>
            <br>

            <div>
                <label for="nombre">Nombre de la categoría:</label><br>
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
                <label>
                    <input type="checkbox" name="activa" value="1" {$checkedActiva} />
                    Activa
                </label>
            </div>

            <br>

            {$htmlImagenActual}

            <div>
                <label for="imagen">Imagen de la categoría (jpg, png, webp) - opcional:</label><br>
                <input id="imagen" type="file" name="imagen" accept="image/*" />
                {$erroresCampos['imagen']}
            </div>

            <br>

            <div>
                <button type="submit" name="guardar">Guardar categoría</button>
            </div>
        </fieldset>
    EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        # Validar nombre
        $nombre = trim($datos['nombre'] ?? '');
        $nombre = strip_tags($nombre);
        if (!$nombre || strlen($nombre) < 3) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        # Validar descripción
        $descripcion = trim($datos['descripcion'] ?? '');
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$descripcion || strlen($descripcion) < 8) {
            $this->errores['descripcion'] = 'La descripción debe tener al menos 8 caracteres.';
        }

        # Checkbox
        $activa = isset($datos['activa']) ? true : false;

        # Imagen: solo si se subió un fichero
        $fichero = (!empty($_FILES['imagen']['name'])) ? $_FILES['imagen'] : null;

        # Validar extensión si se subió imagen
        if ($fichero) {
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower(pathinfo($fichero['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $extensionesPermitidas)) {
                $this->errores['imagen'] = 'La imagen debe ser jpg, png o webp.';
            }
        }

        if (count($this->errores) === 0) {
            if ($this->categoria) {
                # Editar categoría existente
                if (!CategoriaService::actualizar(
                    $this->categoria->getId(),
                    $nombre,
                    $descripcion,
                    $fichero,
                    $activa
                )) {
                    $this->errores[] = 'Error al actualizar la categoría.';
                }
            } else {
                # Crear nueva categoría
                $categoria = CategoriaService::crear($nombre, $descripcion, $fichero, $activa);
                if (!$categoria) {
                    $this->errores[] = 'Error al crear la categoría.';
                }
            }
        }
    }
    //endregion
}
?>