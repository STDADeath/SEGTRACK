<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloDotacion.php";

class ControladorDotacion {

    private DotacionModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new DotacionModelo($conexion);
    }

    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    private function fechaValida(?string $fecha): bool {
        if (empty($fecha)) return true;
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    private function convertirFecha(array &$datos, array $campos): array {
        foreach ($campos as $campo) {
            if (isset($datos[$campo]) && !$this->fechaValida($datos[$campo])) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campo"];
            }
            if (!empty($datos[$campo])) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $datos[$campo]);
                $datos[$campo] = $fecha->format('Y-m-d H:i:s');
            }
        }
        return ['success' => true];
    }

    public function registrarDotacion(array $datos): array {
        foreach (['EstadoDotacion', 'TipoDotacion', 'FechaEntrega', 'IdFuncionario'] as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }
        $val = $this->convertirFecha($datos, ['FechaEntrega', 'FechaDevolucion']);
        if (!$val['success']) return $val;

        try {
            $res = $this->modelo->insertar($datos);
            return $res['success']
                ? ['success' => true,  'message' => 'Dotación registrada correctamente',
                   'data'    => ['IdDotacion' => $res['id']]]
                : ['success' => false, 'message' => 'No se pudo registrar',
                   'error'   => $res['error'] ?? 'Error BD'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    public function mostrarDotaciones(): array  {
        $filtros = [];
        $params  = [];

        if (!empty($_POST['estado'])) {
            $filtros[]         = "d.EstadoDotacion = :estado";
            $params[':estado'] = $_POST['estado'];
        }
        if (!empty($_POST['tipo'])) {
            $filtros[]       = "d.TipoDotacion = :tipo";
            $params[':tipo'] = $_POST['tipo'];
        }
        if (!empty($_POST['funcionario'])) {
            $filtros[]              = "f.NombreFuncionario LIKE :funcionario";
            $params[':funcionario'] = '%' . $_POST['funcionario'] . '%';
        }

        return $this->modelo->obtenerTodos($filtros, $params);
    }
    public function obtenerPorId(int $id): ?array { return $this->modelo->obtenerPorId($id); }

    public function actualizarDotacion(int $id, array $datos): array {
        $val = $this->convertirFecha($datos, ['FechaEntrega', 'FechaDevolucion']);
        if (!$val['success']) return $val;
        return $this->modelo->actualizar($id, $datos);
    }

    public function eliminarDotacion(int $id): array { return $this->modelo->eliminar($id); }

    // Llama a obtenerFuncionarios() — todos los activos
    public function obtenerFuncionarios(): array { return $this->modelo->obtenerFuncionarios(); }
}

// ════════════════════════════════════════════════
// RUTEO
// ════════════════════════════════════════════════
try {
    if (!isset($conexion)) throw new Exception("Conexión no disponible");

    $controlador = new ControladorDotacion($conexion);
    $accion      = $_POST['accion'] ?? null;
    $id          = isset($_POST['IdDotacion']) ? (int)$_POST['IdDotacion'] : 0;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarDotacion($_POST));
            break;
        case 'mostrar':
            echo json_encode($controlador->mostrarDotaciones());
            break;
        case 'obtener':
            echo json_encode($controlador->obtenerPorId($id));
            break;
        case 'actualizar':
            echo json_encode($controlador->actualizarDotacion($id, $_POST));
            break;
        case 'eliminar':
            echo json_encode($controlador->eliminarDotacion($id));
            break;
        case 'personal_seguridad': // ← el JS envía este valor
        case 'funcionarios':       // ← por si acaso
            echo json_encode($controlador->obtenerFuncionarios());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error servidor: ' . $e->getMessage()]);
}
?>