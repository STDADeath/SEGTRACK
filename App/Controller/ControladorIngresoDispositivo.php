<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Model/ModeloDispositivo.php";

class ControladorDispositivo {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloDispositivo();
    }

    // Registrar movimiento de dispositivo
    public function registrarMovimiento() {
        $input = json_decode(file_get_contents('php://input'), true);
        $qrCodigo = $input['qr_codigo'] ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';
        $idSede = $input['idSede'] ?? null;

        if (!$qrCodigo || !$idSede) {
            return $this->responder(false, 'Faltan datos necesarios');
        }

        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);
        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado');
        }

        $exito = $this->modelo->registrarMovimiento($dispositivo['IdDispositivo'], $idSede, $tipoMovimiento);
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'qr' => $dispositivo['QrDispositivo'],
            'tipo' => $dispositivo['TipoDispositivo'],
            'marca' => $dispositivo['MarcaDispositivo'],
            'fecha' => date('Y-m-d H:i:s'),
            'movimiento' => $tipoMovimiento
        ]);
    }

    // Listar movimientos
    public function listarMovimientos() {
        $lista = $this->modelo->listarMovimientos();
        echo json_encode(["data" => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function responder($success, $message, $data = null) {
        echo json_encode(['success'=>$success, 'message'=>$message, 'data'=>$data], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Ruteo
$controlador = new ControladorDispositivo();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarMovimiento();
} else {
    $controlador->listarMovimientos();
}
?>
