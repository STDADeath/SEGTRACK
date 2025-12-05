<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Model/ModeloIngresParqueadero.php";

class ControladorParqueadero {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloParqueadero();
    }

    // REGISTRAR INGRESO/SALIDA
    public function registrarIngreso() {
        $input = json_decode(file_get_contents('php://input'), true);
        $qrCodigo = $input['qr_codigo'] ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) return $this->responder(false, 'Código QR no recibido');

        $vehiculo = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        if (!$vehiculo) return $this->responder(false, 'Vehículo no encontrado');

        $exito = $this->modelo->registrarIngreso(
            $vehiculo['IdParqueadero'],
            $vehiculo['IdSede'],
            $tipoMovimiento
        );

        if (!$exito) return $this->responder(false, 'No se pudo registrar el movimiento');

        return $this->responder(true, "$tipoMovimiento registrado correctamente", [
            'descripcion' => $vehiculo['DescripcionVehiculo'],
            'placa'       => $vehiculo['PlacaVehiculo'],
            'tipo'        => $vehiculo['TipoVehiculo'],
            'fecha'       => date('Y-m-d H:i:s'),
            'movimiento'  => $tipoMovimiento
        ]);
    }

    // LISTAR INGRESOS
    public function listarIngresos() {
        $lista = $this->modelo->listarIngresos();
        echo json_encode(["data" => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // RESPUESTA ESTÁNDAR
    private function responder($success, $message, $data = null) {
        echo json_encode(['success'=>$success, 'message'=>$message, 'data'=>$data], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$controlador = new ControladorParqueadero();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}

?>
