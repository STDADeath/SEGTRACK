<?php
require_once __DIR__ . "/../../config/conexion.php"; 
require_once __DIR__ . "/../../Model/bitacora_dotacion/ModeloBitacora.php";

class ControladorBitacora {
    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    // Valida datetime-local (ejemplo: 2025-09-29T10:30)
    private function fechaValida(string $fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    public function registrarBitacora(array $DatosBitacora): array {
        $camposObligatorios = ['TurnoBitacora','NovedadesBitacora','FechaBitacora','IdFuncionario','IdIngreso'];

        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($DatosBitacora, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }

        // Validar fecha
        if ($this->fechaValida($DatosBitacora['FechaBitacora'])) {
            $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $DatosBitacora['FechaBitacora']);
            $DatosBitacora['FechaBitacora'] = $fecha->format('Y-m-d H:i:s');
        } else {
            return ['success' => false, 'message' => "Formato de fecha inválido"];
        }

        // Campos opcionales
        $DatosBitacora['IdVisitante']   = $DatosBitacora['IdVisitante']   ?? null;
        $DatosBitacora['IdDispositivo'] = $DatosBitacora['IdDispositivo'] ?? null;

        $resultado = $this->modelo->insertar($DatosBitacora);

        return $resultado['success']
            ? ['success' => true, 'message' => 'Bitácora registrada correctamente', 'data' => ['IdBitacora' => $resultado['id']]]
            : ['success' => false, 'message' => 'No se pudo registrar la bitácora', 'error' => $resultado['error'] ?? null];
    }

    public function mostrarBitacora(): array {
        return $this->modelo->obtenerTodos();
    }

    public function obtenerPorId(int $IdBitacora): ?array {
        return $this->modelo->obtenerPorId($IdBitacora);
    }

    public function actualizar(int $IdBitacora, array $DatosBitacora): array {
        return $this->modelo->actualizar($IdBitacora, $DatosBitacora);
    }
}

// =================== Ruteo de acciones ===================
$controlador = new ControladorBitacora($conexion);
$accion = $_POST['accion'] ?? null;

header('Content-Type: application/json; charset=utf-8');

switch ($accion) {
    case 'registrar':
        echo json_encode($controlador->registrarBitacora($_POST));
        break;

    case 'mostrar':
        echo json_encode($controlador->mostrarBitacora());
        break;

    case 'obtener':
        $id = isset($_POST['IdBitacora']) ? (int)$_POST['IdBitacora'] : 0;
        echo json_encode($controlador->obtenerPorId($id));
        break;

    case 'actualizar':
        $id = isset($_POST['IdBitacora']) ? (int)$_POST['IdBitacora'] : 0;
        echo json_encode($controlador->actualizar($id, $_POST));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
?>
