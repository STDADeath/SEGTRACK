<?php

$ruta_conexion = __DIR__ . '/../../core/conexion.php';
if (!file_exists($ruta_conexion)) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'Archivo de conexión no encontrado']));
}
require_once $ruta_conexion;
require_once __DIR__ . "/../../Model/ingreso_Visitante/ModeloVisitante.php";

class ControladorVisitante {
    private VisitanteModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new VisitanteModelo($conexion);
    }

    private function campoVacio(array $arr, string $campo): bool {
        return !isset($arr[$campo]) || trim($arr[$campo]) === "";
    }

    public function registrarVisitante(array $datos): array {
        $requeridos = ['IdentificacionVisitante', 'NombreVisitante'];

        foreach ($requeridos as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }

        try {
            $resultado = $this->modelo->insertar($datos);

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'message' => 'Visitante registrado correctamente',
                    'data' => ['IdVisitante' => $resultado['id']]
                ];
            } else {
                return ['success' => false, 'message' => $resultado['error']];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function mostrarVisitantes(): array {
        return $this->modelo->obtenerTodos();
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
    $accion = $_POST['accion'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarVisitante($_POST));
            break;

        case 'mostrar':
            echo json_encode($controlador->mostrarVisitantes());
            break;

        case 'obtener':
            $id = (int)($_POST['IdVisitante'] ?? 0);
            echo json_encode($controlador->obtenerPorId($id));
            break;

        case 'actualizar':
            $id = (int)($_POST['IdVisitante'] ?? 0);
            echo json_encode($controlador->actualizar($id, $_POST));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
