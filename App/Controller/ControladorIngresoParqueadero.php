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


    // REGISTRAR INGRESO O SALIDA DE VEHÍCULO
    // Recibe JSON desde JavaScript, valida el QR,
    // busca el vehículo y registra el movimiento.

    public function registrarIngreso() {

        $input = json_decode(file_get_contents('php://input'), true);

        $qrCodigo       = $input['qr_codigo']     ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        $vehiculo = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        if (!$vehiculo) {
            return $this->responder(false, 'Vehículo no encontrado o inactivo');
        }

        if (empty($vehiculo['IdParqueadero'])) {
            return $this->responder(false, 'No hay parqueadero activo para la sede de este vehículo');
        }

        $exito = $this->modelo->registrarIngreso(
            $vehiculo['IdVehiculo'],
            $vehiculo['IdFuncionarioReal'] ?? null,
            $vehiculo['IdSede']            ?? null,
            $vehiculo['IdParqueadero'],
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
            'fecha'         => date('Y-m-d H:i:s'),
            'movimiento'    => $tipoMovimiento
        ]);
    }


    // LISTAR INGRESOS DE VEHÍCULOS
    // Responde cuando el navegador hace GET (DataTables)

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresos();

        echo json_encode(['data' => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // RESPUESTA ESTÁNDAR JSON

    private function responder($success, $message, $data = null) {

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


// ----- RUTEO -----
// POST → Registrar entrada o salida de vehículo
// GET  → Listar movimientos para DataTables

$controlador = new ControladorIngresoParqueadero();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}
?>