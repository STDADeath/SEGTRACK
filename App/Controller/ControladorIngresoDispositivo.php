<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');


// IMPORTACIÓN DE ARCHIVOS NECESARIOS

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresoDispositivo.php";


class ControladorIngresoDispositivo {

    private $modelo;

    // Constructor: inicializa el modelo para poder usar sus métodos
    public function __construct() {
        $this->modelo = new ModeloIngresoDispositivo();
    }


    // ========================================
    // REGISTRAR ENTRADA O SALIDA DE DISPOSITIVO
    // Recibe un JSON desde JavaScript: { qr_codigo, tipoMovimiento }
    // ========================================

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

        // Se busca el dispositivo activo que corresponde al QR
        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        // Si no coincide con ningún dispositivo activo → no se registra nada
        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado o inactivo');
        }

        // Se registra el movimiento en la tabla ingreso (campo IdDispositivo)
        $exito = $this->modelo->registrarIngresoDispositivo(
            $dispositivo['IdDispositivo'],
            $dispositivo['IdSede'] ?? null,
            $tipoMovimiento
        );

        // Error al insertar en BD
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Respuesta exitosa para la vista
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'tipo'        => $dispositivo['TipoDispositivo'],
            'marca'       => $dispositivo['MarcaDispositivo'],
            'serial'      => $dispositivo['NumeroSerial'],
            'funcionario' => $dispositivo['NombreFuncionario'],
            'fecha'       => date('Y-m-d H:i:s'),
            'movimiento'  => $tipoMovimiento
        ]);
    }


    // ========================================
    // LISTAR MOVIMIENTOS DE DISPOSITIVOS
    // Responde cuando el navegador hace GET al controlador
    // ========================================

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresosDispositivos();

        echo json_encode([
            "data" => $lista
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ========================================
    // FUNCIÓN DE RESPUESTA GLOBAL EN JSON
    // ========================================

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
// POST → Registrar entrada o salida de dispositivo
// GET  → Listar movimientos

$controlador = new ControladorIngresoDispositivo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}

?>