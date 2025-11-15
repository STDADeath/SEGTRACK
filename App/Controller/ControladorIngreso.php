<?php
<<<<<<< HEAD

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
=======
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
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    public function __construct() {
        $this->modelo = new ModeloIngreso();
    }

<<<<<<< HEAD

    
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
=======
    // Función para registrar entrada o salida
    public function registrarIngreso() {
        // Se obtienen los datos enviados desde el fetch en JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $qrCodigo = $input['qr_codigo'] ?? null;

        // Se recibe si es Entrada o Salida (por defecto Entrada)
        $tipoMovimiento = $input['tipoMovimiento'] ?? 'Entrada';

        // Validación básica: verificar si llegó un código
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        if (!$qrCodigo) {
            return $this->responder(false, 'Código QR no recibido');
        }

<<<<<<< HEAD
        // Se consulta si el QR pertenece a un funcionario
        $funcionario = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        // Si no coincide con ningún funcionario → no se registra nada
=======
        // Buscar si el QR pertenece a un funcionario valido
        $funcionario = $this->modelo->buscarFuncionarioPorQr($qrCodigo);

        // Si no existe en BD, no se registra nada
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        if (!$funcionario) {
            return $this->responder(false, 'Funcionario no encontrado');
        }

<<<<<<< HEAD
        
         //Registrar el movimiento en la tabla ingreso

=======
        // Registrar el movimiento (Entrada o Salida)
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        $exito = $this->modelo->registrarIngreso(
            $funcionario['IdFuncionario'],
            $funcionario['IdSede'],
            $funcionario['IdParqueadero'] ?? null,
<<<<<<< HEAD
            $tipoMovimiento
        );

        // Error al insertar en BD
=======
            $tipoMovimiento // Movimiento enviado al modelo
        );

        // Si hubo un problema al guardar en base de datos
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        if (!$exito) {
            return $this->responder(false, 'No se pudo registrar el movimiento');
        }

<<<<<<< HEAD
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
     
=======
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
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    private function responder($success, $message, $data = null) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
<<<<<<< HEAD

        // Se evita que siga corriendo código después de responder
=======
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        exit;
    }
}

<<<<<<< HEAD

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
=======
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

?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
