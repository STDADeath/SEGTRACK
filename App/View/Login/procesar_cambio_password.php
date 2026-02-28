<?php
// Habilitar reporte de errores para depuración (eliminar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Incluir conexión a base de datos
    require_once '../../Core/conexion.php';

    // Verificar datos recibidos
    if (!isset($_POST['correo']) || !isset($_POST['token']) || !isset($_POST['nueva_password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan datos obligatorios'
        ]);
        exit;
    }

    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $token = trim($_POST['token']);
    $nueva_password = $_POST['nueva_password'];

    // Validaciones
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Correo electrónico no válido'
        ]);
        exit;
    }

    if (strlen($token) !== 6 || !is_numeric($token)) {
        echo json_encode([
            'success' => false,
            'message' => 'Token inválido'
        ]);
        exit;
    }

    if (strlen($nueva_password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña debe tener al menos 6 caracteres'
        ]);
        exit;
    }

    // Verificar token y que no haya expirado (CONSULTA CORREGIDA CON JOIN)
    $stmt = $conexion->prepare("
        SELECT u.IdUsuario, u.TokenExpiracion 
        FROM usuario u
        INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
        WHERE f.CorreoFuncionario = ? AND u.TokenRecuperacion = ? AND u.Estado = 'Activo'
    ");
    $stmt->execute([$correo, $token]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Token inválido o correo incorrecto'
        ]);
        exit;
    }

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el token ha expirado
    if (strtotime($usuario['TokenExpiracion']) < time()) {
        echo json_encode([
            'success' => false,
            'message' => 'El token ha expirado. Solicita uno nuevo.'
        ]);
        exit;
    }

    // Encriptar nueva contraseña
    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

    // Actualizar contraseña y limpiar token (NOMBRES DE COLUMNAS CORREGIDOS)
    $stmt = $conexion->prepare("
        UPDATE usuario 
        SET Contrasena = ?, 
            TokenRecuperacion = NULL, 
            TokenExpiracion = NULL 
        WHERE IdUsuario = ?
    ");

    if ($stmt->execute([$password_hash, $usuario['IdUsuario']])) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente. Ya puedes iniciar sesión.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la contraseña'
        ]);
    }

} catch (PDOException $e) {
    // Log del error para depuración
    error_log("Error PDO: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos. Intenta nuevamente.'
    ]);
} catch (Exception $e) {
    // Log del error para depuración
    error_log("Error general: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Contacta al administrador.'
    ]);
}
?>