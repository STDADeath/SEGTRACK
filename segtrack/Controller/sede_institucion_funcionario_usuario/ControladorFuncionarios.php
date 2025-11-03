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

    $ruta_modelo = __DIR__ . "/../../model/sede_institucion_funcionario_usuario/ModeloFuncionarios.php";
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

        private function generarQR(int $idFuncionario, string $nombre, string $documento): ?string {
            try {
                file_put_contents(__DIR__ . '/debug_log.txt', "Generando QR para funcionario ID: $idFuncionario\n", FILE_APPEND);

                $rutaCarpeta = __DIR__ . '/../../qr';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents(__DIR__ . '/debug_log.txt', "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents(__DIR__ . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                return 'qr_funcionario/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        public function registrarFuncionario(array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "registrarFuncionario llamado\n", FILE_APPEND);

            $cargo = $datos['CargoFuncionario'];
            $nombre = $datos['NombreFuncionario'];
            $sede = $datos['IdSede'];
            $telefono = $datos['TelefonoFuncionario'];
            $documento = $datos['DocumentoFuncionario'];
            $correo = $datos['CorreoFuncionario'];

            if ($this->campoVacio($cargo)) {
                return ['success' => false, 'message' => 'Falta el campo: Cargo del funcionario'];
            }

            if ($this->campoVacio($nombre)) {
                return ['success' => false, 'message' => 'Falta el campo: Nombre del funcionario'];
            }

            if ($this->campoVacio($sede)) {
                return ['success' => false, 'message' => 'Falta el campo: Sede del funcionario'];
            }

            if ($this->campoVacio($documento)) {
                return ['success' => false, 'message' => 'Falta el campo: Documento del funcionario'];
            }

            try {
                $resultado = $this->modelo->RegistrarFuncionario($cargo, $nombre, (int)$sede, (int)$telefono, (int)$documento, $correo);

                if ($resultado['success']) {
                    $idFuncionario = $resultado['id'];
                    $rutaQR = $this->generarQR($idFuncionario, $nombre, $documento);

                    if ($rutaQR) {
                        $this->modelo->ActualizarQrFuncionario($idFuncionario, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Funcionario registrado correctamente con ID: " . $idFuncionario,
                        "data" => ["IdFuncionario" => $idFuncionario, "QrCodigoFuncionario" => $rutaQR]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en la base de datos'];
                }
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarFuncionario(int $id, array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "actualizarFuncionario llamado con ID: $id\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->actualizar($id, $datos);

                if ($resultado['success']) {
                    return ['success' => true, 'message' => 'Funcionario actualizado correctamente'];
                } else {
                    return ['success' => false, 'message' => 'Error al actualizar funcionario'];
                }
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    $controlador = new ControladorFuncionario($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $datos = [
                'CargoFuncionario' => $_POST['CargoFuncionario'],
                'NombreFuncionario' => $_POST['NombreFuncionario'],
                'IdSede' => $_POST['IdSede'] ?? null,
                'TelefonoFuncionario' => $_POST['TelefonoFuncionario'],
                'DocumentoFuncionario' => $_POST['DocumentoFuncionario'],
                'CorreoFuncionario' => $_POST['CorreoFuncionario']
            ];
            $resultado = $controlador->actualizarFuncionario($id, $datos);
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
