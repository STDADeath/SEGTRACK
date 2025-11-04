<?php
// controlador_ingreso.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$ruta_conexion = __DIR__ . '/../../core/conexion.php';
if (!file_exists($ruta_conexion)) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'Archivo de conexión no encontrado']));
}
require_once $ruta_conexion;
require_once __DIR__ . "/../../Model/ingreso_Visitante/Modeloingreso.php";


class IngresoController {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloIngreso();
    }

    public function registrarIngreso() {
        $input = json_decode(file_get_contents('php://input'), true);
        $qrCodigo = $input['qr_codigo'] ?? '';

        if (empty($qrCodigo)) {
            $this->responder(false, 'Código QR no proporcionado');
            return;
        }

        $funcionario = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        if (!$funcionario) {
            $this->responder(false, 'Funcionario no encontrado');
            return;
        }

        $exito = $this->modelo->registrarIngreso(
            $funcionario['IdFuncionario'],
            $funcionario['IdSede'] ?? null,
            $funcionario['IdParqueadero'] ?? null
        );

        if ($exito) {
            $this->responder(true, 'Funcionario ingresado exitosamente', [
                'nombre' => $funcionario['NombreFuncionario'],
                'cargo'  => $funcionario['CargoFuncionario'],
                'fecha'  => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->responder(false, 'Error al registrar el ingreso');
        }
    }

    // ✅ Listar ingresos registrados
    public function listarIngresos() {
        $lista = $this->modelo->listarIngresos();
        $this->responder(true, 'Lista de ingresos obtenida', $lista);
    }

    // 🔧 Método auxiliar para devolver JSON
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
}

// 🧩 Ruteo según método HTTP
$controlador = new IngresoController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $controlador->registrarIngreso();
        break;

    case 'GET':
        $controlador->listarIngresos();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>
