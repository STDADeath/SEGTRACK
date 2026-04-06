<?php
// ==========================================================
// CONTROLADOR: ControladorSede.php
// ==========================================================

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../Model/ModeloSede.php';

class ControladorSede
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloSede();
    }

    // ══════════════════════════════════════════════════════
    // OBTENER INSTITUCIONES
    // ══════════════════════════════════════════════════════
    public function obtenerInstituciones()
    {
        return $this->modelo->obtenerInstituciones();
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDES (tabla completa con JOIN)
    // ══════════════════════════════════════════════════════
    public function obtenerSedes()
    {
        return $this->modelo->obtenerSedes();
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDES ACTIVAS (para selects en formularios)
    // ══════════════════════════════════════════════════════
    public function obtenerSedesActivas()
    {
        return $this->modelo->obtenerSedesActivas();
    }

    // ══════════════════════════════════════════════════════
    // REGISTRAR SEDE
    // ══════════════════════════════════════════════════════
    public function registrarSede($datos)
    {
        $tipoSede    = trim($datos['TipoSede']       ?? '');
        $ciudad      = trim($datos['Ciudad']          ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0);

        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
        }
        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false,
                    'message' => 'El tipo de sede solo debe contener letras.'];
        }
        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false,
                    'message' => 'La ciudad solo debe contener letras.'];
        }

        return $this->modelo->registrarSede($tipoSede, $ciudad, $institucion);
    }

    // ══════════════════════════════════════════════════════
    // EDITAR SEDE
    // ══════════════════════════════════════════════════════
    public function editarSede($datos)
    {
        $idSede      = intval($datos['IdSede']        ?? 0);
        $tipoSede    = trim($datos['TipoSede']         ?? '');
        $ciudad      = trim($datos['Ciudad']            ?? '');
        $institucion = intval($datos['IdInstitucion']   ?? 0);

        $regexTexto = '/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]{1,30}$/';

        if ($idSede === 0) {
            return ['success' => false,
                    'message' => 'No se pudo identificar la sede.'];
        }
        if ($tipoSede === '' || $ciudad === '' || $institucion === 0) {
            return ['success' => false,
                    'message' => 'Todos los campos son obligatorios.'];
        }
        if (!preg_match($regexTexto, $tipoSede)) {
            return ['success' => false,
                    'message' => 'El tipo de sede solo debe contener letras.'];
        }
        if (!preg_match($regexTexto, $ciudad)) {
            return ['success' => false,
                    'message' => 'La ciudad solo debe contener letras.'];
        }

        $resultado = $this->modelo->editarSede($idSede, $tipoSede, $ciudad, $institucion);
        return [
            'success' => $resultado,
            'message' => $resultado ? 'Sede actualizada correctamente' : 'Error al actualizar la sede'
        ];
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDE POR ID
    // ══════════════════════════════════════════════════════
    public function obtenerSedePorId($idSede)
    {
        $idSede = intval($idSede);
        if ($idSede <= 0) return null;
        return $this->modelo->obtenerSedePorId($idSede);
    }

    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO (toggle Activo / Inactivo)
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($idSede)
    {
        $idSede = intval($idSede);
        if ($idSede <= 0) {
            return ['success' => false, 'message' => 'ID de sede inválido'];
        }
        return $this->modelo->cambiarEstado($idSede);
    }
}

// ══════════════════════════════════════════════════════
// PUNTO DE ENTRADA AJAX
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

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
            echo json_encode(
                $idSede > 0
                    ? $controlador->obtenerSedePorId($idSede)
                    : null
            );
            break;

        case 'cambiarEstado':
            $idSede = intval($_POST['id'] ?? 0);
            echo json_encode(
                $idSede > 0
                    ? $controlador->cambiarEstado($idSede)
                    : ['success' => false, 'message' => 'ID de Sede no proporcionado.']
            );
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }

    exit;
}
?>