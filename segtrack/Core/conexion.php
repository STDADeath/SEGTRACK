<?php
/**
 * ✅ Archivo de conexión a la base de datos
 * Se encarga de crear el objeto $conexion (PDO)
 */

$host = "localhost";
$dbname = "seggtack"; // 👈 Verifica que sea el nombre correcto en phpMyAdmin
$user = "root";
$pass = "";

try {
    // Creamos la conexión PDO
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Configuramos el modo de error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si ocurre un error, enviamos respuesta JSON
    echo json_encode([
        'success' => false,
        'message' => '❌ Error al conectar con la base de datos: ' . $e->getMessage()
    ]);
    exit;
}
?>
