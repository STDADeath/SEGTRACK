<?php

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . "/../Model/ModeloIngreso.php";

class ControladorIngreso {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloIngreso();
    }

    public function registrarIngreso() {

        $input          = json_decode(file_get_contents('php://input'), true);
        $qrCodigo       = $input['qr_codigo']     ?? null;
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido', null, 'sin_qr');
        }

        $resultado = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        // Funcionario no existe en absoluto
        if (!$resultado['encontrado']) {
            return $this->responder(false, 'Funcionario no encontrado', null, 'no_encontrado');
        }

        // Funcionario existe pero está inactivo
        if ($resultado['inactivo']) {
            return $this->responder(
                false,
                'Funcionario inactivo. No tiene permiso de acceso.',
                null,
                'inactivo'
            );
        }

        // Funcionario activo — validar lógica de movimiento
        $funcionario = $resultado;
        $ultimoMov   = $this->modelo->obtenerUltimoMovimiento($funcionario['IdFuncionario']);

        if ($tipoMovimiento === 'Salida') {
            if ($ultimoMov !== 'Entrada') {
                return $this->responder(
                    false,
                    'El funcionario debe registrar una Entrada antes de poder registrar una Salida.',
                    null,
                    'sin_entrada_previa'
                );
            }
        }

        if ($tipoMovimiento === 'Entrada') {
            if ($ultimoMov === 'Entrada') {
                return $this->responder(
                    false,
                    'El funcionario ya tiene una Entrada activa. Debe registrar una Salida primero.',
                    null,
                    'entrada_duplicada'
                );
            }
        }

        $exito = $this->modelo->registrarIngreso(
            $funcionario['IdFuncionario'],
            $funcionario['IdSede'],
            $tipoMovimiento
        );

        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento', null, 'error_bd');
        }

        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'nombre' => $funcionario['NombreFuncionario'],
            'cargo'  => $funcionario['CargoFuncionario'],
            'fecha'  => date('Y-m-d H:i:s'),
            'tipo'   => $tipoMovimiento,
            'foto'   => $funcionario['FotoFuncionario']
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

    $controlador = new ControladorIngreso();

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