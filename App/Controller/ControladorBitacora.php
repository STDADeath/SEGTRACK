<?php
$ruta_conexion = __DIR__ . "../../Core/conexion.php";

if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {

    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false, 
        'message' => 'Error: Archivo de conexión no encontrado en: ' . $ruta_conexion
    ]));
}

require_once __DIR__ . "../Model/ModeloBitacora.php";

class ControladorBitacora {
    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    private function fechaValida(string $fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    public function registrarBitacora(array $DatosBitacora): array {
        $camposObligatorios = [
            'TurnoBitacora', 
            'NovedadesBitacora', 
            'FechaBitacora', 
            'IdFuncionario', 
            'IdIngreso', 
            'TieneVisitante'
        ];

        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($DatosBitacora, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }


        if (!$this->fechaValida($DatosBitacora['FechaBitacora'])) {
            return ['success' => false, 'message' => "Formato de fecha inválido. Use: YYYY-MM-DDTHH:MM"];
        }

        $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $DatosBitacora['FechaBitacora']);
        $DatosBitacora['FechaBitacora'] = $fecha->format('Y-m-d H:i:s');


        if ($DatosBitacora['TieneVisitante'] === 'si') {

            if ($this->campoVacio($DatosBitacora, 'IdVisitante')) {
                return ['success' => false, 'message' => "Cuando hay visitante, el ID Visitante es obligatorio"];
            }
            

            if (isset($DatosBitacora['TraeDispositivo']) && $DatosBitacora['TraeDispositivo'] === 'si') {
                if ($this->campoVacio($DatosBitacora, 'IdDispositivo')) {
                    return ['success' => false, 'message' => "Cuando el visitante trae dispositivo, el ID Dispositivo es obligatorio"];
                }
            } else {
    
                $DatosBitacora['IdDispositivo'] = null;
            }
        } else {

            $DatosBitacora['IdVisitante'] = null;
            $DatosBitacora['IdDispositivo'] = null;
            $DatosBitacora['TraeDispositivo'] = 'no';
        }

        try {
            $resultado = $this->modelo->insertar($DatosBitacora);

            if ($resultado['success']) {
                return [
                    'success' => true, 
                    'message' => 'Bitácora registrada correctamente', 
                    'data' => ['IdBitacora' => $resultado['id']]
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'No se pudo registrar la bitácora', 
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



try {

    if (!isset($conexion)) {
        throw new Exception("Conexión a la base de datos no disponible");
    }

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
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
    
}

?>