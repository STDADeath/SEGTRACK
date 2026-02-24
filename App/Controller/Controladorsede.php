<?php
// ==========================================================
// CONTROLADOR: ControladorSede.php
// Capa Controlador (MVC): recibe petición HTTP, valida datos,
// llama al modelo y devuelve siempre JSON.
// NUNCA contiene queries SQL — eso es responsabilidad del Modelo.
// ==========================================================

// En producción cambiar a 0 para no exponer rutas en los errores JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../Model/modelosede.php';

class ControladorSede
{
    private $modelo; // Instancia del ModeloSede

    // Instancia el modelo al construir el controlador
    public function __construct()
    {
        $this->modelo = new ModeloSede();
    }


    // ══════════════════════════════════════════════════════
    // OBTENER INSTITUCIONES
    // Delega al modelo la consulta de instituciones activas.
    // Usado para llenar el select del formulario de registro
    // y el select del modal de edición.
    // ══════════════════════════════════════════════════════
    public function obtenerInstituciones()
    {
        return $this->modelo->obtenerInstituciones();
    }


    // ══════════════════════════════════════════════════════
    // OBTENER SEDES
    // Delega al modelo la consulta con JOIN a institución.
    // Usado para renderizar la tabla en la vista lista.
    // ══════════════════════════════════════════════════════
    public function obtenerSedes()
    {
        return $this->modelo->obtenerSedes();
    }


    // ══════════════════════════════════════════════════════
    // REGISTRAR SEDE
    // Sanea y valida los campos recibidos del formulario.
    // Solo letras y espacios en TipoSede y Ciudad.
    // Estado siempre forzado a 'Activo' en el modelo.
    // ══════════════════════════════════════════════════════
    public function registrarSede($datos)
    {
        $tipoSede    = trim($datos['TipoSede']       ?? '');
        $ciudad      = trim($datos['Ciudad']          ?? '');
        $institucion = intval($datos['IdInstitucion'] ?? 0);

        // Regex: solo letras (con tildes y ñ) y espacios, máx 30 caracteres
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

        // Pasa los datos limpios al modelo
        return $this->modelo->registrarSede($tipoSede, $ciudad, $institucion);
    }


    // ══════════════════════════════════════════════════════
    // EDITAR SEDE
    // Sanea y valida los campos recibidos del modal.
    // Verifica que el ID sea válido antes de proceder.
    // Llama a editarSede() del modelo con datos limpios.
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

        return $this->modelo->editarSede($idSede, $tipoSede, $ciudad, $institucion);
    }


    // ══════════════════════════════════════════════════════
    // OBTENER SEDE POR ID
    // Busca y retorna un registro completo por su ID.
    // Usado por el AJAX del modal para precargar los campos.
    // ══════════════════════════════════════════════════════
    public function obtenerSedePorId($idSede)
    {
        $idSede = intval($idSede);
        if ($idSede <= 0) return null;
        return $this->modelo->obtenerSedePorId($idSede);
    }


    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO (toggle Activo / Inactivo)
    // El modelo consulta el estado actual y lo invierte.
    // Solo toca el campo Estado, no modifica nada más.
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($idSede)
    {
        return $this->modelo->cambiarEstado($idSede);
    }
}


// ══════════════════════════════════════════════════════
// PUNTO DE ENTRADA AJAX
// Solo se ejecuta cuando llega una petición POST con 'accion'.
// Limpia el buffer, define JSON como tipo de respuesta
// e instancia el controlador para procesar la acción.
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // Limpia cualquier salida previa que pudiera romper el JSON
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $controlador = new ControladorSede();

    switch ($_POST['accion']) {

        // Registrar nueva sede
        case 'registrar':
            echo json_encode($controlador->registrarSede($_POST));
            break;

        // Editar sede existente desde el modal
        case 'editar':
            echo json_encode($controlador->editarSede($_POST));
            break;

        // Obtener datos de una sede para precargar el modal
        case 'obtener_sede':
            $idSede = intval($_POST['IdSede'] ?? 0);
            echo json_encode(
                $idSede > 0
                    ? $controlador->obtenerSedePorId($idSede)
                    : null
            );
            break;

        // Toggle estado Activo/Inactivo desde el candado
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