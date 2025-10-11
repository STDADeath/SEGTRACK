<?php
header('Content-Type: application/json; charset=utf-8');

// 🧩 Depuración temporal
file_put_contents("debug_post.txt", print_r($_POST, true));

// ✅ Ruta de conexión
$ruta_conexion = __DIR__ . '/../../Core/conexion.php';

// ✅ Verificar existencia de la conexión
if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {
    die(json_encode([
        'success' => false,
        'message' => 'Error: Archivo de conexión no encontrado en: ' . $ruta_conexion
    ]));
}

// ✅ Incluir modelo y librería QR
require_once __DIR__ . "/../../model/parqueadero_dispositivo/ModeloDispositivo.php";
require_once __DIR__ . "/../../libs/phpqrcode/phpqrcode.php";

class ControladorDispositivo {
    private ModeloDispositivo $modelo;

    public function __construct($conexion) {
        $this->modelo = new ModeloDispositivo($conexion);
    }

    private function campoVacio($campo): bool {
        return !isset($campo) || trim($campo) === "";
    }

    public function registrarDispositivo(array $datos): array {
        $tipo = $datos['TipoDispositivo'] ?? $datos['tipodispositivo'] ?? null;
        $marca = $datos['MarcaDispositivo'] ?? $datos['marca'] ?? null;
        $otroTipo = $datos['OtroTipoDispositivo'] ?? $datos['otrotipodispositivo'] ?? null;
        $idFuncionario = $datos['IdFuncionario'] ?? $datos['idfuncionario'] ?? null;
        $idVisitante = $datos['IdVisitante'] ?? $datos['idvisitante'] ?? null;

        if ($this->campoVacio($tipo)) {
            return ['success' => false, 'message' => 'Falta el campo obligatorio: Tipo de dispositivo'];
        }

        if ($this->campoVacio($marca)) {
            return ['success' => false, 'message' => 'Falta el campo obligatorio: Marca del dispositivo'];
        }

        if ($tipo === 'Otro' && $this->campoVacio($otroTipo)) {
            return ['success' => false, 'message' => 'Debe especificar el tipo de dispositivo'];
        }

        $tipoFinal = ($tipo === 'Otro') ? $otroTipo : $tipo;

        try {
            $resultado = $this->modelo->registrarDispositivo($tipoFinal, $marca, $idFuncionario, $idVisitante);

            if ($resultado['success']) {
                $idDispositivo = $resultado['id'];

                $rutaQR = __DIR__ . "/../../public/qr_dispositivos/";
                if (!file_exists($rutaQR)) {
                    mkdir($rutaQR, 0777, true);
                }

                $nombreArchivoQR = "Dispositivo_" . $idDispositivo . ".png";
                $contenidoQR = "ID: $idDispositivo | Tipo: $tipoFinal | Marca: $marca";
                QRcode::png($contenidoQR, $rutaQR . $nombreArchivoQR, QR_ECLEVEL_L, 5);

                $this->modelo->actualizarQR($idDispositivo, "public/qr_dispositivos/" . $nombreArchivoQR);

                return [
                    "success" => true,
                    "message" => "✅ Dispositivo registrado correctamente",
                    "data" => [
                        "IdDispositivo" => $idDispositivo,
                        "RutaQR" => "public/qr_dispositivos/" . $nombreArchivoQR
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo registrar el dispositivo',
                    'error' => $resultado['error'] ?? 'Error desconocido en la base de datos'
                ];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    public function mostrarDispositivos(): array {
        return $this->modelo->obtenerTodos();
    }

    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    public function actualizar(int $id, array $datos): array {
        return $this->modelo->actualizar($id, $datos);
    }
}

// =======================
// ✅ Manejo de acciones
// =======================
try {
    if (!isset($conexion)) {
        throw new Exception("Conexión a la base de datos no disponible");
    }

    $controlador = new ControladorDispositivo($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarDispositivo($_POST));
            break;

        case 'mostrar':
            echo json_encode($controlador->mostrarDispositivos());
            break;

        case 'obtener':
            $id = isset($_POST['IdDispositivo']) ? (int)$_POST['IdDispositivo'] : 0;
            echo json_encode($controlador->obtenerPorId($id));
            break;

        case 'actualizar':
            $id = isset($_POST['IdDispositivo']) ? (int)$_POST['IdDispositivo'] : 0;
            echo json_encode($controlador->actualizar($id, $_POST));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
