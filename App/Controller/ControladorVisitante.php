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

    // ──────────────────────────────────────────────
    // REGISTRAR
    // ──────────────────────────────────────────────
    public function registrarVisitante(array $datos): array {
        foreach (['IdentificacionVisitante', 'NombreVisitante'] as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }

        // Validar identificación: CC (solo números) o CE (alfanumérico)
        $id = trim($datos['IdentificacionVisitante']);
        if (!preg_match('/^\d{6,10}$/', $id) && !preg_match('/^[A-Za-z0-9\-]{4,20}$/', $id)) {
            return ['success' => false, 'message' => 'Identificación inválida. CC: 6-10 dígitos. CE: 4-20 caracteres alfanuméricos.'];
        }

        // Validar nombre
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]{3,100}$/', trim($datos['NombreVisitante']))) {
            return ['success' => false, 'message' => 'El nombre solo debe contener letras (mínimo 3 caracteres).'];
        }

        // Validar correo si se envía
        if (!empty($datos['CorreoVisitante']) && !filter_var($datos['CorreoVisitante'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El correo electrónico no es válido.'];
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

    // ──────────────────────────────────────────────
    // MOSTRAR CON FILTROS
    // ──────────────────────────────────────────────
    public function mostrarVisitantes(): array {
        $filtros = [];
        $params  = [];

        if (!empty($_POST['identificacion'])) {
            $filtros[]               = "IdentificacionVisitante LIKE :identificacion";
            $params[':identificacion'] = '%' . $_POST['identificacion'] . '%';
        }
        if (!empty($_POST['nombre'])) {
            $filtros[]          = "NombreVisitante LIKE :nombre";
            $params[':nombre']  = '%' . $_POST['nombre'] . '%';
        }
        if (!empty($_POST['estado'])) {
            $filtros[]          = "Estado = :estado";
            $params[':estado']  = $_POST['estado'];
        }

        return $this->modelo->obtenerTodos($filtros, $params);
    }

    // ──────────────────────────────────────────────
    // OBTENER POR ID
    // ──────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    // ──────────────────────────────────────────────
    // ACTUALIZAR
    // ──────────────────────────────────────────────
    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }
}

// ════════════════════════════════════════════════
// RUTEO
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
        case 'mostrar':
            echo json_encode($controlador->mostrarVisitantes());
            break;
        case 'obtener':
            echo json_encode($controlador->obtenerPorId($id));
            break;
        case 'actualizar':
            echo json_encode($controlador->actualizar($id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>