<?php
header("Content-Type: application/json");

$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// Aquí deberías conectar con la base de datos y validar
if ($correo === "admin@mail.com" && $contrasena === "Admin123!") {
    echo json_encode([
        "success" => true,
        "usuario" => "Administrador",
        "rol" => "admin"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Correo o contraseña incorrectos"
    ]);
}
