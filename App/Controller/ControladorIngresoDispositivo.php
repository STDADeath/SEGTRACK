<?php

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

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
            return $this->responder(false, 'Código QR no recibido', null, 'sin_qr');
        }

        $resultado = $this->modelo->buscarDispositivoPorQr($qrCodigo);

        // Dispositivo no existe en absoluto
        if (!$resultado['encontrado']) {
            return $this->responder(false, 'Dispositivo no encontrado', null, 'no_encontrado');
        }

        // Dispositivo existe pero está inactivo
        if ($resultado['inactivo']) {
            return $this->responder(
                false,
                'Dispositivo inactivo. No tiene permiso de acceso.',
                null,
                'inactivo'
            );
        }

        // Dispositivo activo — validar lógica de movimiento
        $dispositivo   = $resultado;
        $ultimoMov     = $this->modelo->obtenerUltimoMovimientoDispositivo($dispositivo['IdDispositivo']);

        // Validación para SALIDA
        if ($tipoMovimiento === 'Salida') {
            if ($ultimoMov !== 'Entrada') {
                return $this->responder(
                    false,
                    'El dispositivo debe registrar una Entrada antes de poder registrar una Salida.',
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
                    'El dispositivo ya tiene una Entrada activa. Debe registrar una Salida primero.',
                    null,
                    'entrada_duplicada'
                );
            }
        }

        // Registrar el movimiento
        $exito = $this->modelo->registrarIngreso(
            $dispositivo['IdDispositivo'],
            $dispositivo['IdFuncionario'] ?? null,
            $dispositivo['IdSede'] ?? null,
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento', null, 'error_bd');
        }

        // Respuesta exitosa con datos para mostrar en card
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'tipo'        => $dispositivo['TipoDispositivo'],
            'marca'       => $dispositivo['MarcaDispositivo'],
            'serial'      => $dispositivo['NumeroSerial'],
            'funcionario' => $dispositivo['NombreFuncionario'] ?? 'Sin asignar',
            'cargo'       => $dispositivo['CargoFuncionario'] ?? '—',
            'foto'        => $dispositivo['FotoFuncionario'] ?? null,
            'fecha'       => date('Y-m-d H:i:s'),
            'tipo_mov'    => $tipoMovimiento
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

    $controlador = new ControladorIngresoDispositivo();

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