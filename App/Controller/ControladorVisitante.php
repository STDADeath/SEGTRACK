<?php
// Incluye la conexión a la base de datos
require_once __DIR__ . "/../Core/conexion.php";
// Incluye el modelo de Visitante para interactuar con la base de datos
require_once __DIR__ . "/../Model/ModeloVisitante.php";

class ControladorVisitante {
    // Instancia del modelo de Visitante
    private VisitanteModelo $modelo;

    // Constructor: recibe la conexión y crea la instancia del modelo
    public function __construct($conexion) {
        $this->modelo = new VisitanteModelo($conexion);
    }

    // Función privada para verificar si un campo está vacío
    private function campoVacio(array $arr, string $campo): bool {
        return !isset($arr[$campo]) || trim($arr[$campo]) === "";
    }

    // Método para registrar un visitante
    public function registrarVisitante(array $datos): array {
        // Campos obligatorios para registrar un visitante
        $requeridos = ['IdentificacionVisitante', 'NombreVisitante'];

        // Verifica que los campos obligatorios no estén vacíos
        foreach ($requeridos as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }

        try {
            // Llama al método insertar del modelo para guardar en la base de datos
            $resultado = $this->modelo->insertar($datos);

            if ($resultado['success']) {
                // Si se insertó correctamente, devuelve éxito y el ID generado
                return [
                    'success' => true,
                    'message' => 'Visitante registrado correctamente',
                    'data' => ['IdVisitante' => $resultado['id']]
                ];
            } else {
                // Si hubo error en la base de datos, devuelve el mensaje de error
                return ['success' => false, 'message' => $resultado['error']];
            }
        } catch (Exception $e) {
            // Captura errores de ejecución y los devuelve
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Método para obtener todos los visitantes
    public function mostrarVisitantes(): array {
        return $this->modelo->obtenerTodos();
    }

    // Método para obtener un visitante por su ID
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    // Método para actualizar los datos de un visitante
    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }
}

// Bloque de manejo de peticiones POST
try {
    // Verifica que la conexión exista
    if (!isset($conexion)) throw new Exception("Conexión no disponible");

    // Crea la instancia del controlador
    $controlador = new ControladorVisitante($conexion);
    // Obtiene la acción enviada por POST
    $accion = $_POST['accion'] ?? null;

    // Configura la respuesta en formato JSON
    header('Content-Type: application/json; charset=utf-8');

    // Ruteo de acciones según lo enviado por POST
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
            // Si la acción no está definida, devuelve un error
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Exception $e) {
    // Captura cualquier error del servidor y lo devuelve en JSON
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
