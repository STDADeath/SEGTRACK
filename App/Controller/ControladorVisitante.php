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

    // ── NUEVO: Verificar duplicados vía AJAX ────────────────────────────────
    public function verificarDuplicado(array $datos): array {
        $identificacion = trim($datos['IdentificacionVisitante'] ?? '');
        $correo         = trim($datos['CorreoVisitante'] ?? '');
        $excludeId      = (int)($datos['excludeId'] ?? 0);

        if (empty($identificacion) && empty($correo)) {
            return ['duplicado' => false];
        }

        return $this->modelo->existeDuplicado($identificacion, $correo, $excludeId);
    }

    public function registrarVisitante(array $datos): array {
        foreach (['IdentificacionVisitante', 'NombreVisitante'] as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
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

        // ── Verificar duplicados antes de insertar ──────────────────────────
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

    public function mostrarVisitantes(): array {
        $filtros = [];
        $params  = [];

        if (!empty($_POST['identificacion'])) {
            $filtros[]                 = "IdentificacionVisitante LIKE :identificacion";
            $params[':identificacion'] = '%' . $_POST['identificacion'] . '%';
        }
        if (!empty($_POST['nombre'])) {
            $filtros[]         = "NombreVisitante LIKE :nombre";
            $params[':nombre'] = '%' . $_POST['nombre'] . '%';
        }
        if (!empty($_POST['estado'])) {
            $filtros[]         = "Estado = :estado";
            $params[':estado'] = $_POST['estado'];
        }

        return $this->modelo->obtenerTodos($filtros, $params);
    }

    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }
}

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
        case 'verificar':                                          // ← NUEVO
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
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>