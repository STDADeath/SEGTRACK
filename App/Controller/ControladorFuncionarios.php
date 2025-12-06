<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ✅ DEBUG dentro de /debugFunc/
$debugRuta = __DIR__ . '/debugFunc/debug_log.txt';
file_put_contents($debugRuta, date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {

    // =====================================
    // 1. LOG DE POST
    // =====================================
    file_put_contents($debugRuta, "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // =====================================
    // 2. CARGAR CONEXION (ruta corregida)
    // =====================================
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents($debugRuta, "Conexión cargada correctamente\n", FILE_APPEND);

    $conexion = (new Conexion())->getConexion();

    if (!$conexion) {
        throw new Exception("No se obtuvo conexión PDO");
    }

    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia válida de PDO");
    }

    file_put_contents($debugRuta, "Conexión validada como PDO\n", FILE_APPEND);

    // =====================================
    // 3. LIBRERÍA QR
    // =====================================
    $ruta_qrlib = __DIR__ . '/../Libraries/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería QR no encontrada: $ruta_qrlib");
    }

    require_once $ruta_qrlib;
    file_put_contents($debugRuta, "Librería QR cargada\n", FILE_APPEND);

    // =====================================
    // 4. CARGAR MODELO (ruta corregida)
    // =====================================
    $ruta_modelo = __DIR__ . '/../Model/ModeloFuncionarios.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }

    require_once $ruta_modelo;
    file_put_contents($debugRuta, "Modelo cargado correctamente\n", FILE_APPEND);

    // =====================================
    // CONTROLADOR
    // =====================================

    class ControladorFuncionario {

        private $modelo;

        public function __construct($conexion) {
            $this->modelo = new ModeloFuncionario($conexion);
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || trim($campo) === '';
        }

        // ============================================================
        // GENERAR QR (AJUSTADO PARA GUARDAR EN PUBLIC/qr/Qr_Func/)
        // ============================================================
        private function generarQR(int $idFuncionario, string $nombre, string $documento): ?string {

            $debugRuta = __DIR__ . '/debugFunc/debug_log.txt';

            try {
                // Ruta física final: SEGTRACK/Public/qr/Qr_Func/
                $rutaCarpeta = realpath(__DIR__ . '/../../Public/qr/Qr_Func');

                // Si no existe la carpeta, crearla
                if (!$rutaCarpeta) {
                    mkdir(__DIR__ . '/../../Public/qr/Qr_Func', 0777, true);
                    $rutaCarpeta = realpath(__DIR__ . '/../../Public/qr/Qr_Func');
                }

                // Nombre único del archivo
                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";

                // Ruta física completa
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;

                // Ruta relativa que se guarda en BD
                $rutaRelativa = 'qr/Qr_Func/' . $nombreArchivo;

                // Contenido del QR
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                // Generar QR
                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se generó en: $rutaCompleta");
                }

                file_put_contents($debugRuta, "QR generado correctamente: $rutaRelativa\n", FILE_APPEND);

                return $rutaRelativa;

            } catch (Exception $e) {
                file_put_contents($debugRuta, "ERROR generando QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        // ============================================================
        // REGISTRAR FUNCIONARIO
        // ============================================================
        public function registrarFuncionario(array $datos): array {

            $cargo = trim($datos['CargoFuncionario']);
            $nombre = trim($datos['NombreFuncionario']);
            $sede = $datos['IdSede'];
            $telefono = $datos['TelefonoFuncionario'];
            $documento = $datos['DocumentoFuncionario'];
            $correo = trim($datos['CorreoFuncionario']);

            if ($this->campoVacio($cargo)) return ['success' => false, 'message' => 'Falta el cargo'];
            if ($this->campoVacio($nombre)) return ['success' => false, 'message' => 'Falta el nombre'];
            if ($this->campoVacio($sede)) return ['success' => false, 'message' => 'Falta la sede'];
            if ($this->campoVacio($documento)) return ['success' => false, 'message' => 'Falta el documento'];

            try {

                $resultado = $this->modelo->RegistrarFuncionario(
                    $cargo,
                    $nombre,
                    (int)$sede,
                    (int)$telefono,
                    (int)$documento,
                    $correo
                );

                if (!$resultado['success']) {
                    return ['success' => false, 'message' => 'Error BD: ' . ($resultado['error'] ?? 'desconocido')];
                }

                $idFuncionario = $resultado['id'];

                // Generar el QR
                $rutaQR = $this->generarQR($idFuncionario, $nombre, $documento);

                if ($rutaQR) {
                    $this->modelo->ActualizarQrFuncionario($idFuncionario, $rutaQR);
                }

                return [
                    "success" => true,
                    "message" => "Funcionario registrado correctamente",
                    "data" => [
                        "IdFuncionario" => $idFuncionario,
                        "QrCodigoFuncionario" => $rutaQR
                    ]
                ];

            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // ============================================================
    // EJECUCIÓN PRINCIPAL
    // ============================================================
    $controlador = new ControladorFuncionario($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST);
    }

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

exit;
