<?php

header('Content-Type: application/json; charset=utf-8');
// Permitimos que cualquier dominio pueda consumir este servicio (evita errores CORS)
header('Access-Control-Allow-Origin: *');
// Permitimos únicamente los métodos GET y POST
header('Access-Control-Allow-Methods: GET, POST');
// Permitimos que el cliente envíe contenido JSON
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
    // Este método recibe un JSON desde JavaScript,
    // valida el QR, busca el dispositivo y registra el movimiento.
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

        // Se consulta si el QR pertenece a un dispositivo activo
        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        // Si no coincide con ningún dispositivo → no se registra nada
        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado o inactivo');
        }

        // Registrar el movimiento en la tabla ingreso
        $exito = $this->modelo->registrarIngresoDispositivo(
            $dispositivo['IdDispositivo'],
            $dispositivo['IdFuncionario'] ?? null,
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
            'tipo_mov'    => $tipoMovimiento
        ]);
    }


    // ========================================
    // LISTAR MOVIMIENTOS DE DISPOSITIVOS
    // Obtiene todos los registros desde el modelo.
    // Este método responde cuando el navegador hace GET al controlador
    // ========================================

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresosDispositivos();

        echo json_encode([
            "data" => $lista
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ========================================
    // FUNCIÓN DE RESPUESTA GLOBAL
    // Formatea todas las respuestas del controlador en JSON
    // para mantener un estándar único.
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
// GET  → Listar movimientos de dispositivos

$controlador = new ControladorIngresoDispositivo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}

?>