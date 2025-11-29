<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloBitacora.php";

class ControladorBitacora {

    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    // Verifica si un campo obligatorio viene vacío
    private function campoVacio($data, $campo): bool {
        return empty(trim($data[$campo] ?? ""));
    }

    // Convierte datetime-local al formato MySQL
    private function convertirFecha(?string $fecha): ?string {
        if (empty($fecha)) return null;
        $obj = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $obj ? $obj->format('Y-m-d H:i:s') : null;
    }

    // Procesa y valida fechas múltiples
    private function validarFechas(array &$datos, array $campos): array {
        foreach ($campos as $campo) {
            if (!empty($datos[$campo])) {
                $convertida = $this->convertirFecha($datos[$campo]);
                if (!$convertida) {
                    return ['success' => false, 'message' => "Formato de fecha inválido en $campo"];
                }
                $datos[$campo] = $convertida;
            }
        }
        return ['success' => true];
    }

    public function registrarBitacora(array $data): array {

        // Validación de campos obligatorios
        $obligatorios = ['TurnoBitacora', 'NovedadesBitacora', 'FechaBitacora', 'IdFuncionario', 'IdIngreso', 'TieneVisitante'];
        foreach ($obligatorios as $c) {
            if ($this->campoVacio($data, $c)) {
                return ['success' => false, 'message' => "Falta el campo: $c"];
            }
        }

        // Validación de fecha
        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        // Lógica visitante / dispositivo
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

        try {
            $res = $this->modelo->insertar($data);

            return $res['success']
                ? ['success' => true, 'message' => 'Bitácora registrada', 'data' => ['IdBitacora' => $res['id']]]
                : ['success' => false, 'message' => 'No se pudo registrar', 'error' => $res['error'] ?? 'Error BD'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => "Error: " . $e->getMessage()];
        }
    }

    public function mostrarBitacora(): array {
        return $this->modelo->obtenerTodos();
    }

    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    public function actualizar(int $id, array $data): array {

        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        return $this->modelo->actualizar($id, $data);
    }
}

try {
    if (!isset($conexion)) throw new Exception("No hay conexión a la base de datos");

    $controlador = new ControladorBitacora($conexion);
    $accion = $_POST['accion'] ?? "";
    $id = isset($_POST['IdBitacora']) ? (int)$_POST['IdBitacora'] : 0;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar': echo json_encode($controlador->registrarBitacora($_POST)); break;
        case 'mostrar': echo json_encode($controlador->mostrarBitacora()); break;
        case 'obtener': echo json_encode($controlador->obtenerPorId($id)); break;
        case 'actualizar': echo json_encode($controlador->actualizar($id, $_POST)); break;
        default: echo json_encode(['success' => false, 'message' => 'Acción no reconocida']); break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Error servidor: " . $e->getMessage()]);
}
?>
