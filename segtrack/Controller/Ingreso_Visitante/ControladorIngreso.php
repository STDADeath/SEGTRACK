<?php

// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json; charset=utf-8');
// Permitimos peticiones desde cualquier origen (para evitar problemas de CORS)
header('Access-Control-Allow-Origin: *');
// Permitimos métodos GET y POST
header('Access-Control-Allow-Methods: GET, POST');
// Permitimos enviar datos en el encabezado tipo JSON
header('Access-Control-Allow-Headers: Content-Type');

// Se incluyen los archivos necesarios: conexión a BD y el modelo
require_once __DIR__ . '/../../Core/conexion.php';
require_once __DIR__ . '/../../Model/Ingreso_Visitante/ModeloIngreso.php';

class ControladorIngreso {
    private $modelo;

    // Constructor: crea una instancia del modelo para usar sus funciones
    public function __construct() {
        $this->modelo = new ModeloIngreso();
    }

    // Función para registrar entrada o salida
    public function registrarIngreso() {
        // Se obtienen los datos enviados desde el fetch en JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $qrCodigo = $input['qr_codigo'] ?? null;

        // Se recibe si es Entrada o Salida (por defecto Entrada)
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        // Validación básica: verificar si llegó un código
        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        // Buscar si el QR pertenece a un funcionario valido
        $funcionario = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        // Si no existe en BD, no se registra nada
        if (!$funcionario) {
            return $this->responder(false, 'Funcionario no encontrado');
        }

        // Registrar el movimiento (Entrada o Salida)
        $exito = $this->modelo->registrarIngreso(
            $funcionario['IdFuncionario'],
            $funcionario['IdSede'],
            $funcionario['IdParqueadero'] ?? null,
            $tipoMovimiento // Movimiento enviado al modelo
        );

        // Si hubo un problema al guardar en base de datos
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Si todo salió bien se devuelve una respuesta de éxito
        return $this->responder(true, "$tipoMovimiento registrada correctamente ✅", [
            'nombre' => $funcionario['NombreFuncionario'],
            'cargo' => $funcionario['CargoFuncionario'],
            'fecha' => date('Y-m-d H:i:s'),
            'tipo' => $tipoMovimiento
        ]);
    }

    // Función para listar los ingresos más recientes
    public function listarIngresos() {
        $lista = $this->modelo->listarIngresos();
        return $this->responder(true, 'Lista cargada', $lista);
    }

    // Función que unifica el formato JSON de respuesta
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Se crea una instancia del controlador
$controlador = new ControladorIngreso();

// Si la petición es POST → registrar ingreso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} 
// Si es GET → listar
else {
    $controlador->listarIngresos();
}
