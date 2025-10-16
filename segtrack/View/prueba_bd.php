<?php
echo "Probando conexión a MySQL...\n\n";

try {
    $conexion = new PDO(
        "mysql:host=localhost;dbname=seggtack;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "✅ CONEXIÓN EXITOSA\n\n";
    
    // Mostrar BD actual
    $resultado = $conexion->query("SELECT DATABASE();")->fetch();
    echo "Base de datos: " . $resultado['DATABASE()'] . "\n";
    
    // Mostrar tablas
    echo "\nTablas en la BD:\n";
    $tablas = $conexion->query("SHOW TABLES;")->fetchAll();
    foreach ($tablas as $tabla) {
        echo "  - " . reset($tabla) . "\n";
    }
    
    // Probar tabla dispositivo
    echo "\nEstructura de tabla 'dispositivo':\n";
    $estructura = $conexion->query("DESCRIBE dispositivo;")->fetchAll();
    foreach ($estructura as $campo) {
        echo "  - " . $campo['Field'] . " (" . $campo['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    // Sugerencias según el error
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "POSIBLE PROBLEMA: La base de datos 'seggtrack' no existe\n";
    } elseif (strpos($e->getMessage(), "Access denied") !== false) {
        echo "POSIBLE PROBLEMA: Usuario o contraseña incorrectos\n";
    } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
        echo "POSIBLE PROBLEMA: MySQL no está corriendo o el host es incorrecto\n";
    }
}
?>