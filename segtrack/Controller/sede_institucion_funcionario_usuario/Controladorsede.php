<?php
require_once __DIR__ . "/../../Model/sede_institucion_funcionario_usuario/modelosede.php";

class ControladorSede {

    private $modelo;

    public function __construct() {
        $this->modelo = new Modelo_Sede();
    }

    // ================================
    // ✔ MÉTODOS GET
    // ================================
    public function obtenerInstituciones() {
        return $this->modelo->obtenerInstituciones();
    }

    public function obtenerTodasLasSedes() {
        return $this->modelo->obtenerTodasLasSedes();
    }

    public function obtenerSedePorId($id) {
        return $this->modelo->obtenerSedePorId($id);
    }

    // ================================
    // ✔ MÉTODOS POST (Devuelven un array para JSON)
    // ================================
    /**
     * Registra una nueva sede y retorna el resultado para la respuesta JSON.
     * @return array
     */
    public function registrarSede($tipo, $ciudad, $idInstitucion) {
        
        // 1. Validación de datos
        if (empty($tipo) || empty($ciudad) || empty($idInstitucion)) {
            return ['success' => false, 'message' => 'Faltan datos obligatorios para el registro.'];
        }

        // 2. Inserción en el modelo
        if ($this->modelo->insertarSede($tipo, $ciudad, $idInstitucion)) {
            return ['success' => true, 'message' => 'Sede registrada correctamente.'];
        } else {
            // Error de base de datos
            return ['success' => false, 'message' => 'Error al intentar registrar la sede en la base de datos.'];
        }
    }
}


// ===============================================
// BLOQUE DE PROCESAMIENTO AJAX
// Este código maneja todas las peticiones POST de la vista.
// ===============================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {
    
    // Crear la instancia del controlador
    $controlador = new ControladorSede();
    
    // Configurar la cabecera para devolver JSON
    header('Content-Type: application/json');

    // Inicializar la respuesta por defecto
    $response = ['success' => false, 'message' => 'Acción no reconocida en el controlador.'];

    // Ejecutar la acción
    if ($_POST['accion'] === 'registrar') {
        
        // Se capturan los datos usando los nombres 'name' del formulario HTML
        $tipo = $_POST['TipoSede'] ?? null;
        $ciudad = $_POST['Ciudad'] ?? null;
        $institucion = $_POST['IdInstitucion'] ?? null;

        $response = $controlador->registrarSede($tipo, $ciudad, $institucion);
        
    } 
    
    // Responder y detener la ejecución del script
    echo json_encode($response);
    exit; 
}
?>