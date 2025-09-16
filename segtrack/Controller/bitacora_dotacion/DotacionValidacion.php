<?php
require_once "../backed/conexion.php";  // Asegúrate que la ruta sea correcta

// Crear conexión
$conn = (new Conexion())->getConexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir datos del formulario
    $IdDotacion    = $_POST["IdDotacion"];
    $NombreDotacion = $_POST["NombreDotacion"];
    $EstadoDotacion = $_POST["EstadoDotacion"];
    $TipoDotacion   = $_POST["TipoDotacion"];
    $Novedad       = $_POST["Novedad"];
    $FechaDevolucion = $_POST["FechaDevolucion"];
    $FechaEntrega   = $_POST["FechaEntrega"];
    $IdFuncionario  = $_POST["IdFuncionario"];
   

    // Preparar consulta SQL
    $sql = "INSERT INTO Dotacion (IdDotacion, NombreDotacion, TipoDotacion, Novedad, FechaDevolucion, FechaEntrega, IdFuncionario)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("❌ Error en prepare: " . $conn->error);
    }

    // Vincular parámetros
    $stmt->bind_param("sssii", $IdDotacion, $NombreDotacion, $EstadoDotacion, $TipoDotacion,$Novedad,$FechaDevolucion,$FechaEntrega,$IdFuncionario);

    // Ejecutar
    if ($stmt->execute()) {
        echo "<div style='color: green; font-weight: bold;'>✅ Dotación registrada correctamente</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no permitido.";
}
?>
