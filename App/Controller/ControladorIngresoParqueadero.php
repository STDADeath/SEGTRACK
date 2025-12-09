<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresParqueadero.php";

class ControladorParqueadero {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloParqueadero();
    }

    /**
     * REGISTRAR MOVIMIENTO DE VEHÍCULO
     * EXACTAMENTE IGUAL QUE DISPOSITIVOS
     */
    public function registrarMovimiento() {
        // Obtenemos el cuerpo del POST (JSON enviado desde fetch)
        $input = json_decode(file_get_contents('php://input'), true);

        // QR enviado por la vista
        $qrCodigo = $input['qr_codigo'] ?? null;

        // Tipo de movimiento enviado ("Entrada" o "Salida")
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        // Si no llegó ningún QR → error inmediato
        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        // Se consulta si el QR pertenece a un vehículo
        $vehiculo = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        // Si no coincide con ningún vehículo → no se registra nada
        if (!$vehiculo) {
            return $this->responder(false, 'Vehículo no encontrado');
        }

        /**
         * Registrar el movimiento actualizando el estado
         */
        $exito = $this->modelo->registrarMovimiento(
            $vehiculo['IdParqueadero'],
            $tipoMovimiento
        );

        // Error al actualizar en BD
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Respuesta exitosa para la vista
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'placa' => $vehiculo['PlacaVehiculo'],
            'tipo' => $vehiculo['TipoVehiculo'],
            'descripcion' => $vehiculo['DescripcionVehiculo'],
            'sede' => $vehiculo['NombreSede'] ?? 'Sin sede',
            'fecha' => date('Y-m-d H:i:s'),
            'movimiento' => $tipoMovimiento
        ]);
    }

    /**
     * LISTAR MOVIMIENTOS
     * IGUAL QUE DISPOSITIVOS
     */
    public function listarMovimientos() {
        $lista = $this->modelo->listarMovimientos();

        echo json_encode([
            "data" => $lista
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * FUNCION DE RESPUESTA GLOBAL
     * IGUAL QUE DISPOSITIVOS
     */
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

// ----- RUTEO BÁSICO -----
// POST → Registrar movimiento
// GET  → Listar movimientos
$controlador = new ControladorParqueadero();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarMovimiento();
} else {
    $controlador->listarMovimientos();
}

?>