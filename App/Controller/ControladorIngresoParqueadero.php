<?php

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

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
            return $this->responder(false, 'Código QR no recibido', null, 'sin_qr');
        }

        $resultado = $this->modelo->buscarVehiculoPorQr($qrCodigo);

        // Vehículo no existe
        if (!$resultado['encontrado']) {
            return $this->responder(false, 'Vehículo no encontrado', null, 'no_encontrado');
        }

        // Vehículo inactivo
        if ($resultado['inactivo']) {
            return $this->responder(
                false,
                'Vehículo inactivo. No tiene permiso de acceso.',
                null,
                'inactivo'
            );
        }

        // Vehículo activo — validar lógica de movimiento
        $vehiculo  = $resultado;
        $ultimoMov = $this->modelo->obtenerUltimoMovimientoVehiculo($vehiculo['IdVehiculo']);

        // Validación para SALIDA
        if ($tipoMovimiento === 'Salida') {
            if ($ultimoMov !== 'Entrada') {
                return $this->responder(
                    false,
                    'El vehículo debe registrar una Entrada antes de poder registrar una Salida.',
                    null,
                    'sin_entrada_previa'
                );
            }
        }

        // Validación para ENTRADA (evitar duplicados)
        if ($tipoMovimiento === 'Entrada') {
            if ($ultimoMov === 'Entrada') {
                return $this->responder(
                    false,
                    'El vehículo ya tiene una Entrada activa. Debe registrar una Salida primero.',
                    null,
                    'entrada_duplicada'
                );
            }
        }

        // Registrar el movimiento
        $exito = $this->modelo->registrarIngreso(
            $vehiculo['IdVehiculo'],
            $vehiculo['IdFuncionarioReal'] ?? null,
            $vehiculo['IdSede'] ?? null,
            $vehiculo['IdParqueadero'] ?? null,
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento', null, 'error_bd');
        }

        // Respuesta exitosa
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'dueno'         => $vehiculo['DuenoVehiculo'] ?? 'No registrado',
            'placa'         => $vehiculo['PlacaVehiculo'] ?? '—',
            'tipo'          => $vehiculo['TipoVehiculo'] ?? '—',
            'descripcion'   => $vehiculo['DescripcionVehiculo'] ?? '—',
            'numeroEspacio' => $vehiculo['NumeroEspacio'] ?? 'Sin asignar',
            'foto'          => $vehiculo['FotoFuncionario'] ?? null,
            'fecha'         => date('Y-m-d H:i:s'),
            'movimiento'    => $tipoMovimiento
        ]);
    }

    public function listarIngresos() {

        $lista = $this->modelo->listarIngresos();

        ob_clean();
        echo json_encode(['data' => $lista], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function responder($success, $message, $data = null, $codigo = null) {

        ob_clean();
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'codigo'  => $codigo,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

try {

    $controlador = new ControladorIngresoParqueadero();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controlador->registrarIngreso();
    } else {
        $controlador->listarIngresos();
    }

} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'codigo'  => 'error_servidor',
        'data'    => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>