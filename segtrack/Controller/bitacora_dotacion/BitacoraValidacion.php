<?php
require_once "../backed/conexion.php";  // Asegúrate que la ruta sea correcta

// Crear conexión
$conn = (new Conexion())->getConexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir datos del formulario
    $IdBitacora    = $_POST["IdBitacora"];
    $Turno         = $_POST["Turno"];
    $Novedades     = $_POST["Novedades"];
    $IdFuncionario  = $_POST["IdFuncionario"];
    $IdIngreso     = $_POST["IdIngreso"];
    $IdDispositivo  = $_POST["IdDispositivo"];

    

    // Preparar consulta SQL
    $sql = "INSERT INTO bitacora (IdBitacora, Turno, Novedades, IdFuncionario, IdIngreso, IdDispositivo)
        VALUES (?, ?, ?, ?, ?, ?)";
                    

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("❌ Error en prepare: " . $conn->error);
    }

    // Vincular parámetros
    $stmt->bind_param("sssii", 
        $IdBitacora, 
        $Turno, 
        $Novedades, 
        $IdFuncionario, 
        $IdIngreso,
        $IdDispositivo
    );
    
    // Ejecutar
    if ($stmt->execute()) {
        echo "<div style='color: green; font-weight: bold;'>✅ Bitacora registrada correctamente</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no permitido.";
}
?>
