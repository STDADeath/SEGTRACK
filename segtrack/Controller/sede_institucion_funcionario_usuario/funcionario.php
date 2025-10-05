<?php

$ruta_conexion = __DIR__ . '/../../core/conexion.php';

if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false, 
        'message' => 'Error: Archivo de conexión no encontrado en: ' . $ruta_conexion
    ]));
} 

require_once __DIR__ . "/IngresoFuncionario.php";

class ControladorFuncionario {
    private FuncionarioModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new FuncionarioModelo($conexion);
    }

    /** ✅ Verifica si un campo está vacío */
    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    /** ✅ Registrar funcionario */
    public function registrarFuncionario(array $datos): array {
        $camposObligatorios = [
            'CargoFuncionario', 
            'QrCodigoFuncionario', 
            'NombreFuncionario', 
            'IdSede', 
            'TelefonoFuncionario', 
            'DocumentoFuncionario', 
            'CorreoFuncionario'
        ];

        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }

        return $this->modelo->insertar($datos);
    }

    /** ✅ Mostrar todos */
    public function mostrarFuncionario(): array {
        return $this->modelo->obtenerTodos();
    }

    /** ✅ Obtener por ID */
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    /** ✅ Actualizar */
    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }

    /** ✅ Eliminar */
    public function eliminar(int $id): array {
        return $this->modelo->eliminar($id);
    }
}

// ==========================
// 🚀 Control de peticiones
// ==========================
try {
    if (!isset($conexion)) {
        throw new Exception("Conexión a la base de datos no disponible");
    }

    $controlador = new ControladorFuncionario($conexion);
    $accion = $_POST['accion'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarFuncionario($_POST));
            break;

        case 'mostrar':
            echo json_encode($controlador->mostrarFuncionario());
            break;

        case 'obtener':
            $id = (int)($_POST['IdFuncionario'] ?? 0);
            echo json_encode($controlador->obtenerPorId($id));
            break;

        case 'actualizar':
            $id = (int)($_POST['IdFuncionario'] ?? 0);
            echo json_encode($controlador->actualizar($id, $_POST));
            break;

        case 'eliminar':
            $id = (int)($_POST['IdFuncionario'] ?? 0);
            echo json_encode($controlador->eliminar($id));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
