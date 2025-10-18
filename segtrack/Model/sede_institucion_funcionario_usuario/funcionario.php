<?php
header('Content-Type: application/json');
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Capturar datos usando exactamente los mismos name del formulario
$nombre    = $_POST['Nombre']    ?? '';
$documento = $_POST['Documento'] ?? '';
$telefono  = $_POST['Telefono']  ?? '';
$correo    = $_POST['Correo']    ?? '';
$cargo     = $_POST['Cargo']     ?? '';
$idSede    = $_POST['IdSede']    ?? null;

// Validaciones
if (empty($nombre) || empty($documento) || empty($telefono) || empty($correo) || empty($cargo)) {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos obligatorios']);
    exit;
}

try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();

    // Si se pasa IdSede, validar que exista
    if (!empty($idSede)) {
        $check = $conn->prepare("SELECT IdSede FROM sede WHERE IdSede = ?");
        $check->bind_param("i", $idSede);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'La sede seleccionada no existe']);
            exit;
        }
        $check->close();
    }

    // Insertar (si IdSede es opcional, se maneja null)
    $sql = "INSERT INTO funcionario (Nombre, Documento, Telefono, Correo, Cargo, IdSede) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $documento, $telefono, $correo, $cargo, $idSede);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Funcionario registrado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar funcionario: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}
