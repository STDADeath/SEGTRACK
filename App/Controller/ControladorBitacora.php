<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloBitacora.php";

class ControladorBitacora {

    private BitacoraModelo $modelo;

    // Constructor: recibe la conexión y crea la instancia del modelo
    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    // Función privada que verifica si un campo está vacío
    private function campoVacio($data, $campo): bool {
        return empty(trim($data[$campo] ?? ""));
    }

    // Convierte una fecha de formato HTML datetime-local a formato MySQL
    private function convertirFecha(?string $fecha): ?string {
        if (empty($fecha)) return null;
        $obj = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $obj ? $obj->format('Y-m-d H:i:s') : null;
    }

    // Valida y convierte varias fechas a formato MySQL
    private function validarFechas(array &$datos, array $campos): array {
        foreach ($campos as $campo) {
            if (!empty($datos[$campo])) {
                $convertida = $this->convertirFecha($datos[$campo]);
                if (!$convertida) {
                    return ['success' => false, 'message' => "Formato de fecha inválido en $campo (YYYY-MM-DDTHH:MM)"];
                }
                $datos[$campo] = $convertida; // Actualiza el valor en el array
            }
        }
        return ['success' => true]; // Todas las fechas válidas
    }

    // Función para registrar una nueva bitácora
    public function registrarBitacora(array $data): array {
        $obligatorios = ['TurnoBitacora', 'NovedadesBitacora', 'FechaBitacora', 'IdFuncionario', 'IdIngreso', 'TieneVisitante'];

        // Verificación de campos obligatorios
        foreach ($obligatorios as $c) {
            if ($this->campoVacio($data, $c)) {
                return ['success' => false, 'message' => "Falta el campo: $c"];
            }
        }

        // Validar y convertir la fecha
        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        // Validación de visitantes y dispositivos
        if (($data['TieneVisitante'] ?? 'no') === 'si') {
            if ($this->campoVacio($data, 'IdVisitante')) {
                return ['success' => false, 'message' => "ID Visitante obligatorio"];
            }
            if (($data['TraeDispositivo'] ?? 'no') === 'si' && $this->campoVacio($data, 'IdDispositivo')) {
                return ['success' => false, 'message' => "ID Dispositivo obligatorio"];
            }
            $data['IdDispositivo'] = $data['IdDispositivo'] ?? null;
        } else {
            $data['IdVisitante'] = null;
            $data['IdDispositivo'] = null;
            $data['TraeDispositivo'] = 'no';
        }

        // Guardar la bitácora usando el modelo
        try {
            $res = $this->modelo->insertar($data);
            return $res['success']
                ? ['success' => true, 'message' => 'Bitácora registrada', 'data' => ['IdBitacora' => $res['id']]]
                : ['success' => false, 'message' => 'No se pudo registrar', 'error' => $res['error'] ?? 'Error BD'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Error: " . $e->getMessage()];
        }
    }

    // Obtiene todas las bitácoras con filtros opcionales
    public function obtenerBitacoras($filtros = [], $params = []) {
        return $this->modelo->obtenerBitacoras($filtros, $params);
    }

    // Obtiene una bitácora por su ID
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    // Actualiza una bitácora existente
    public function actualizar(int $id, array $data): array {
        // Valida y convierte la fecha antes de actualizar
        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        // Llama al modelo para actualizar
        return $this->modelo->actualizar($id, $data);
    }
}

// Bloque de ruteo y manejo de acciones
try {
    if (!isset($conexion)) throw new Exception("No hay conexión a la base de datos");

    $controlador = new ControladorBitacora($conexion);
    $accion = $_POST['accion'] ?? "";
    $id = isset($_POST['IdBitacora']) ? (int)$_POST['IdBitacora'] : 0;

    // Configura la respuesta como JSON
    header('Content-Type: application/json; charset=utf-8');

    // Rutea la acción según el valor de 'accion' enviado por POST
    switch ($accion) {
        case 'registrar': 
            echo json_encode($controlador->registrarBitacora($_POST)); 
            break;
        case 'mostrar': 
            echo json_encode($controlador->obtenerBitacoras($_POST)); 
            break;
        case 'obtener': 
            echo json_encode($controlador->obtenerPorId($id)); 
            break;
        case 'actualizar': 
            echo json_encode($controlador->actualizar($id, $_POST)); 
            break;
        default: 
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']); 
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Error servidor: " . $e->getMessage()]);
}
