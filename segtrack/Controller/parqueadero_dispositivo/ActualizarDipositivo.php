<?php
header('Content-Type: application/json');

require_once "conexion.php";

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar datos
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$qr = isset($_POST['qr']) ? trim($_POST['qr']) : '';
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$marca = isset($_POST['marca']) ? trim($_POST['marca']) : '';
$id_funcionario = isset($_POST['id_funcionario']) ? intval($_POST['id_funcionario']) : 0;
$id_visitante = isset($_POST['id_visitante']) ? intval($_POST['id_visitante']) : 0;

// Validaciones básicas
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de dispositivo inválido']);
    exit;
}

if (empty($qr) || empty($tipo) || empty($marca)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
    
    // Preparar la consulta SQL
    $sql = "UPDATE Dipositivo SET 
            QrDipositivo = ?, 
            tipoDipositivo = ?, 
            Marca = ?, 
            IdFuncionario = ?, 
            IdVisitante = ? 
            WHERE IdDipositivo = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $qr, $tipo, $marca, $id_funcionario, $id_visitante, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Dispositivo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios o el dispositivo no existe']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}
?>