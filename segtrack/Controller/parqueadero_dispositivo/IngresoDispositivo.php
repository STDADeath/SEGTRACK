<?php
require_once "../backed/conexion.php";

$conexion = new Conexion();
$conn = $conexion->getConexion();

// Obtener datos del formulario
$TipoDispositivo = $_POST['TipoDispositivo'] ?? null;
$MarcaDispositivo = $_POST['MarcaDispositivo'] ?? null;
$IdFuncionario = $_POST['IdFuncionario'] ?? null;
$IdVisitante = $_POST['IdVisitante'] ?? null;
$QrDispositivo = $_POST['QrDispositivo'] ?? null; // opcional

// ✅ Validación de campos obligatorios
if (!$TipoDispositivo || !$MarcaDispositivo) {
    echo "❌ Tipo de dispositivo y marca son obligatorios.";
    exit;
}

// ✅ Validación de IDs (solo se permite 1)
if ((!$IdFuncionario && !$IdVisitante) || ($IdFuncionario && $IdVisitante)) {
    echo "❌ Debe ingresar solo un ID: Funcionario o Visitante.";
    exit;
}

// ✅ Preparamos consulta según el ID usado
if ($IdFuncionario) {
    $sql = "INSERT INTO Dispositivo (QrDispositivo, TipoDispositivo, MarcaDispositivo, IdFuncionario) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $QrDispositivo = $QrDispositivo ?: null;
    $stmt->bind_param("sssi", $QrDispositivo, $TipoDispositivo, $MarcaDispositivo, $IdFuncionario);
} else {
    $sql = "INSERT INTO Dispositivo (QrDispositivo, TipoDispositivo, MarcaDispositivo, IdVisitante) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $QrDispositivo = $QrDispositivo ?: null;
    $stmt->bind_param("sssi", $QrDispositivo, $TipoDispositivo, $MarcaDispositivo, $IdVisitante);
}

// ✅ Ejecutamos
if ($stmt->execute()) {
    echo "✅ Dispositivo agregado correctamente.";
} else {
    echo "❌ Error al registrar dispositivo: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
