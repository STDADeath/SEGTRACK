<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresoDispositivo.php";

class ControladorDispositivo {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloDispositivo();
    }

    /**
     * REGISTRAR INGRESO O SALIDA DE DISPOSITIVO
     */
    public function registrarIngreso() {

        $input = json_decode(file_get_contents('php://input'), true);

        $qrCodigo = $input['qr_codigo'] ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado');
        }

        /**
         * Registrar movimiento
         */
        $exito = $this->modelo->registrarIngreso(
            $dispositivo['IdDispositivo'],
            1,          // IdSede fijo si tu sistema lo usa
            null,       // Parqueadero opcional
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente", [

            "IdDispositivo" => $dispositivo['IdDispositivo'],
            "QrDispositivo" => $dispositivo['QrDispositivo'],
            "TipoDispositivo" => $dispositivo['TipoDispositivo'],
            "MarcaDispositivo" => $dispositivo['MarcaDispositivo'],
            "NumeroSerial" => $dispositivo['NumeroSerial'],
            "Estado" => $dispositivo['Estado'],
            "FechaIngreso" => date('Y-m-d H:i:s'),
            "TipoMovimiento" => $tipoMovimiento

        ]);
    }

    /**
     * LISTAR INGRESOS
     */
    public function listarIngresos() {

        $lista = $this->modelo->listarIngresos();

        echo json_encode([
            "data" => $lista
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * RESPUESTA GLOBAL
     */
    private function responder($success, $message, $data = null) {

        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

/**
 * RUTEO
 */

$controlador = new ControladorDispositivo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $controlador->registrarIngreso();

} else {

    $controlador->listarIngresos();
}

?>