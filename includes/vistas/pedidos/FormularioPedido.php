<?php
require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/Pedido/PedidoService.php';
require_once RAIZ_APP . '/includes/Pedido/Categoria.php';

class FormularioPedido extends formularioBase
{
	// region Campos privados
	private $pedido; #null = crear, Pedido = editar
	// endregion

	// region Constructor
	public function __construct($pedido = null)
	{
		$this->pedido = $pedido; #Si es null es crear, si es Pedido es editar
		parent::__construct('formPedido', ['urlRedireccion' => RUTA_VISTAS . '/pedidoslist.php']); #Redirección a la lista de pedidos
	}
	// endregion

	// region Métodos protegidos
	protected function generaCamposFormulario(&$datos)
	{
		// Valores por defecto: del pedido existente o vacíos
		$tipo = $datos['tipo'] ?? ($this->pedido ? $this->pedido->getTipo() : 'local');

		// Generar errores
		$htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
		$erroresCampos = self::generaErroresCampos(
			['tipo'],
			$this->errores
		);

		$tituloForm = $this->pedido ? 'Editar pedido' : 'Nuevo pedido'; #Si el pedido existe, se marca como editar, si no, como nuevo

		$html = <<<EOF
	{$htmlErroresGlobales}

	<fieldset>
		<legend>{$tituloForm}</legend>
		<br>

		<div>
			<label>
				<input type="radio" name="tipo" value="local" {$tipo} />
				Local
			</label>
			<br>
			<label>
				<input type="radio" name="tipo" value="llevar" {$tipo} />
				Llevar
			</label>
		</div>

		<br>

		<div>
			<button type="submit" name="guardar">Guardar pedido</button>
		</div>
	</fieldset>
EOF;
		return $html;
	}

	protected function procesaFormulario(&$datos)
	{
		$this->errores = []; #Se inicializan los errores

		$numero_pedido = 1;
		$fecha_creacion = new DateTime('now');
		$ultimo_pedido_hoy = PedidoDB::obtenerUltimoPedidoDelDia($fecha_creacion);
		if ($ultimo_pedido_hoy !== null) {
			$numero_pedido = $ultimo_pedido_hoy->getNumeroPedido() + 1;
		}

		// Validar nombre
		$nombre = trim($datos['nombre'] ?? ''); #Se obtiene el nombre del pedido (si no existe, se deja vacío)
		$nombre = strip_tags($nombre); 
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
			if ($this->pedido) { #Si el pedido existe
				// Editar pedido existente
				$this->pedido = PedidoService::buscarPorId($this->pedido->getId());
				if (!$this->pedido) {
					$this->errores[] = 'Error: el pedido no existe.';
					return;
				}
				// Actualizar campos del DTO
				$this->pedido->setNombre($nombre);
				$this->pedido->setDescripcion($descripcion);
				$this->pedido->setCategoriaId($categoriaId);
				$this->pedido->setPrecioBase($precioBase);
				$this->pedido->setIva($iva);
				$this->pedido->setDisponible($disponible);
				$this->pedido->setOfertado($ofertado);
				$this->pedido->setActivo($activo);

				if (!PedidoService::actualizar($this->pedido)) {
					$this->errores[] = 'Error al actualizar el pedido.';
				}
			}
			else {
				// Crear nuevo pedido (DTO)
				$dto = new Pedido($nombre, $descripcion, $categoriaId, $precioBase, $iva, $disponible, $ofertado, $activo);
				$pedido = PedidoService::crear($dto);
				if (!$pedido) {
					$this->errores[] = 'Error al crear el pedido.';
				}
			}
		}
	}

	//endregion
}
?>
