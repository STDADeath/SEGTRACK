<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloVisitante.php";

class ControladorVisitante {
    private VisitanteModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new VisitanteModelo($conexion);
    }

    private function campoVacio(array $arr, string $campo): bool {
        return !isset($arr[$campo]) || trim($arr[$campo]) === "";
    }

    // ══════════════════════════════════════════════
    // VERIFICAR DUPLICADOS
    // ══════════════════════════════════════════════
    public function verificarDuplicado(array $datos): array {
        $identificacion = trim($datos['IdentificacionVisitante'] ?? '');
        $correo         = trim($datos['CorreoVisitante'] ?? '');
        $excludeId      = (int)($datos['excludeId'] ?? 0);

        if (empty($identificacion) && empty($correo)) {
            return ['duplicado' => false];
        }

        return $this->modelo->existeDuplicado($identificacion, $correo, $excludeId);
    }

    // ══════════════════════════════════════════════
    // REGISTRAR
    // ══════════════════════════════════════════════
    public function registrarVisitante(array $datos): array {
        foreach (['IdentificacionVisitante', 'NombreVisitante', 'IdSede'] as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }

        if (!is_numeric($datos['IdSede']) || (int)$datos['IdSede'] <= 0) {
            return ['success' => false, 'message' => 'Debe seleccionar una sede válida.'];
        }

        $id = trim($datos['IdentificacionVisitante']);
        if (!preg_match('/^\d{6,11}$/', $id)) {
            return ['success' => false, 'message' => 'Identificación inválida. Ingrese solo números (6 a 11 dígitos).'];
        }

        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]{3,100}$/u', trim($datos['NombreVisitante']))) {
            return ['success' => false, 'message' => 'El nombre solo debe contener letras (mínimo 3 caracteres).'];
        }

        $correo = trim($datos['CorreoVisitante'] ?? '');
        if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El correo electrónico no es válido.'];
        }

        $duplicado = $this->modelo->existeDuplicado($id, $correo);
        if ($duplicado['duplicado']) {
            return ['success' => false, 'message' => $duplicado['message']];
        }

        try {
            $res = $this->modelo->insertar($datos);
            return $res['success']
                ? ['success' => true,  'message' => 'Visitante registrado correctamente', 'data' => ['IdVisitante' => $res['id']]]
                : ['success' => false, 'message' => $res['error']];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // PREPARAR DATOS PARA LA VISTA LISTA
    // Retorna $visitantes e $instituciones listos
    // para ser usados directamente en VisitanteLista.php
    // ══════════════════════════════════════════════
    public function prepararLista(): array {
        $filtros = [];
        $params  = [];

        if (!empty($_GET['identificacion'])) {
            $filtros[]                 = "v.IdentificacionVisitante LIKE :identificacion";
            $params[':identificacion'] = '%' . $_GET['identificacion'] . '%';
        }
        if (!empty($_GET['nombre'])) {
            $filtros[]         = "v.NombreVisitante LIKE :nombre";
            $params[':nombre'] = '%' . $_GET['nombre'] . '%';
        }
        if (!empty($_GET['estado'])) {
            $filtros[]         = "v.Estado = :estado";
            $params[':estado'] = $_GET['estado'];
        }
        if (!empty($_GET['idInstitucion'])) {
            $filtros[]                = "s.IdInstitucion = :idInstitucion";
            $params[':idInstitucion'] = (int)$_GET['idInstitucion'];
        }

        return [
            'visitantes'   => $this->modelo->obtenerTodos($filtros, $params),
            'instituciones' => $this->modelo->obtenerInstituciones(),
        ];
    }

    // ══════════════════════════════════════════════
    // MOSTRAR CON FILTROS (para llamadas AJAX)
    // ══════════════════════════════════════════════
    public function mostrarVisitantes(): array {
        $filtros = [];
        $params  = [];

        if (!empty($_POST['identificacion'])) {
            $filtros[]                 = "v.IdentificacionVisitante LIKE :identificacion";
            $params[':identificacion'] = '%' . $_POST['identificacion'] . '%';
        }
        if (!empty($_POST['nombre'])) {
            $filtros[]         = "v.NombreVisitante LIKE :nombre";
            $params[':nombre'] = '%' . $_POST['nombre'] . '%';
        }
        if (!empty($_POST['estado'])) {
            $filtros[]         = "v.Estado = :estado";
            $params[':estado'] = $_POST['estado'];
        }
        if (!empty($_POST['idInstitucion'])) {
            $filtros[]                = "s.IdInstitucion = :idInstitucion";
            $params[':idInstitucion'] = (int)$_POST['idInstitucion'];
        }

        return $this->modelo->obtenerTodos($filtros, $params);
    }

    // ══════════════════════════════════════════════
    // OBTENER POR ID
    // ══════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    // ══════════════════════════════════════════════
    // ACTUALIZAR
    // ══════════════════════════════════════════════
    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }

    // ══════════════════════════════════════════════
    // CAMBIAR ESTADO
    // ══════════════════════════════════════════════
    public function cambiarEstado(int $id, string $estado): array {
        return $this->modelo->cambiarEstado($id, $estado);
    }

    // ══════════════════════════════════════════════
    // OBTENER INSTITUCIONES
    // ══════════════════════════════════════════════
    public function obtenerInstituciones(): array {
        return $this->modelo->obtenerInstituciones();
    }

    // ══════════════════════════════════════════════
    // OBTENER SEDES POR INSTITUCIÓN
    // ══════════════════════════════════════════════
    public function obtenerSedes(int $idInstitucion): array {
        return $this->modelo->obtenerSedesPorInstitucion($idInstitucion);
    }
}

// ════════════════════════════════════════════════
// RUTEO (solo cuando se llama vía AJAX/POST)
// ════════════════════════════════════════════════
try {
    if (!isset($conexion)) throw new Exception("Conexión no disponible");

    $controlador = new ControladorVisitante($conexion);
    $accion      = $_POST['accion'] ?? null;
    $id          = (int)($_POST['IdVisitante'] ?? 0);

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarVisitante($_POST));
            break;
        case 'verificar':
            echo json_encode($controlador->verificarDuplicado($_POST));
            break;
        case 'mostrar':
            echo json_encode($controlador->mostrarVisitantes());
            break;
        case 'obtener':
            echo json_encode($controlador->obtenerPorId($id));
            break;
        case 'actualizar':
            echo json_encode($controlador->actualizar($id, $_POST));
            break;
        case 'cambiar_estado':
            $nuevo = $_POST['nuevoEstado'] ?? '';
            if (!in_array($nuevo, ['Activo', 'Inactivo'])) {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
                break;
            }
            echo json_encode($controlador->cambiarEstado($id, $nuevo));
            break;
        case 'obtener_instituciones':
            echo json_encode($controlador->obtenerInstituciones());
            break;
        case 'obtener_sedes':
            $idInstitucion = (int)($_POST['IdInstitucion'] ?? 0);
            if ($idInstitucion <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de institución inválido']);
                break;
            }
            echo json_encode(['success' => true, 'sedes' => $controlador->obtenerSedes($idInstitucion)]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>