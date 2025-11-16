<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloBitacora.php";

class ControladorBitacora {
    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    private function campoVacio($data, $campo): bool {
        return empty(trim($data[$campo] ?? ""));
    }

    private function fechaValida($fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $d && $d->format('Y-m-d\TH:i') === $fecha;
    }

    public function registrarBitacora($data): array {

        // ✔ campos obligatorios
        $obligatorios = [
            'TurnoBitacora', 'NovedadesBitacora', 'FechaBitacora',
            'IdFuncionario', 'IdIngreso', 'TieneVisitante'
        ];

        foreach ($obligatorios as $c) {
            if ($this->campoVacio($data, $c)) {
                return ['success' => false, 'message' => "Falta el campo: $c"];
            }
        }

        // ✔ validar fecha
        if (!$this->fechaValida($data['FechaBitacora'])) {
            return ['success' => false, 'message' => "Fecha inválida (YYYY-MM-DDTHH:MM)"];
        }

        // convertir fecha
        $data['FechaBitacora'] = DateTime::createFromFormat(
            'Y-m-d\TH:i', $data['FechaBitacora']
        )->format('Y-m-d H:i:s');

        // ✔ validaciones de visitante
        if ($data['TieneVisitante'] === 'si') {

            if ($this->campoVacio($data, 'IdVisitante')) {
                return ['success' => false, 'message' => "ID Visitante obligatorio"];
            }

            if (($data['TraeDispositivo'] ?? 'no') === 'si') {
                if ($this->campoVacio($data, 'IdDispositivo')) {
                    return ['success' => false, 'message' => "ID Dispositivo obligatorio"];
                }
            } else {
                $data['IdDispositivo'] = null;
            }
        } 
        else {
            // sin visitante
            $data['IdVisitante'] = null;
            $data['IdDispositivo'] = null;
            $data['TraeDispositivo'] = 'no';
        }

        // ✔ guardar
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

    public function obtenerPorId($id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    public function actualizar($id, $data): array {
        return $this->modelo->actualizar($id, $data);
    }
}

try {
    if (!isset($conexion)) {
        throw new Exception("No hay conexión a la base de datos");
    }

    $controlador = new ControladorBitacora($conexion);
    $accion = $_POST['accion'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar': echo json_encode($controlador->registrarBitacora($_POST)); break;
        case 'mostrar':   echo json_encode($controlador->mostrarBitacora()); break;
        case 'obtener':   echo json_encode($controlador->obtenerPorId((int)($_POST['IdBitacora'] ?? 0))); break;
        case 'actualizar':echo json_encode($controlador->actualizar((int)($_POST['IdBitacora'] ?? 0), $_POST)); break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Error servidor: " . $e->getMessage()]);
}
