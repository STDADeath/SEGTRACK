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

require_once __DIR__ . "/../../Model/bitacora_dotacion/ModeloDotacion.php";

class ControladorDotacion {
    private DotacionModelo $modelo; 

    public function __construct($conexion) {
        $this->modelo = new DotacionModelo($conexion);
    }

    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    private function fechaValida(?string $fecha): bool {
        if (empty($fecha)) return true; // fechas opcionales
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    public function registrarDotacion(array $Datos): array {
        $camposObligatorios = [
            'EstadoDotacion',
            'TipoDotacion',
            'IdFuncionario'
        ];

        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($Datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }


        foreach (['FechaDevolucion', 'FechaEntrega'] as $campoFecha) {
            if (isset($Datos[$campoFecha]) && !$this->fechaValida($Datos[$campoFecha])) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campoFecha. Use: YYYY-MM-DDTHH:MM"];
            }

            if (!empty($Datos[$campoFecha])) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $Datos[$campoFecha]);
                $Datos[$campoFecha] = $fecha->format('Y-m-d H:i:s');
            }
        }

        try {
            $resultado = $this->modelo->insertar($Datos);

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'message' => 'Dotación registrada correctamente',
                    'data' => ['IdDotacion' => $resultado['id']]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo registrar la dotación',
                    'error' => $resultado['error'] ?? 'Error desconocido en la base de datos'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ];
        }
    }

    public function mostrarDotaciones(): array {
        return $this->modelo->obtenerTodos();
    }

    public function obtenerPorId(int $IdDotacion): ?array {
        return $this->modelo->obtenerPorId($IdDotacion);
    }

    public function actualizarDotacion(int $IdDotacion, array $Datos): array {

        foreach (['FechaDevolucion', 'FechaEntrega'] as $campoFecha) {
            if (isset($Datos[$campoFecha]) && !$this->fechaValida($Datos[$campoFecha])) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campoFecha"];
            }

            if (!empty($Datos[$campoFecha])) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $Datos[$campoFecha]);
                $Datos[$campoFecha] = $fecha->format('Y-m-d H:i:s');
            }
        }

        return $this->modelo->actualizar($IdDotacion, $Datos);
    }

    public function eliminarDotacion(int $IdDotacion): array {
        return $this->modelo->eliminar($IdDotacion);
    }
}



try {
    if (!isset($conexion)) {
        throw new Exception("Conexión a la base de datos no disponible");
    }

    $controlador = new ControladorDotacion($conexion);
    $accion = $_POST['accion'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

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
