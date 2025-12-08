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

    // ============================================================
    // 🔹 OBTENER LISTA DE INSTITUCIONES
    // ============================================================
    public function obtenerInstituciones() {
        return $this->modelo->obtenerInstituciones();
    }

    // ============================================================
    // 🔹 OBTENER LISTA DE SEDES PARA SELECT
    // ============================================================
    public function obtenerSedes() {
        return $this->modelo->obtenerSedes();
    }

    // ============================================================
    // 🔹 REGISTRAR UNA NUEVA SEDE
    // ============================================================
    public function registrarSede($datos) {

        $tipoSede = trim($datos['TipoSede'] ?? '');
        $ciudad = trim($datos['Ciudad'] ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0); 

        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
        }

        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false, 'message' => 'El tipo de sede contiene caracteres inválidos.'];
        }

        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false, 'message' => 'La ciudad contiene caracteres inválidos.'];
        }

        return $this->modelo->registrarSede($tipoSede, $ciudad, $institucion);
    }

    // ============================================================
    // 🔹 EDITAR UNA SEDE EXISTENTE
    // ============================================================
    public function editarSede($datos) {

        $idSede = intval($datos['IdSede'] ?? 0);
        $tipoSede = trim($datos['TipoSede'] ?? '');
        $ciudad = trim($datos['Ciudad'] ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0);

        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($idSede === 0 || $tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
        }

        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false, 'message' => 'El tipo de sede contiene caracteres inválidos.'];
        }

        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false, 'message' => 'La ciudad contiene caracteres inválidos.'];
        }

        return $this->modelo->editarSede($idSede, $tipoSede, $ciudad, $institucion);
    }
}

// ============================================================
// 🔹 PETICIÓN AJAX
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    header('Content-Type: application/json');
    $controlador = new ControladorSede();
    $respuesta = [];

    switch ($_POST['accion']) {

        case 'registrar':
            $respuesta = $controlador->registrarSede($_POST);
            break;

        case 'editar':
            $respuesta = $controlador->editarSede($_POST);
            break;
    }

    if (isset($respuesta['success']) && $respuesta['success'] === false) {
        http_response_code(400);
    }

    echo json_encode($respuesta);
    exit;
}
