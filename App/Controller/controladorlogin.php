<?php
// ==========================================
// IMPORTANTE: NO DEBE HABER NINGÚN ECHO ANTES
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1); // Cambiar a 0 en producción

ob_start();
session_start();

// Headers SOLO JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

try {

    require_once __DIR__ . '/../Model/modulousuario.php';

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $nuevoRol = trim($_POST['rol'] ?? '');

    if (empty($correo) || empty($contrasena)) {
        throw new Exception('Por favor llena todos los campos', 400);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo no es válido', 400);
    }

    $usuarioModel = new ModuloUsuario();
    $resultado = $usuarioModel->validarLogin($correo, $contrasena);

    if (!$resultado['ok']) {
        throw new Exception($resultado['message'], 401);
    }

    $usuario = $resultado['usuario'];

    // Actualizar rol si se envía uno diferente
    if (!empty($nuevoRol) && $nuevoRol !== $usuario['TipoRol']) {
        $usuarioModel->actualizarRol($usuario['IdFuncionario'], $nuevoRol);
        $usuario['TipoRol'] = $nuevoRol;
    }

    // ==============================
    // GUARDAR SESIÓN (SOLO UNA ESTRUCTURA LIMPIA)
    // ==============================
    $_SESSION['usuario'] = [
        'IdFuncionario'     => $usuario['IdFuncionario'],
        'NombreFuncionario' => $usuario['NombreFuncionario'],
        'Correo'            => $usuario['Correo'] ?? $correo,
        'TipoRol'           => $usuario['TipoRol']
    ];

    // ==============================
    // REDIRECCIÓN SEGÚN ROL
    // ==============================
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
            throw new Exception('Rol no válido', 403);
    }

    ob_end_clean();
    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' => 'Login exitoso',
        'redirect' => $ruta,
        'usuario' => $_SESSION['usuario']
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    ob_end_clean();
    http_response_code($e->getCode() ?: 500);

    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;