<?php
// App/Controller/ControladorSede.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Model/modelosede.php';

class ControladorSede
{

    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloSede();
    }

    // ============================================================
    // OBTENER INSTITUCIONES
    // ============================================================
    public function obtenerInstituciones()
    {
        return $this->modelo->obtenerInstituciones();
    }

    // ============================================================
    // OBTENER SEDES
    // ============================================================
    public function obtenerSedes()
    {
        // La vista usa esta función.
        return $this->modelo->obtenerSedes();
    }

    // ============================================================
    // REGISTRAR
    // ============================================================
    public function registrarSede($datos)
    {

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
    // EDITAR
    // ============================================================
    public function editarSede($datos)
    {

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

    // ============================================================
    // OBTENER SEDE POR ID
    // ============================================================
    public function obtenerSedePorId($idSede)
    {
        return $this->modelo->obtenerSedePorId($idSede);
    }

    // ============================================================
    // CAMBIAR ESTADO (ACTIVO / INACTIVO) - CORREGIDO
    // ============================================================
    // Ya que el modelo maneja la lógica de alternar, el controlador solo necesita el ID.
    public function cambiarEstado($idSede)
    {
        // Se llama al método del modelo.
        return $this->modelo->cambiarEstado($idSede);
    }
}



// ============================================================
// PETICIÓN AJAX (Punto de entrada para la vista SedeLista)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    header('Content-Type: application/json; charset=utf-8');
    $controlador = new ControladorSede();

    switch ($_POST['accion']) {

        case 'registrar':
            echo json_encode($controlador->registrarSede($_POST));
            break;

        case 'editar':
            echo json_encode($controlador->editarSede($_POST));
            break;

        case 'obtener_sede':
            // Se asume que en el POST se envía 'IdSede'
            echo json_encode($controlador->obtenerSedePorId($_POST['IdSede']));
            break;

        // En tu ControladorSede.php (líneas 143-157)
        case 'cambiarEstado':
            // CORRECCIÓN CLAVE: El modelo solo espera el ID, no el estado, 
            // ya que el modelo calcula el nuevo estado.
            // Se pasa el IdSede del POST.
            $idSede = intval($_POST['id'] ?? 0); // La vista envía el ID como 'id' en la petición AJAX <--- CORRECTO

            if ($idSede > 0) {
                echo json_encode(
                    $controlador->cambiarEstado($idSede) // Se llama solo con el ID
                );
            } else {
                echo json_encode(["success" => false, "message" => "ID de Sede no proporcionado."]);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Acción no válida"]);
    }

    exit;
}