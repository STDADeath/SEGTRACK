<?php
header('Content-Type: application/json');

require_once "conexion.php";

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de dispositivo inválido']);
    exit;
}

try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
    
    // Preparar la consulta SQL
    $sql = "DELETE FROM Dipositivo WHERE IdDipositivo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Dispositivo eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'El dispositivo no existe o ya fue eliminado']);
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