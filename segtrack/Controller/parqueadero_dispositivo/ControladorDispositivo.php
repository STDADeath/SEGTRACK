<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

file_put_contents(__DIR__ . '/debug_log.txt', date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    file_put_contents(__DIR__ . '/debug_log.txt', "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    $ruta_conexion = __DIR__ . '/../../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión cargada\n", FILE_APPEND);

    if (!isset($conexion)) {
        throw new Exception("Variable \$conexion no inicializada");
    }

    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión verificada como PDO\n", FILE_APPEND);

    $ruta_qrlib = __DIR__ . '/../../libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents(__DIR__ . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

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

        private function generarQR(int $idDispositivo, string $tipo, string $marca): ?string {
            try {
                file_put_contents(__DIR__ . '/debug_log.txt', "Generando QR para dispositivo ID: $idDispositivo\n", FILE_APPEND);

                $rutaCarpeta = __DIR__ . '/../../qr';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents(__DIR__ . '/debug_log.txt', "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-DISP-" . $idDispositivo . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR = "ID: $idDispositivo\nTipo: $tipo\nMarca: $marca";

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents(__DIR__ . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                return 'qr/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        public function registrarDispositivo(array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "registrarDispositivo llamado\n", FILE_APPEND);

            $tipo = $datos['TipoDispositivo'] ?? null;
            $marca = $datos['MarcaDispositivo'] ?? null;
            $otroTipo = $datos['OtroTipoDispositivo'] ?? null;
            $idFuncionario = $datos['IdFuncionario'] ?? null;
            $idVisitante = $datos['IdVisitante'] ?? null;

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

            try {
                $idFunc = $this->campoVacio($idFuncionario) ? null : (int)$idFuncionario;
                $idVis = $this->campoVacio($idVisitante) ? null : (int)$idVisitante;

                $resultado = $this->modelo->registrarDispositivo($tipoFinal, $marca, $idFunc, $idVis);

                if ($resultado['success']) {
                    $idDispositivo = $resultado['id'];
                    $rutaQR = $this->generarQR($idDispositivo, $tipoFinal, $marca);

                    if ($rutaQR) {
                        $this->modelo->actualizarQR($idDispositivo, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Dispositivo registrado correctamente con ID: " . $idDispositivo,
                        "data" => ["IdDispositivo" => $idDispositivo, "QrDispositivo" => $rutaQR]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en BD'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarDispositivo(int $id, array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "actualizarDispositivo llamado con ID: $id\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/debug_log.txt', "Datos a actualizar: " . json_encode($datos) . "\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->actualizar($id, $datos);
                
                if ($resultado['success']) {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Dispositivo actualizado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Dispositivo actualizado correctamente'];
                } else {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar dispositivo'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "EXCEPCIÓN en actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function eliminarDispositivo(int $id): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "eliminarDispositivo llamado con ID: $id\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->eliminar($id);
                
                if ($resultado['success']) {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Dispositivo eliminado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Dispositivo eliminado correctamente'];
                } else {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Error al eliminar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al eliminar dispositivo'];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "EXCEPCIÓN en eliminar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    $controlador = new ControladorDispositivo($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarDispositivo($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $datos = [
                'TipoDispositivo' => $_POST['tipo'] ?? null,
                'MarcaDispositivo' => $_POST['marca'] ?? null,
                'IdFuncionario' => !empty($_POST['id_funcionario']) ? (int)$_POST['id_funcionario'] : null,
                'IdVisitante' => !empty($_POST['id_visitante']) ? (int)$_POST['id_visitante'] : null
            ];
            $resultado = $controlador->actualizarDispositivo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de dispositivo no válido'];
        }
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $resultado = $controlador->eliminarDispositivo($id);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de dispositivo no válido'];
        }
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Respuesta final: " . json_encode($resultado) . "\n", FILE_APPEND);

    ob_end_clean();
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