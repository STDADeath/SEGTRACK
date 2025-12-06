<?php
// ==========================================
// IMPORTANTE: NO DEBE HABER NINGÚN ECHO ANTES
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 0 para producción

// Limpiar cualquier salida previa
ob_start();

// Iniciar sesión
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
    // Incluir modelo
    require_once __DIR__ . '/../Model/modulousuario.php';

    // Obtener datos
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
    $nuevoRol = isset($_POST['rol']) ? trim($_POST['rol']) : '';

    // Validaciones básicas
    if (empty($correo) || empty($contrasena)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Por favor llena todos los campos'
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'El formato del correo no es válido'
        ]);
        exit;
    }

    // Validar login
    $usuarioModel = new ModuloUsuario();
    $resultado = $usuarioModel->validarLogin($correo, $contrasena);

    if (!$resultado['ok']) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'message' => $resultado['message']
        ]);
        exit;
    }

    // Actualizar rol si es necesario
    if (!empty($nuevoRol) && $nuevoRol !== $resultado['usuario']['TipoRol']) {
        $usuarioModel->actualizarRol($resultado['usuario']['IdFuncionario'], $nuevoRol);
        $resultado['usuario']['TipoRol'] = $nuevoRol;
    }

    // Guardar en sesión
    $_SESSION['usuario'] = $resultado['usuario'];
    $_SESSION['usuario_id'] = $resultado['usuario']['IdFuncionario'];
    $_SESSION['usuario_nombre'] = $resultado['usuario']['NombreFuncionario'];
    $_SESSION['usuario_rol'] = $resultado['usuario']['TipoRol'];

    // Bloque corregido en controladorlogin.php (PHP)
// ...
// Determinar ruta
    $ruta = '';
    switch ($resultado['usuario']['TipoRol']) {
        case 'Administrador':
            // Correcto: Salir de /Login/ (../) y entrar a /Administrador/
            $ruta = '../Administrador/DasboardAdministrador.php';
            break;
        case 'Supervisor':
            // Correcto: Salir de /Login/ (../) y entrar a /Supervisor/
            $ruta = '../Supervisor/DasboardSupervisor.php';
            break;
        case 'Personal Seguridad':
            // Correcto: Salir de /Login/ (../) y entrar a /PersonalSeguridad/
            $ruta = '../PersonalSeguridad/DasboardPersonalSeguridad.php';
            break;
        default:
            // Si no tiene rol válido, se queda en la misma página de login
            $ruta = 'login.php';
            break;
    }


    // Limpiar buffer y enviar JSON
    ob_end_clean();
    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' => 'Login exitoso',
        'usuario' => [
            'IdFuncionario' => $resultado['usuario']['IdFuncionario'],
            'NombreFuncionario' => $resultado['usuario']['NombreFuncionario'],
            'Correo' => $resultado['usuario']['Correo'] ?? $correo,
            'TipoRol' => $resultado['usuario']['TipoRol']
        ],
        'redirect' => $ruta
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>