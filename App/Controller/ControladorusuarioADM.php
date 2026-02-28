<?php

/**
 * ========================================
 * CONTROLADOR USUARIO ADM - SEGTRACK
 * ========================================
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../Model/modelousuarioAdm.php';

// Roles permitidos en el sistema
$ROLES_VALIDOS = ['Supervisor', 'Personal Seguridad', 'Administrador'];

/**
 * Envía respuesta JSON estandarizada y termina ejecución
 */
function responder(bool $success, string $message, array $extra = []): void {
    ob_end_clean();
    echo json_encode(
        array_merge(['success' => $success, 'message' => $message], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(false, 'Método no permitido');
    }

    $modelo = new Modelo_Usuario();
    $accion = trim($_POST['accion'] ?? '');

    switch ($accion) {

        // ============================================================
        // REGISTRAR USUARIO
        // ============================================================
        case 'registrar':

            $idFuncionario = (int)($_POST['id_funcionario'] ?? 0);
            $tipoRol       = trim($_POST['tipo_rol']        ?? '');
            $contrasena    = trim($_POST['contrasena']      ?? '');

            if ($idFuncionario <= 0) {
                responder(false, 'Debe seleccionar un funcionario');
            }

            if (empty($tipoRol)) {
                responder(false, 'Debe seleccionar un rol');
            }

            global $ROLES_VALIDOS;
            if (!in_array($tipoRol, $ROLES_VALIDOS)) {
                responder(false, 'Rol no válido');
            }

            if (strlen($contrasena) < 7) {
                responder(false, 'La contraseña debe tener mínimo 7 caracteres');
            }

            if ($modelo->usuarioExiste($idFuncionario)) {
                responder(false, 'El funcionario ya tiene un usuario asignado');
            }

            $ok = $modelo->registrarUsuario($tipoRol, $contrasena, $idFuncionario);

            if ($ok) {
                responder(true, 'Usuario registrado correctamente');
            }

            responder(false, 'No se pudo registrar el usuario');
            break;

        // ============================================================
        // ACTUALIZAR ROL
        // ============================================================
        case 'actualizar':

            $idUsuario = (int)($_POST['IdUsuario'] ?? 0);
            $nuevoRol  = trim($_POST['tipo_rol']   ?? '');

            if ($idUsuario <= 0) {
                responder(false, 'ID de usuario inválido');
            }

            if (empty($nuevoRol)) {
                responder(false, 'Debe seleccionar un rol');
            }

            global $ROLES_VALIDOS;
            if (!in_array($nuevoRol, $ROLES_VALIDOS)) {
                responder(false, 'Rol no válido');
            }

            $ok = $modelo->actualizarRol($idUsuario, $nuevoRol);

            if ($ok) {
                responder(true, 'Rol actualizado correctamente');
            }

            responder(false, 'No se pudo actualizar el rol');
            break;

        // ============================================================
        // CAMBIAR ESTADO (Activo / Inactivo)
        // ============================================================
        case 'cambiar_estado':

            $idUsuario  = (int)($_POST['IdUsuario'] ?? 0);
            $nuevoEstado = trim($_POST['Estado']    ?? '');

            if ($idUsuario <= 0) {
                responder(false, 'ID de usuario inválido');
            }

            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                responder(false, 'Estado inválido');
            }

            $ok = $modelo->cambiarEstado($idUsuario, $nuevoEstado);

            if ($ok) {
                responder(true, 'Estado actualizado correctamente');
            }

            responder(false, 'No se pudo cambiar el estado');
            break;

        // ============================================================
        // LISTAR USUARIOS
        // ============================================================
        case 'listar':

            $usuarios = $modelo->obtenerUsuarios();
            responder(true, 'OK', ['data' => $usuarios]);
            break;

        default:
            responder(false, 'Acción no válida: ' . $accion);
    }

} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}