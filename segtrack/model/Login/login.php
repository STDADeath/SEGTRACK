<?php
require_once(__DIR__ . '/../../../Core/conexion.php'); // Ruta corregida: 3 niveles arriba

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST["correo_o_documento"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $conexion->prepare("SELECT * FROM funcionario WHERE Correo = :input OR Documento = :input");
    $stmt->bindParam(":input", $input);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo "Inicio de sesión exitoso. Bienvenido, " . htmlspecialchars($usuario['Nombre']);
    } else {
        echo "Correo o documento no encontrado.";
    }
}
?>