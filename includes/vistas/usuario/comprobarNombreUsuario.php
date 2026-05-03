<?php

require_once dirname(__DIR__, 3) . '/includes/config.php';

use es\ucm\fdi\aw\Usuario\UsuarioService;

header('Content-Type: application/json; charset=utf-8');

$nombreUsuario = trim($_GET['nombreUsuario'] ?? '');
$original = trim($_GET['original'] ?? '');

if ($nombreUsuario === '' || mb_strlen($nombreUsuario) < 4) {
  echo json_encode([
    'available' => false,
    'reason' => 'short'
  ]);
  exit;
}

$nombreUsuarioNormalizado = mb_strtolower($nombreUsuario);
$originalNormalizado = mb_strtolower($original);

if ($originalNormalizado !== '' && $nombreUsuarioNormalizado === $originalNormalizado) {
  echo json_encode([
    'available' => true,
    'reason' => 'same'
  ]);
  exit;
}

$usuarioExistente = UsuarioService::buscarPorNombre($nombreUsuario);

echo json_encode([
  'available' => $usuarioExistente === null,
  'reason' => $usuarioExistente === null ? 'available' : 'exists'
]);
