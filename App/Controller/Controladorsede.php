<?php
// App/Controller/ControladorSede.php

// ⚠️ IMPORTANTE: display_errors OFF para que los warnings de PHP
// no contaminen la respuesta JSON y rompan el AJAX
error_reporting(0);
ini_set('display_errors', 0);

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
        return $this->modelo->obtenerSedes();
    }

    // ============================================================
    // REGISTRAR
    // ============================================================
    public function registrarSede($datos)
    {
        $tipoSede    = trim($datos['TipoSede']       ?? '');
        $ciudad      = trim($datos['Ciudad']          ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0);

        // Solo letras y espacios, sin números
        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
        }

        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false, 'message' => 'El tipo de sede solo debe contener letras.'];
        }

        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false, 'message' => 'La ciudad solo debe contener letras.'];
        }

        return $this->modelo->registrarSede($tipoSede, $ciudad, $institucion);
    }

    // ============================================================
    // EDITAR
    // ============================================================
    public function editarSede($datos)
    {
        $idSede      = intval($datos['IdSede']        ?? 0);
        $tipoSede    = trim($datos['TipoSede']         ?? '');
        $ciudad      = trim($datos['Ciudad']            ?? '');
        $institucion = intval($datos['IdInstitucion']   ?? 0);

        // Solo letras y espacios, sin números
        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($idSede === 0) {
            return ['success' => false, 'message' => 'No se pudo identificar la sede.'];
        }

        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
        }

        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false, 'message' => 'El tipo de sede solo debe contener letras.'];
        }

        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false, 'message' => 'La ciudad solo debe contener letras.'];
        }

        return $this->modelo->editarSede($idSede, $tipoSede, $ciudad, $institucion);
    }

    // ============================================================
    // OBTENER SEDE POR ID
    // ============================================================
    public function obtenerSedePorId($idSede)
    {
        $idSede = intval($idSede);

        if ($idSede <= 0) {
            return null;
        }

        return $this->modelo->obtenerSedePorId($idSede);
    }

    // ============================================================
    // CAMBIAR ESTADO (ACTIVO / INACTIVO)
    // ============================================================
    public function cambiarEstado($idSede)
    {
        return $this->modelo->cambiarEstado($idSede);
    }
}


// ============================================================
// PETICIÓN AJAX — Punto de entrada para la vista SedeLista
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // Limpiar cualquier salida previa que pudiera romper el JSON
    ob_clean();
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
            $idSede = intval($_POST['IdSede'] ?? 0);
            if ($idSede > 0) {
                echo json_encode($controlador->obtenerSedePorId($idSede));
            } else {
                echo json_encode(null);
            }
            break;

        case 'cambiarEstado':
            $idSede = intval($_POST['id'] ?? 0);
            if ($idSede > 0) {
                echo json_encode($controlador->cambiarEstado($idSede));
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de Sede no proporcionado.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }

    exit;
}