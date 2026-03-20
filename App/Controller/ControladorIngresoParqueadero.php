<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloIngresoParqueadero.php";


class ControladorIngresoParqueadero {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloIngresoParqueadero();
    }

    public function registrarIngreso() {

        $input          = json_decode(file_get_contents('php://input'), true);
        $qrCodigo       = $input['qr_codigo']     ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        $vehiculo = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        if (!$vehiculo) {
            return $this->responder(false, 'Vehículo no encontrado o inactivo');
        }

        $exito = $this->modelo->registrarIngreso(
            $vehiculo['IdVehiculo'],
            $vehiculo['IdFuncionarioReal'] ?? null,
            $vehiculo['IdSede']            ?? null,
            $vehiculo['IdParqueadero']     ?? null,
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'dueno'         => $vehiculo['DuenoVehiculo']       ?? 'No registrado',
            'placa'         => $vehiculo['PlacaVehiculo']       ?? '—',
            'tipo'          => $vehiculo['TipoVehiculo']        ?? '—',
            'descripcion'   => $vehiculo['DescripcionVehiculo'] ?? '—',
            'numeroEspacio' => $vehiculo['NumeroEspacio']       ?? 'Sin asignar',
            'foto'          => $vehiculo['FotoFuncionario']     ?? null,
            'fecha'         => date('Y-m-d H:i:s'),
            'movimiento'    => $tipoMovimiento
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

$controlador = new ControladorIngresoParqueadero();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}
?>