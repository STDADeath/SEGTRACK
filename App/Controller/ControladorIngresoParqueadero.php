<?php

// Capturar cualquier warning/error de PHP antes de que rompa el JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Model/ModeloIngresoParqueadero.php";

class ControladorParqueadero {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloParqueadero();
    }

    public function registrarIngreso() {

        $input = json_decode(file_get_contents('php://input'), true);

        $qrCodigo      = $input['qr_codigo']      ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? "Entrada";

        if (!$qrCodigo) {
            return $this->responder(false, "Código QR no recibido.");
        }

        $vehiculo = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        if (!$vehiculo) {
            return $this->responder(false, "Vehículo no encontrado en el sistema.");
        }

        $exito = $this->modelo->registrarIngreso(
            $vehiculo['IdVehiculo'],
            $vehiculo['IdSede'],
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, "No se pudo registrar el movimiento.");
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente.", [
            "dueno"         => $vehiculo["DuenoVehiculo"],
            "placa"         => $vehiculo["PlacaVehiculo"],
            "tipo"          => $vehiculo["TipoVehiculo"],
            "descripcion"   => $vehiculo["DescripcionVehiculo"],
            "qr"            => $vehiculo["QrVehiculo"],
            "numeroEspacio" => $vehiculo["NumeroEspacio"] ?? "Sin asignar",
            "fecha"         => date("Y-m-d H:i:s"),
            "movimiento"    => $tipoMovimiento
        ]);
    }

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresos();

        ob_clean(); // Limpiar cualquier output previo que rompa el JSON
        echo json_encode(["data" => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function responder($success, $message, $data = null) {

        ob_clean(); // Limpiar cualquier output previo que rompa el JSON
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

try {
    $controlador = new ControladorParqueadero();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controlador->registrarIngreso();
    } else {
        $controlador->listarIngresos();
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "message" => "Error del servidor: " . $e->getMessage(),
        "data"    => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

?>