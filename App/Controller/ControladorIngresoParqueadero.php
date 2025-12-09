<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresoParqueadero.php";

class ControladorParqueadero {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloParqueadero();
    }

    /**
     * REGISTRAR INGRESO O SALIDA DE VEHÍCULO AL PARQUEADERO
     * IMPORTANTE: Usa la sede del parqueadero, no del vehículo
     */
    public function registrarIngreso() {
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

        // Se consulta si el QR pertenece a un parqueadero (vehículo)
        $parqueadero = $this->modelo->buscarParqueaderoPorQr($qrCodigo);

        // Si no coincide con ningún parqueadero → no se registra nada
        if (!$parqueadero) {
            return $this->responder(false, 'Vehículo no encontrado en el sistema');
        }

        // Verificar que el parqueadero tenga una sede asignada
        if (empty($parqueadero['IdSede'])) {
            return $this->responder(false, 'El vehículo no tiene sede asignada. Contacte al administrador.');
        }

        /**
         * Registrar el movimiento en la tabla ingreso
         * USANDO LA SEDE DEL PARQUEADERO (esto es clave)
         */
        $exito = $this->modelo->registrarIngreso(
            $parqueadero['IdParqueadero'],
            $parqueadero['IdSede'],  // ← SEDE DEL PARQUEADERO
            $tipoMovimiento
        );

        // Error al insertar en BD
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Respuesta exitosa para la vista
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'tipoVehiculo' => $parqueadero['TipoVehiculo'],
            'placa' => $parqueadero['PlacaVehiculo'],
            'descripcion' => $parqueadero['DescripcionVehiculo'] ?? 'N/A',
            'fecha' => date('Y-m-d H:i:s'),
            'movimiento' => $tipoMovimiento
        ]);
    }

    /**
     * LISTAR INGRESOS DE PARQUEADERO
     */
    public function listarIngresos() {
        $lista = $this->modelo->listarIngresos();

        echo json_encode([
            "data" => $lista
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * FUNCION DE RESPUESTA GLOBAL
     */
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

// ----- RUTEO BÁSICO -----
// POST → Registrar entrada o salida
// GET  → Listar ingresos
$controlador = new ControladorParqueadero();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}

?>