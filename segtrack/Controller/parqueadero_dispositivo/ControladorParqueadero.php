<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log_parqueadero.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    $ruta_conexion = __DIR__ . '/../../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Conexión cargada\n", FILE_APPEND);

    if (!isset($conexion)) {
        throw new Exception("Variable \$conexion no inicializada");
    }

    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Conexión verificada como PDO\n", FILE_APPEND);

    $ruta_modelo = __DIR__ . "/../../model/parqueadero_vehiculo/ModeloParqueadero.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;
    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Modelo cargado\n", FILE_APPEND);

    class ControladorParqueadero {
        private $modelo;

        public function __construct($conexion) {
            $this->modelo = new ModeloParqueadero($conexion);
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        public function registrarParqueadero(array $datos): array {
            file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "registrarParqueadero llamado\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Datos recibidos: " . json_encode($datos) . "\n", FILE_APPEND);

            $tipo = $datos['TipoVehiculo'] ?? null;
            $placa = $datos['PlacaVehiculo'] ?? null;
            $descripcion = $datos['DescripcionVehiculo'] ?? null;
            $tarjeta = $datos['TarjetaPropiedad'] ?? null;
            $fecha = $datos['FechaParqueadero'] ?? null;
            $idSede = $datos['IdSede'] ?? null;

            // Validaciones
            if ($this->campoVacio($tipo)) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "ERROR: Tipo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Tipo de vehículo'];
            }

            if ($this->campoVacio($placa)) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "ERROR: Placa vacía\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Placa del vehículo'];
            }

            if ($this->campoVacio($idSede)) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "ERROR: IdSede vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: ID de Sede'];
            }

            try {
                $resultado = $this->modelo->registrarParqueadero($tipo, $placa, $descripcion, $tarjeta, $fecha, (int)$idSede);

                if ($resultado['success']) {
                    $idParqueadero = $resultado['id'];
                    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Vehículo registrado con ID: $idParqueadero\n", FILE_APPEND);

                    return [
                        "success" => true,
                        "message" => "Vehículo registrado correctamente con ID: " . $idParqueadero,
                        "data" => ["IdParqueadero" => $idParqueadero]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en BD'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarParqueadero(int $id, array $datos): array {
            file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "actualizarParqueadero llamado con ID: $id\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->actualizar($id, $datos);
                
                if ($resultado['success']) {
                    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Vehículo actualizado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
                } else {
                    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "EXCEPCIÓN en actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function eliminarParqueadero(int $id): array {
            file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "eliminarParqueadero llamado con ID: $id\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->eliminar($id);
                
                if ($resultado['success']) {
                    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Vehículo eliminado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Vehículo eliminado correctamente'];
                } else {
                    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Error al eliminar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al eliminar vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "EXCEPCIÓN en eliminar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    $controlador = new ControladorParqueadero($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarParqueadero($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $datos = [
                'TipoVehiculo' => $_POST['tipo'] ?? null,
                'PlacaVehiculo' => $_POST['placa'] ?? null,
                'DescripcionVehiculo' => $_POST['descripcion'] ?? null,
                'TarjetaPropiedad' => $_POST['tarjeta'] ?? null,
                'FechaParqueadero' => $_POST['fecha'] ?? null,
                'IdSede' => !empty($_POST['idsede']) ? (int)$_POST['idsede'] : null
            ];
            $resultado = $controlador->actualizarParqueadero($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $resultado = $controlador->eliminarParqueadero($id);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "Respuesta final: " . json_encode($resultado) . "\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    
    $error = $e->getMessage();
    file_put_contents(__DIR__ . '/debug_log_parqueadero.txt', "ERROR FINAL: $error\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $error,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;