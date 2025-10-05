<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '../../Conexion/conexion.php';
require_once __DIR__ . '/../../model/Funcionario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $conexion = (new Conexion())->getConexion();
    $modelo = new Funcionario($conexion);

    $datos = [
        'NombreFuncionario' => trim($_POST['NombreFuncionario'] ?? ''),
        'DocumentoFuncionario' => trim($_POST['DocumentoFuncionario'] ?? ''),
        'TelefonoFuncionario' => trim($_POST['TelefonoFuncionario'] ?? ''),
        'CorreoFuncionario' => trim($_POST['CorreoFuncionario'] ?? ''),
        'CargoFuncionario' => trim($_POST['CargoFuncionario'] ?? ''),
        'IdSede' => trim($_POST['IdSede'] ?? '')
    ];

    // Validaciones
    if (empty($datos['NombreFuncionario']) || empty($datos['DocumentoFuncionario']) || 
        empty($datos['TelefonoFuncionario']) || empty($datos['CorreoFuncionario']) || 
        empty($datos['CargoFuncionario']) || empty($datos['IdSede'])) {
        echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
        exit;
    }

    if (strlen($datos['NombreFuncionario']) > 30) {
        echo json_encode(['success' => false, 'error' => 'El nombre no debe superar 30 caracteres']);
        exit;
    }

    if (strlen($datos['DocumentoFuncionario']) > 12) {
        echo json_encode(['success' => false, 'error' => 'El documento no debe superar 12 dígitos']);
        exit;
    }

    if (!ctype_digit($datos['DocumentoFuncionario'])) {
        echo json_encode(['success' => false, 'error' => 'El documento debe contener solo números']);
        exit;
    }

    if (strlen($datos['TelefonoFuncionario']) != 10) {
        echo json_encode(['success' => false, 'error' => 'El teléfono debe tener exactamente 10 dígitos']);
        exit;
    }

    if (!ctype_digit($datos['TelefonoFuncionario'])) {
        echo json_encode(['success' => false, 'error' => 'El teléfono debe contener solo números']);
        exit;
    }

    if (!filter_var($datos['CorreoFuncionario'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'El correo electrónico no es válido']);
        exit;
    }

    if (strlen($datos['CorreoFuncionario']) > 80) {
        echo json_encode(['success' => false, 'error' => 'El correo no debe superar 80 caracteres']);
        exit;
    }

    if ($modelo->existeDocumento($datos['DocumentoFuncionario'])) {
        echo json_encode(['success' => false, 'error' => "Ya existe un funcionario con el documento {$datos['DocumentoFuncionario']}"]);
        exit;
    }

    if ($modelo->existeCorreo($datos['CorreoFuncionario'])) {
        echo json_encode(['success' => false, 'error' => "Ya existe un funcionario con el correo {$datos['CorreoFuncionario']}"]);
        exit;
    }

    if (!$modelo->existeSede($datos['IdSede'])) {
        echo json_encode(['success' => false, 'error' => 'La sede seleccionada no existe']);
        exit;
    }

    $resultado = $modelo->insertar($datos);

    if ($resultado['success']) {
        $funcionario = $modelo->obtenerPorId($resultado['id']);
        echo json_encode([
            'success' => true,
            'message' => 'Funcionario registrado correctamente',
            'data' => $funcionario
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>