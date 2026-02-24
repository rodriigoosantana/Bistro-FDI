<?php
require_once dirname(__DIR__,2).'/includes/config.php';
require_once dirname(__DIR__,2).'/includes/vistas/common/utils.php';

$username = filter_input(INPUT_POST, 'nombreUsuario', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$erroresFormulario = [];

// Validación de campos
if (!$username || strlen(trim($username)) < 4) {
    $erroresFormulario['username'] = 'El nombre de usuario no puede estar vacío o tener longitud menor a 4';
}
if (!$password || strlen(trim($password)) < 4) {
    $erroresFormulario['password'] = 'El password no puede estar vacío o tener longitud menor a 4';
}

// Solo intentamos login si no hay errores de validación
if (count($erroresFormulario) === 0) {
  $usuario = Usuario::login($username, $password);

  if (!$usuario) {
    $erroresFormulario[] = "El usuario o el password no coinciden";
  }
  else {
    $_SESSION['login'] = true;
    $_SESSION['nombre'] = $usuario.getNombre();
    $_SESSION['esAdmin'] = $usuario->tieneRol(Usuario::ADMIN_ROLE);
    
    header('Location:' . RUTA_APP . '/index.php');

    exit();
  }
}

// Generamos contenido principal según sesión y errores
if (isset($_SESSION["login"])) {
    $contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>Bienvenido {$_SESSION['nombre']}</h2>
        <p>Usa el menú de la izquierda para navegar.</p>
    </section>
EOS;
} else {
    $erroresGlobalesFormulario = generaErroresGlobalesFormulario($erroresFormulario);
    $contenidoPrincipal = <<<EOS
    <section id="contenido">
        <h2>ERROR</h2>
        <p>El usuario o contraseña no son válidos.</p>
        $erroresGlobalesFormulario
    </section>
EOS;
}

$tituloPagina = 'Login';
$tituloHeader = 'Login';

require __DIR__ . "/common/plantilla.php";
?>
