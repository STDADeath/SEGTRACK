<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Model/ModeloIngresoDispositivo.php";

class ControladorDispositivo {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloDispositivo();
    }

    /**
     * Registrar movimiento de dispositivo (Entrada/Salida)
     */
    public function registrarMovimiento() {
        // Obtener datos del POST
        $input = json_decode(file_get_contents('php://input'), true);
        
        $qrCodigo = $input['qr_codigo'] ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';
        
        // IdSede es opcional, puede ser NULL
        $idSede = $input['idSede'] ?? null;

        // LOG 1: Ver qué está llegando
        error_log("===== INICIO REGISTRO DISPOSITIVO =====");
        error_log("QR recibido: '" . $qrCodigo . "'");
        error_log("Length del QR: " . strlen($qrCodigo));
        error_log("Tipo Movimiento: " . $tipoMovimiento);
        error_log("ID Sede: " . $idSede);

        // Validar datos mínimos
        if (!$qrCodigo) {
            error_log("ERROR: Código QR vacío o no recibido");
            return $this->responder(false, 'Código QR no recibido');
        }

        if (!$idSede) {
            error_log("ERROR: No hay sedes disponibles en la base de datos");
            return $this->responder(false, 'No hay sedes configuradas. Contacte al administrador.');
        }

        // LOG 2: Intentar buscar dispositivo
        error_log("Buscando dispositivo en BD con QR: '" . $qrCodigo . "'");
        
        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        // LOG 3: Resultado de búsqueda
        if ($dispositivo) {
            error_log("✓ Dispositivo ENCONTRADO: " . json_encode($dispositivo));
        } else {
            error_log("✗ Dispositivo NO ENCONTRADO");
            
            // Mostrar QRs disponibles en BD (solo para debug)
            error_log("Intentando listar algunos QR disponibles...");
            
            return $this->responder(false, 'Dispositivo no encontrado. Verifica que el código QR esté registrado.');
        }

        // Verificar estado del dispositivo
        if (isset($dispositivo['Estado']) && $dispositivo['Estado'] === 'Inactivo') {
            error_log("ERROR: Dispositivo está INACTIVO");
            return $this->responder(false, 'El dispositivo está inactivo y no puede registrar movimientos');
        }

        // LOG 4: Intentar registrar movimiento
        error_log("Registrando movimiento en BD...");
        error_log("IdDispositivo: " . $dispositivo['IdDispositivo']);
        
        $exito = $this->modelo->registrarMovimiento(
            $dispositivo['IdDispositivo'],
            $idSede,
            $tipoMovimiento
        );

        // LOG 5: Resultado de inserción
        if (!$exito) {
            error_log("✗ ERROR al insertar en tabla ingreso");
            return $this->responder(false, 'No se pudo registrar el movimiento en la base de datos');
        }

        error_log("✓ Movimiento registrado EXITOSAMENTE");
        error_log("===== FIN REGISTRO DISPOSITIVO =====");

        // Respuesta exitosa
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'qr' => $dispositivo['QrDispositivo'],
            'tipo' => $dispositivo['TipoDispositivo'],
            'marca' => $dispositivo['MarcaDispositivo'],
            'fecha' => date('Y-m-d H:i:s'),
            'movimiento' => $tipoMovimiento
        ]);
    }

    /**
     * Listar todos los movimientos de dispositivos
     */
    public function listarMovimientos() {
        error_log("Listando movimientos de dispositivos...");
        
        $lista = $this->modelo->listarMovimientos();
        
        error_log("Total movimientos encontrados: " . count($lista));
        
        echo json_encode(["data" => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Función auxiliar para responder en JSON
     */
    private function responder($success, $message, $data = null) {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        
        error_log("Respuesta enviada: " . json_encode($response));
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ===== RUTEO PRINCIPAL =====
try {
    $controlador = new ControladorDispositivo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Método: POST - Registrando movimiento");
        $controlador->registrarMovimiento();
    } else {
        error_log("Método: GET - Listando movimientos");
        $controlador->listarMovimientos();
    }
} catch (Exception $e) {
    error_log("EXCEPCIÓN CAPTURADA: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>