<?php
require_once "../backed/conexion.php";

// Crear conexión
$conn = (new Conexion())->getConexion();

header('Content-Type: application/json'); // ✅ Respuesta en JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipoVehiculo        = $_POST["TipoVehiculo"];
    $PlacaVehiculo       = $_POST["PlacaVehiculo"];
    $DescripcionVehiculo = $_POST["DescripcionVehiculo"];
    $TarjetaPropiedad    = $_POST["TarjetaPropiedad"];
    $FechaParqueadero    = $_POST["FechaParqueadero"];
    $IdSede              = $_POST["IdSede"];

    // Validar si existe la sede
    $checkSede = $conn->prepare("SELECT COUNT(*) FROM sede WHERE IdSede = ?");
    $checkSede->bind_param("i", $IdSede);
    $checkSede->execute();
    $checkSede->bind_result($existe);
    $checkSede->fetch();
    $checkSede->close();

    if ($existe == 0) {
        echo json_encode([
            "status" => "error",
            "message" => "❌ La sede con ID $IdSede no existe."
        ]);
        $conn->close();
        exit;
    }

    // Insertar registro
    $sql = "INSERT INTO parqueadero 
            (TipoVehiculo, PlacaVehiculo, DescripcionVehiculo, TarjetaPropiedad, FechaParqueadero, IdSede) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode([
            "status" => "error",
            "message" => "Error en prepare: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssssi",
        $tipoVehiculo,
        $PlacaVehiculo,
        $DescripcionVehiculo,
        $TarjetaPropiedad,
        $FechaParqueadero,
        $IdSede
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "✅ Vehículo registrado correctamente"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "❌ Error al registrar vehículo: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Acceso no permitido"
    ]);
}
?>
