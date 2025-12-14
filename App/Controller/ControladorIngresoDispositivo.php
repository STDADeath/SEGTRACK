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
     * EXACTAMENTE IGUAL QUE FUNCIONARIOS
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

        // Se consulta si el QR pertenece a un dispositivo
        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        // Si no coincide con ningún dispositivo → no se registra nada
        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado');
        }

        /**
         * Registrar el movimiento en la tabla ingreso
         * USANDO LOS DATOS DEL DISPOSITIVO (igual que funcionario usa sus datos)
         */
        $exito = $this->modelo->registrarIngreso(
            $dispositivo['IdDispositivo'],
            $dispositivo['IdSede'] ?? null,
            $dispositivo['IdParqueadero'] ?? null,
            $tipoMovimiento
        );

        // Error al insertar en BD
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Respuesta exitosa para la vista
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'qr' => $dispositivo['QrDispositivo'],
            'tipo' => $dispositivo['TipoDispositivo'],
            'marca' => $dispositivo['MarcaDispositivo'],
            'fecha' => date('Y-m-d H:i:s'),
            'movimiento' => $tipoMovimiento
        ]);
    }

    /**
     * LISTAR INGRESOS DE DISPOSITIVOS
     * IGUAL QUE FUNCIONARIOS
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
     * IGUAL QUE FUNCIONARIOS
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
$controlador = new ControladorDispositivo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}

?>