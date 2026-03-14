<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    require_once __DIR__ . '/../Model/modulousuario.php';

    $correo     = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $nuevoRol   = trim($_POST['rol'] ?? '');

    if (empty($correo) || empty($contrasena)) {
        throw new Exception('Por favor llena todos los campos.');
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo no es válido.');
    }

    $usuarioModel = new ModuloUsuario();
    $resultado    = $usuarioModel->validarLogin($correo, $contrasena);

    if (!$resultado['ok']) {
        throw new Exception($resultado['message']);
    }

    $usuario = $resultado['usuario'];

    if (!isset($usuario['Estado']) || $usuario['Estado'] !== 'Activo') {
        session_destroy();
        throw new Exception('Tu cuenta está inactiva. Contacta al administrador.');
    }

    if (!empty($nuevoRol) && $nuevoRol !== $usuario['TipoRol']) {
        $usuarioModel->actualizarRol($usuario['IdFuncionario'], $nuevoRol);
        $usuario['TipoRol'] = $nuevoRol;
    }

    // ✅ SESIÓN CON FOTO
    $_SESSION['usuario'] = [
        'IdFuncionario'     => $usuario['IdFuncionario'],
        'NombreFuncionario' => $usuario['NombreFuncionario'],
        'Correo'            => $usuario['CorreoFuncionario'] ?? $correo,
        'TipoRol'           => $usuario['TipoRol'],
        'Estado'            => $usuario['Estado'],
        'FotoFuncionario'   => $usuario['FotoFuncionario'] ?? ''
    ];

    switch ($usuario['TipoRol']) {
        case 'Personal Seguridad':
            $ruta = '../PersonalSeguridad/DasboardPersonalSeguridad.php';
            break;
        case 'Supervisor':
            $ruta = '../Supervisor/DasboardSupervisor.php';
            break;
        case 'Administrador':
            $ruta = '../Administrador/DasboardAdministrador.php';
            break;
        default:
            session_destroy();
            throw new Exception('Rol no autorizado.');
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'ok'      => true,
        'message' => 'Bienvenido, ' . $usuario['NombreFuncionario'],
        'redirect' => $ruta,
        'usuario' => [
            'NombreFuncionario' => $usuario['NombreFuncionario'],
            'TipoRol'           => $usuario['TipoRol']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;