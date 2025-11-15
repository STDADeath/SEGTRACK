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

    // ✅ Cargar archivo de conexión
    $ruta_conexion = __DIR__ . '/../../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión cargada\n", FILE_APPEND);

    // ✅ Crear instancia de la clase Conexion y obtener el PDO
    $conexion = (new Conexion())->getConexion();

    if (!$conexion) {
        throw new Exception("No se pudo obtener la conexión PDO desde la clase Conexion");
    }

    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Conexión verificada como instancia de PDO\n", FILE_APPEND);

    // ✅ Verificar librería QR
    $ruta_qrlib = __DIR__ . '/../../libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents(__DIR__ . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    // ✅ Verificar modelo
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

                // 🔥 RUTA CORREGIDA: Desde Controller/sede_institucion_funcionario_usuario/ hacia raíz
                // __DIR__ está en: SEGTRACK/Controller/sede_institucion_funcionario_usuario/
                // Necesitamos ir a: SEGTRACK/qr/
                $rutaCarpeta = realpath(__DIR__ . '/../../') . '/qr';
                
                file_put_contents(__DIR__ . '/debug_log.txt', "Ruta carpeta QR: $rutaCarpeta\n", FILE_APPEND);
                
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents(__DIR__ . '/debug_log.txt', "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                } else {
                    file_put_contents(__DIR__ . '/debug_log.txt', "Carpeta QR ya existe\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                file_put_contents(__DIR__ . '/debug_log.txt', "Intentando crear QR en: $rutaCompleta\n", FILE_APPEND);

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente en: $rutaCompleta");
                }

                file_put_contents(__DIR__ . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                // Guardar solo el nombre del archivo, sin ruta
                return $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        public function registrarFuncionario(array $datos): array {
            file_put_contents(__DIR__ . '/debug_log.txt', "registrarFuncionario llamado\n", FILE_APPEND);

            // 🔥 Limpiar y validar longitud de campos
            $cargo = trim($datos['CargoFuncionario']);
            $nombre = trim($datos['NombreFuncionario']);
            $sede = $datos['IdSede'];
            $telefono = $datos['TelefonoFuncionario'];
            $documento = $datos['DocumentoFuncionario'];
            $correo = trim($datos['CorreoFuncionario']);

            // 🔥 Limitar longitud según tu BD (ajusta estos valores según tu tabla)
            $cargo = substr($cargo, 0, 100);
            $nombre = substr($nombre, 0, 150);
            $correo = substr($correo, 0, 100);

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
                file_put_contents(__DIR__ . '/debug_log.txt', "Llamando a RegistrarFuncionario en el modelo\n", FILE_APPEND);
                
                $resultado = $this->modelo->RegistrarFuncionario($cargo, $nombre, (int)$sede, (int)$telefono, (int)$documento, $correo);
                
                // 🔥 CAMBIO CRÍTICO: Registrar el resultado completo
                file_put_contents(__DIR__ . '/debug_log.txt', "Resultado del modelo: " . json_encode($resultado) . "\n", FILE_APPEND);

                if ($resultado['success']) {
                    $idFuncionario = $resultado['id'];
                    file_put_contents(__DIR__ . '/debug_log.txt', "Registro exitoso, ID: $idFuncionario\n", FILE_APPEND);
                    
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
                    // 🔥 CAMBIO CRÍTICO: Mostrar el error real de la base de datos
                    $errorMsg = $resultado['error'] ?? 'Error desconocido al registrar';
                    file_put_contents(__DIR__ . '/debug_log.txt', "ERROR en BD: $errorMsg\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error en BD: ' . $errorMsg];
                }
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/debug_log.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
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
                    $errorMsg = $resultado['error'] ?? 'Error desconocido al actualizar';
                    return ['success' => false, 'message' => 'Error en BD: ' . $errorMsg];
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
                'IdSede' => $_POST['IdSede'],
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