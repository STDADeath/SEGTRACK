<?php
// App/Controller/ControladorSede.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Model/ModeloSede.php';

class ControladorSede {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloSede(); 
    }

    public function obtenerInstituciones() {
        return $this->modelo->obtenerInstituciones();
    }

    /**
     * Obtener todas las sedes para llenar select
     * @return array
     */
    public function obtenerSedes() {
        return $this->modelo->obtenerSedes();
    }

    public function registrarSede($datos) {

        $tipoSede = trim($datos['TipoSede'] ?? '');
        $ciudad = trim($datos['Ciudad'] ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0); 

        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        // VALIDACIÓN PHP
        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios o el ID de la institución es inválido.'];
        }

        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false, 'message' => 'El nombre/tipo de sede contiene caracteres inválidos.'];
        }

        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false, 'message' => 'La ciudad contiene caracteres inválidos.'];
        }

        $resultado = $this->modelo->registrarSede($tipoSede, $ciudad, $institucion);

        if ($resultado['success'] === false) {
             // Retorna el error específico (ej. de llave foránea)
            return $resultado; 
        }
        
        return ['success' => true, 'message' => 'Sede registrada correctamente.'];
    }
}

// ===============================
// PETICIÓN AJAX (Punto de Entrada)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    header('Content-Type: application/json');

    $controlador = new ControladorSede();
    $respuesta = [];

    if ($_POST['accion'] === 'registrar') {
        $respuesta = $controlador->registrarSede($_POST);
    }

    // SI LA RESPUESTA ES FALLIDA, ENVIAMOS CÓDIGO 400
    if (isset($respuesta['success']) && $respuesta['success'] === false) {
        http_response_code(400); // Bad Request / Error de Cliente
    }
    
    echo json_encode($respuesta);
    exit;
}