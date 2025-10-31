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

    // === Cargar conexión ===
    $ruta_conexion = __DIR__ . '/../../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }
    require_once $ruta_conexion;
    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión cargada\n", FILE_APPEND);

    if (!isset($conexion) || !($conexion instanceof PDO)) {
        throw new Exception("Conexión PDO no válida");
    }

    // === Cargar librería QR ===
    $ruta_qrlib = __DIR__ . '/../../libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents(__DIR__ . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    // === Cargar modelo ===
    $ruta_modelo = __DIR__ . "/../../model/funcionario/ModeloFuncionario.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;
    file_put_contents(__DIR__ . '/debug_log.txt', "Modelo cargado\n", FILE_APPEND);

    class ControladorFuncionario {
        private $modelo;

        public function __construct($conexion) {
            $this->modelo = new ModeloFuncionario($conexion);
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        /**
         * 📌 Generar QR del funcionario
         */
        private function generarQR(int $idFuncionario, string $nombre, string $cargo): ?string {
            try {
                file_put_contents(__DIR__ . '/debug_log.txt', "Generando QR para funcionario ID: $idFuncionario\n", FILE_APPEND);

                $rutaCarpeta = __DIR__ . '/../../qr/qr funcionarios';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents(__DIR__ . '/debug_log.txt', "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                }

                // 📍 Aquí puedes cambiar el formato del nombre del QR cuando quieras
                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";

                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nCargo: $cargo";

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents(__DIR__ . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                return 'qr/qr funcionarios/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        /**
         * ✅ Registrar funcionario
         */
        public function registrarFuncionario(array $datos): array {
            $camposObligatorios = [
                'CargoFuncionario', 'NombreFuncionario', 'IdSede',
                'TelefonoFuncionario', 'DocumentoFuncionario', 'CorreoFuncionario'
            ];

            foreach ($camposObligatorios as $campo) {
                if ($this->campoVacio($datos[$campo] ?? null)) {
                    return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
                }
            }

            try {
                $datos['QrCodigoFuncionario'] = ''; // temporal, se actualiza luego

                $resultado = $this->modelo->registrarFuncionario($datos);
                if (!$resultado['success']) {
                    return ['success' => false, 'message' => 'Error al registrar en BD', 'error' => $resultado['error'] ?? null];
                }

                $idFuncionario = $resultado['id'];
                $rutaQR = $this->generarQR($idFuncionario, $datos['NombreFuncionario'], $datos['CargoFuncionario']);

                if ($rutaQR) {
                    $this->modelo->actualizarQR($idFuncionario, $rutaQR);
                }

                return [
                    'success' => true,
                    'message' => 'Funcionario registrado correctamente',
                    'data' => ['IdFuncionario' => $idFuncionario, 'QrCodigoFuncionario' => $rutaQR]
                ];

            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        /**
         * ✅ Actualizar funcionario
         */
        public function actualizarFuncionario(int $id, array $datos): array {
            try {
                $resultado = $this->modelo->actualizar($id, $datos);

                if ($resultado['success']) {
                    return ['success' => true, 'message' => 'Funcionario actualizado correctamente'];
                } else {
                    return ['success' => false, 'message' => 'Error al actualizar', 'error' => $resultado['error'] ?? null];
                }

            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // === Controlador principal ===
    $controlador = new ControladorFuncionario($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $resultado = $controlador->actualizarFuncionario($id, $_POST);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de funcionario no válido'];
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
?>
