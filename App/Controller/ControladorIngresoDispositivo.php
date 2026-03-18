<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresoDispositivo.php";


class ControladorIngresoDispositivo {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloIngresoDispositivo();
    }

    public function registrarIngreso() {

        $input          = json_decode(file_get_contents('php://input'), true);
        $qrCodigo       = $input['qr_codigo']     ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        $dispositivo = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        if (!$dispositivo) {
            return $this->responder(false, 'Dispositivo no encontrado o inactivo');
        }

        $exito = $this->modelo->registrarIngreso(
            $dispositivo['IdDispositivo'],
            $dispositivo['IdFuncionario'] ?? null,
            $dispositivo['IdSede']        ?? null,
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'tipo'        => $dispositivo['TipoDispositivo'],
            'marca'       => $dispositivo['MarcaDispositivo'],
            'serial'      => $dispositivo['NumeroSerial'],
            'funcionario' => $dispositivo['NombreFuncionario'],
            'cargo'       => $dispositivo['CargoFuncionario'],
            'foto'        => $dispositivo['FotoFuncionario'],   // ← nuevo
            'fecha'       => date('Y-m-d H:i:s'),
            'tipo_mov'    => $tipoMovimiento
        ]);
    }

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresos();

        echo json_encode(['data' => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function responder($success, $message, $data = null) {

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


$controlador = new ControladorIngresoDispositivo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}
?>