<?php
// ✅ Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// ✅ Limpiar cualquier salida previa
ob_start();

// ✅ Forzar JSON como respuesta
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ✅ Log de inicio
file_put_contents(__DIR__ . '/debug_log.txt', date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    // Log de POST
    file_put_contents(__DIR__ . '/debug_log.txt', "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // ✅ Verificar conexión
    $ruta_conexion = __DIR__ . '/../../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión cargada\n", FILE_APPEND);

    if (!isset($conexion)) {
        throw new Exception("Variable \$conexion no inicializada");
    }

    // ✅ Verificar que la conexión sea PDO
    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión verificada como PDO\n", FILE_APPEND);

    // ✅ Incluir modelo
    $ruta_modelo = __DIR__ . "/../../model/parqueadero_dispositivo/ModeloDispositivo.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;
    file_put_contents(__DIR__ . '/debug_log.txt', "Modelo cargado\n", FILE_APPEND);

    class ControladorDispositivo {
        private $modelo;

        public function __construct($conexion) {
            $this->modelo = new ModeloDispositivo($conexion);
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        public function registrarDispositivo(array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "registrarDispositivo llamado\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/debug_log.txt', "Datos recibidos: " . json_encode($datos) . "\n", FILE_APPEND);

            $tipo = $datos['TipoDispositivo'] ?? null;
            $marca = $datos['MarcaDispositivo'] ?? null;
            $otroTipo = $datos['OtroTipoDispositivo'] ?? null;
            $idFuncionario = $datos['IdFuncionario'] ?? null;
            $idVisitante = $datos['IdVisitante'] ?? null;

            file_put_contents(__DIR__ . '/debug_log.txt', "Variables extraídas - Tipo: $tipo, Marca: $marca\n", FILE_APPEND);

            // Validaciones
            if ($this->campoVacio($tipo)) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR: Tipo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Tipo de dispositivo'];
            }

            if ($this->campoVacio($marca)) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR: Marca vacía\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Marca del dispositivo'];
            }

            if ($tipo === 'Otro' && $this->campoVacio($otroTipo)) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR: Otro tipo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Debe especificar el tipo de dispositivo'];
            }

            $tipoFinal = ($tipo === 'Otro') ? $otroTipo : $tipo;

            file_put_contents(__DIR__ . '/debug_log.txt', "Validaciones pasadas. Tipo final: $tipoFinal\n", FILE_APPEND);

            try {
                // Convertir IDs a null si están vacíos
                $idFunc = $this->campoVacio($idFuncionario) ? null : (int)$idFuncionario;
                $idVis = $this->campoVacio($idVisitante) ? null : (int)$idVisitante;

                file_put_contents(__DIR__ . '/debug_log.txt', "IDs convertidos - Funcionario: " . ($idFunc ?? 'null') . ", Visitante: " . ($idVis ?? 'null') . "\n", FILE_APPEND);

                $resultado = $this->modelo->registrarDispositivo($tipoFinal, $marca, $idFunc, $idVis);

                file_put_contents(__DIR__ . '/debug_log.txt', "Resultado modelo: " . json_encode($resultado) . "\n", FILE_APPEND);

                if ($resultado['success']) {
                    $idDispositivo = $resultado['id'];
                    file_put_contents(__DIR__ . '/debug_log.txt', "Dispositivo registrado con ID: $idDispositivo\n", FILE_APPEND);

                    return [
                        "success" => true,
                        "message" => "Dispositivo registrado correctamente con ID: " . $idDispositivo,
                        "data" => ["IdDispositivo" => $idDispositivo]
                    ];
                } else {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Error en modelo: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return [
                        'success' => false,
                        'message' => 'Error al registrar en BD',
                        'error' => $resultado['error'] ?? 'Error desconocido'
                    ];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // ✅ Procesar solicitud
    $controlador = new ControladorDispositivo($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarDispositivo($_POST);
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Respuesta final: " . json_encode($resultado) . "\n", FILE_APPEND);

    // Limpiar buffer
    ob_end_clean();

    // Enviar JSON
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    
    $error = $e->getMessage();
    file_put_contents(__DIR__ . '/debug_log.txt', "ERROR FINAL: $error\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $error,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;