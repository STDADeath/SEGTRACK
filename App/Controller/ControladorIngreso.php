<?php

header('Content-Type: application/json; charset=utf-8');
// Permitimos que cualquier dominio pueda consumir este servicio (evita errores CORS)
header('Access-Control-Allow-Origin: *');
// Permitimos únicamente los métodos GET y POST
header('Access-Control-Allow-Methods: GET, POST');
// Permitimos que el cliente envíe contenido JSON
header('Access-Control-Allow-Headers: Content-Type');


// IMPORTACIÓN DE ARCHIVOS NECESARIOS 

require_once __DIR__ . "/../Core/conexion.php";

require_once __DIR__ . "/../Model/ModeloIngreso.php";


class ControladorIngreso {

    private $modelo;

    // Constructor: inicializa el modelo para poder usar sus métodos
    public function __construct() {
        $this->modelo = new ModeloIngreso();
    }


    
     //REGISTRAR INGRESO O SALIDA
     //Este método recibe un JSON desde JavaScript,
     //valida el QR, busca el funcionario y registra el movimiento.
     //
    public function registrarIngreso() {

        // Obtenemos el cuerpo del POST (JSON enviado desde fetch)
        $input = json_decode(file_get_contents('php://input'), true);

        // QR enviado por la vista
        $qrCodigo = $input['qr_codigo'] ?? null;

        // Tipo de movimiento enviado ("Entrada" o "Salida")
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        // Si no llegó ningún QR → error inmediato
        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

        // Se consulta si el QR pertenece a un funcionario
        $funcionario = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        // Si no coincide con ningún funcionario → no se registra nada
        if (!$funcionario) {
            return $this->responder(false, 'Funcionario no encontrado');
        }

        
         //Registrar el movimiento en la tabla ingreso

        $exito = $this->modelo->registrarIngreso(
            $funcionario['IdFuncionario'],
            $funcionario['IdSede'],
            $funcionario['IdParqueadero'] ?? null,
            $tipoMovimiento
        );

        // Error al insertar en BD
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

        // Respuesta exitosa para la vista
        return $this->responder(true, "$tipoMovimiento registrada correctamente", [
            'nombre' => $funcionario['NombreFuncionario'],
            'cargo'  => $funcionario['CargoFuncionario'],
            'fecha'  => date('Y-m-d H:i:s'),
            'tipo'   => $tipoMovimiento
        ]);
    }


    
    //LISTAR INGRESOS
    //Obtiene todos los ingresos recientes desde el modelo.
      //Este método responde cuando el navegador hace GET al controlador
   public function listarIngresos() {
    $lista = $this->modelo->listarIngresos();

    echo json_encode([
        "data" => $lista
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



    
    // FUNCION DE RESPUESTA GLOBAL
    // Formatea todas las respuestas del controlador en JSON
     //para mantener un estándar único.
     
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);

        // Se evita que siga corriendo código después de responder
        exit;
    }
}


// ----- RUTEO BÁSICO -----
// POST → Registrar entrada o salida
// GET  → Listar ingresos
$controlador = new ControladorIngreso();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->registrarIngreso();
} else {
    $controlador->listarIngresos();
}


?>
