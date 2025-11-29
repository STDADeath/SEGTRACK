<?php
// Activa el reporte de todos los errores de PHP.
error_reporting(E_ALL);

// Evita que los errores se muestren en pantalla.
ini_set('display_errors', 0);

// Habilita el registro de errores en archivo.
ini_set('log_errors', 1);

// ======================================================
//  CREA LA CARPETA debugFun PARA GUARDAR LOS LOGS
// ======================================================

// Define la ruta absoluta donde se guardará la carpeta debugFun.
$debugDir = __DIR__ . '/debugFun';

// Si la carpeta no existe, la crea con permisos completos.
if (!file_exists($debugDir)) {
    mkdir($debugDir, 0777, true);
}

// Define la ruta del archivo debug.log dentro de debugFun.
$debugFile = $debugDir . '/debug.log';

// Define la ruta del archivo error.log dentro de debugFun.
$errorFile = $debugDir . '/error.log';

// Cambia el archivo donde PHP escribirá errores fatales.
ini_set('error_log', $errorFile);

// Inicia el buffer de salida para controlar lo que se imprime.
ob_start();

// Define que la respuesta será JSON en UTF-8.
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Permite solicitudes de cualquier dominio.

// Escribe en debug.log la marca de inicio del proceso.
file_put_contents($debugFile, date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {

    // Guarda en el log el contenido recibido por POST.
    file_put_contents($debugFile, "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // Define la ruta del archivo de conexión a la base de datos.
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';

    // Verifica que el archivo exista.
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    // Importa el archivo de conexión.
    require_once $ruta_conexion;

    // Log de que la conexión fue cargada.
    file_put_contents($debugFile, "Conexión cargada\n", FILE_APPEND);

    // Se instancia el objeto Conexion y se recupera el PDO.
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    // Verifica que la variable de conexión exista.
    if (!isset($conexion)) {
        throw new Exception("Variable \$conexion no inicializada");
    }

    // Verifica que la conexión sea instancia de PDO.
    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    // Log de verificación exitosa.
    file_put_contents($debugFile, "Conexión verificada como PDO\n", FILE_APPEND);

    // Carga la librería que genera los códigos QR.
    $ruta_qrlib = __DIR__ . '/../Libraries/phpqrcode/qrlib.php';

    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }

    require_once $ruta_qrlib;

    // Log confirmando que la librería fue cargada.
    file_put_contents($debugFile, "Librería QR cargada\n", FILE_APPEND);

    // Carga el modelo para funcionarios.
    $ruta_modelo = __DIR__ . "/../Model/ModeloFuncionarios.php";

    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }

    require_once $ruta_modelo;

    // Log confirmando que el modelo fue cargado.
    file_put_contents($debugFile, "Modelo cargado\n", FILE_APPEND);

    // ============================================
    // CLASE CONTROLADOR DE FUNCIONARIOS
    // ============================================
    class ControladorFuncionario {

        private $modelo; // Almacena la instancia del modelo.

        public function __construct($conexion) {
            // Crea el modelo usando la conexión PDO.
            $this->modelo = new ModeloFuncionario($conexion);
        }

        // Método para validar si un campo está vacío.
        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        // ============================================
        // GENERACIÓN DEL CÓDIGO QR
        // ============================================
        private function generarQR(int $idFuncionario, string $nombre, string $documento): ?string {
            global $debugFile;

            try {
                file_put_contents($debugFile, "Generando QR para funcionario ID: $idFuncionario\n", FILE_APPEND);

                // Ruta donde se guardan los QR.
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Func';
                
                // Crea la carpeta si no existe.
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($debugFile, "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                }

                // Nombre único para el archivo QR.
                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";

                // Ruta completa donde se guardará.
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;

                // Información codificada en el QR.
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                // Genera el archivo PNG del QR.
                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents($debugFile, "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                
                return 'qr/Qr_Func/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($debugFile, "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        // ============================================
        // REGISTRAR FUNCIONARIO
        // ============================================
        public function registrarFuncionario(array $datos): array {
            global $debugFile;

            file_put_contents($debugFile, "registrarFuncionario llamado\n", FILE_APPEND);

            // EXTRAER DATOS DEL POST
            $cargo = $datos['CargoFuncionario'];
            $nombre = $datos['NombreFuncionario'];
            $sede = $datos['IdSede'];  // <<----------------------------- AQUÍ RECIBE LA SEDE
            $telefono = $datos['TelefonoFuncionario'];
            $documento = $datos['DocumentoFuncionario'];
            $correo = $datos['CorreoFuncionario'];

            // VALIDACIONES
            if ($this->campoVacio($cargo)) {
                file_put_contents($debugFile, "ERROR: Cargo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Cargo'];
            }

            if ($this->campoVacio($nombre)) {
                file_put_contents($debugFile, "ERROR: Nombre vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Nombre'];
            }

            if ($this->campoVacio($sede)) {
                file_put_contents($debugFile, "ERROR: Sede vacía\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Sede'];
            }

            if ($this->campoVacio($documento)) {
                file_put_contents($debugFile, "ERROR: Documento vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Documento'];
            }

            try {

                // LLAMA AL MÉTODO DEL MODELO PARA INSERTAR EN LA BD
                $resultado = $this->modelo->RegistrarFuncionario(
                    $cargo,
                    $nombre,
                    (int)$sede,   // <<----- ESTE VALOR CORRESPONDE A TU CONTROLADOR DE SEDE
                    (int)$telefono,
                    (int)$documento,
                    $correo
                );

                if ($resultado['success']) {
                    $idFuncionario = $resultado['id'];

                    // Genera el QR
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
                    return ['success' => false, 'message' => 'Error al registrar en BD'];
                }

            } catch (Exception $e) {
                file_put_contents($debugFile, "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ============================================
        // ACTUALIZAR FUNCIONARIO
        // ============================================
        public function actualizarFuncionario(int $id, array $datos): array {
            global $debugFile;

            file_put_contents($debugFile, "actualizarFuncionario llamado con ID: $id\n", FILE_APPEND);
            file_put_contents($debugFile, "Datos a actualizar: " . json_encode($datos) . "\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->actualizar($id, $datos);

                if ($resultado['success']) {
                    file_put_contents($debugFile, "Funcionario actualizado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Funcionario actualizado correctamente'];
                } else {
                    file_put_contents($debugFile, "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar funcionario'];
                }

            } catch (Exception $e) {
                file_put_contents($debugFile, "EXCEPCIÓN en actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // CREA EL CONTROLADOR
    $controlador = new ControladorFuncionario($conexion);

    // DETERMINA LA ACCIÓN ENVIADA DESDE POST
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents($debugFile, "Acción: $accion\n", FILE_APPEND);

    // SEGÚN LA ACCIÓN, EJECUTA EL MÉTODO CORRESPONDIENTE DEL CONTROLADOR
    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST);
    } elseif ($accion === 'actualizar') {

        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {

            $datos = [
                'CargoFuncionario' => $_POST['cargo'] ?? null,
                'NombreFuncionario' => $_POST['nombre'] ?? null,
                'IdSede' => !empty($_POST['sede']) ? (int)$_POST['sede'] : null, // <<--- SEDE TAMBIÉN SE OBTIENE AQUÍ
                'TelefonoFuncionario' => !empty($_POST['telefono']) ? (int)$_POST['telefono'] : null,
                'DocumentoFuncionario' => !empty($_POST['documento']) ? (int)$_POST['documento'] : null,
                'CorreoFuncionario' => $_POST['correo'] ?? null
            ];

            $resultado = $controlador->actualizarFuncionario($id, $datos);

        } else {
            $resultado = ['success' => false, 'message' => 'ID de funcionario no válido'];
        }

    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    // REGISTRA EL RESULTADO FINAL
    file_put_contents($debugFile, "Respuesta final: " . json_encode($resultado) . "\n", FILE_APPEND);

    // ENVÍA LA RESPUESTA EN FORMATO JSON
    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    ob_end_clean();
    
    $error = $e->getMessage();

    // REGISTRA EL ERROR FINAL
    file_put_contents($debugFile, "ERROR FINAL: $error\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $error,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;
