<?php

require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloDotacion.php";

class ControladorDotacion {

    private DotacionModelo $modelo; 

    // Constructor que recibe la conexión y crea una instancia del modelo
    public function __construct($conexion) {
        $this->modelo = new DotacionModelo($conexion);
    }

    // Función privada para verificar si un campo está vacío
    private function campoVacio(array $array, string $campo): bool {
        // Retorna true si el campo no existe o su valor es solo espacios
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    // Función privada que valida que una fecha tenga formato datetime-local
    private function fechaValida(?string $fecha): bool {
        if (empty($fecha)) return true; 
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha); 
        // Compara el valor original con el formato esperado
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    // Función para registrar una nueva dotación
    public function registrarDotacion(array $Datos): array {
        // Campos obligatorios que deben enviarse
        $camposObligatorios = [
            'EstadoDotacion',
            'TipoDotacion',
            'IdFuncionario'
        ];

        // Validación de campos obligatorios
        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($Datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }

        // Validación y conversión de fechas si existen
        foreach (['FechaDevolucion', 'FechaEntrega'] as $campoFecha) {
            // Validar el formato de fecha
            if (isset($Datos[$campoFecha]) && !$this->fechaValida($Datos[$campoFecha])) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campoFecha. Use: YYYY-MM-DDTHH:MM"];
            }

            // Convertir fecha al formato MySQL 'Y-m-d H:i:s'
            if (!empty($Datos[$campoFecha])) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $Datos[$campoFecha]);
                $Datos[$campoFecha] = $fecha->format('Y-m-d H:i:s');
            }
        }

        // Intentar insertar la dotación en la base de datos
        try {
            $resultado = $this->modelo->insertar($Datos);

            // Si la inserción fue exitosa
            if ($resultado['success']) {
                return [
                    'success' => true,
                    'message' => 'Dotación registrada correctamente',
                    'data' => ['IdDotacion' => $resultado['id']]
                ];
            } else {
                // Si ocurrió un error en la base de datos
                return [
                    'success' => false,
                    'message' => 'No se pudo registrar la dotación',
                    'error' => $resultado['error'] ?? 'Error desconocido en la base de datos'
                ];
            }
        } catch (Exception $e) {
            // Captura cualquier excepción y devuelve un mensaje de error
            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ];
        }
    }

    // Función para obtener todas las dotaciones
    public function mostrarDotaciones(): array {
        return $this->modelo->obtenerTodos();
    }

    // Función para obtener una dotación específica por ID
    public function obtenerPorId(int $IdDotacion): ?array {
        return $this->modelo->obtenerPorId($IdDotacion);
    }

    // Función para actualizar una dotación existente
    public function actualizarDotacion(int $IdDotacion, array $Datos): array {
        // Validación y conversión de fechas antes de actualizar
        foreach (['FechaDevolucion', 'FechaEntrega'] as $campoFecha) {
            if (isset($Datos[$campoFecha]) && !$this->fechaValida($Datos[$campoFecha])) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campoFecha"];
            }

            if (!empty($Datos[$campoFecha])) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $Datos[$campoFecha]);
                $Datos[$campoFecha] = $fecha->format('Y-m-d H:i:s');
            }
        }

        // Llamar al modelo para actualizar la dotación
        return $this->modelo->actualizar($IdDotacion, $Datos);
    }

    // Función para eliminar una dotación por ID
    public function eliminarDotacion(int $IdDotacion): array {
        return $this->modelo->eliminar($IdDotacion);
    }
}

// Bloque principal para manejar las acciones enviadas por POST
try {
    if (!isset($conexion)) {
        throw new Exception("Conexión a la base de datos no disponible");
    }

    $controlador = new ControladorDotacion($conexion);
    $accion = $_POST['accion'] ?? null;

    // Configura la respuesta como JSON
    header('Content-Type: application/json; charset=utf-8');

    // Switch que rutea la acción recibida
    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarDotacion($_POST));
            break;

        case 'mostrar':
            echo json_encode($controlador->mostrarDotaciones());
            break;

        case 'obtener':
            $id = isset($_POST['IdDotacion']) ? (int)$_POST['IdDotacion'] : 0;
            echo json_encode($controlador->obtenerPorId($id));
            break;

        case 'actualizar':
            $id = isset($_POST['IdDotacion']) ? (int)$_POST['IdDotacion'] : 0;
            echo json_encode($controlador->actualizarDotacion($id, $_POST));
            break;

        case 'eliminar':
            $id = isset($_POST['IdDotacion']) ? (int)$_POST['IdDotacion'] : 0;
            echo json_encode($controlador->eliminarDotacion($id));
            break;

        default:
            // Acción no reconocida
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }
} catch (Exception $e) {

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
