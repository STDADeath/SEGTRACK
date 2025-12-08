<?php
// File: ../../Controller/ControladorFuncionarios.php

error_reporting(E_ALL);
ini_set('display_errors', 1); 
ini_set('log_errors', 1);

// ✅ CORRECCIÓN DE RUTA: Los logs ahora irán a la carpeta Debug_Func
ini_set('error_log', __DIR__ . '/Debug_Func/error_log.txt'); 

ob_start();

// Configuración de cabeceras para respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ✅ CORRECCIÓN DE RUTA: Debug log ahora en Debug_Func
$ruta_debug_log = __DIR__ . '/Debug_Func/debug_log.txt';

// ✅ Asegurar que la carpeta Debug_Func existe
if (!file_exists(__DIR__ . '/Debug_Func')) {
    mkdir(__DIR__ . '/Debug_Func', 0777, true);
}

file_put_contents($ruta_debug_log, "\n" . date('Y-m-d H:i:s') . " === INICIO DE PETICIÓN ===\n", FILE_APPEND);

try {
    file_put_contents($ruta_debug_log, "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // =======================================================
    // 🛢️ CONEXIÓN Y CARGA DE CORE
    // =======================================================
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents($ruta_debug_log, "Conexión cargada\n", FILE_APPEND);

    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion(); 

    if (!$conexion || !($conexion instanceof PDO)) {
        throw new Exception("La conexión PDO no es válida o nula");
    }

    file_put_contents($ruta_debug_log, "Conexión verificada como instancia de PDO\n", FILE_APPEND);

    // =======================================================
    // 📦 CARGA DE LIBRERÍA Y MODELO
    // =======================================================

    // ✅ Verificar librería QR
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents($ruta_debug_log, "Librería QR cargada\n", FILE_APPEND);

    // ✅ VERIFICAR MODELO
    $ruta_modelo = __DIR__ . "/../Model/ModeloFuncionarios.php";
    
    if (!file_exists($ruta_modelo)) {
         $ruta_modelo = __DIR__ . "/../Model/ModeloFuncionarios.php"; 
    }
    
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado en ninguna de las rutas esperadas: " . $ruta_modelo);
    }
    
    require_once $ruta_modelo;
    file_put_contents($ruta_debug_log, "Modelo cargado: $ruta_modelo\n", FILE_APPEND);

    // =======================================================
    // 🏛️ CONTROLADOR DE FUNCIONARIOS
    // =======================================================
    class ControladorFuncionario {
        private $modelo;
        private $log;

        public function __construct(PDO $conexion) { 
            $this->modelo = new ModeloFuncionario($conexion);
            // ✅ CORRECCIÓN DE RUTA: Log de la clase ahora en Debug_Func
            $this->log = __DIR__ . '/Debug_Func/debug_log.txt';
        }

        private function log($msg) {
            file_put_contents($this->log, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        // ✅ CORRECCIÓN DEFINITIVA: Generación del código QR SOLO en Public/qr/QR_Func
        private function generarQR(int $idFuncionario, string $nombre, string $documento): ?string {
            try {
                $this->log("Generando QR para funcionario ID: $idFuncionario");

                // ✅ Construir ruta absoluta desde la raíz del servidor
                // Obtenemos la ruta base del proyecto (hasta SEGTRACK/)
                $rutaBase = dirname(dirname(__DIR__)); // Sube 2 niveles desde Controller
                $rutaFisica = $rutaBase . '/Public/qr/QR_Func';
                
                $this->log("Ruta base del proyecto: $rutaBase");
                $this->log("Ruta física completa QR: $rutaFisica");

                // Crear la carpeta si no existe con permisos completos
                if (!is_dir($rutaFisica)) {
                    if (!mkdir($rutaFisica, 0777, true)) {
                        throw new Exception("No se pudo crear la carpeta QR: $rutaFisica");
                    }
                    chmod($rutaFisica, 0777); // Asegurar permisos
                    $this->log("Carpeta QR creada con permisos 777: $rutaFisica");
                } else {
                    $this->log("Carpeta QR ya existe: $rutaFisica");
                }

                // Verificar que la carpeta es escribible
                if (!is_writable($rutaFisica)) {
                    throw new Exception("La carpeta QR no es escribible: $rutaFisica");
                }

                // Nombre único del archivo
                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";
                
                // ✅ RUTA ABSOLUTA COMPLETA para el archivo
                $rutaCompletaFisica = $rutaFisica . DIRECTORY_SEPARATOR . $nombreArchivo;
                
                // Contenido del código QR
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                $this->log("Intentando crear QR en ruta absoluta: $rutaCompletaFisica");

                // ✅ Generar el código QR con ruta absoluta
                QRcode::png($contenidoQR, $rutaCompletaFisica, QR_ECLEVEL_H, 10);

                // Verificar que el archivo se creó correctamente
                if (!file_exists($rutaCompletaFisica)) {
                    throw new Exception("El archivo QR no se creó en: $rutaCompletaFisica");
                }

                $this->log("✓ QR generado exitosamente en: $rutaCompletaFisica");
                $this->log("✓ Tamaño del archivo: " . filesize($rutaCompletaFisica) . " bytes");
                
                // ✅ Ruta relativa para guardar en la BD
                $rutaRelativa = "qr/QR_Func/" . $nombreArchivo;
                $this->log("Ruta relativa para BD: $rutaRelativa");
                
                return $rutaRelativa;

            } catch (Exception $e) {
                $this->log("ERROR al generar QR: " . $e->getMessage());
                return null;
            }
        }

        public function registrarFuncionario(array $datos): array {
            $this->log("registrarFuncionario llamado");

            $cargo = trim($datos['CargoFuncionario'] ?? '');
            $nombre = trim($datos['NombreFuncionario'] ?? '');
            $correo = trim($datos['CorreoFuncionario'] ?? '');
            
            $sede = (int)($datos['IdSede'] ?? 0);
            $telefono = (int)($datos['TelefonoFuncionario'] ?? 0);
            $documento = (int)($datos['DocumentoFuncionario'] ?? 0);
            

            if ($this->campoVacio($cargo) || $cargo == 0) return ['success' => false, 'message' => 'Falta el Cargo o es inválido.'];
            if ($this->campoVacio($nombre)) return ['success' => false, 'message' => 'Falta el Nombre.'];
            if ($sede <= 0) return ['success' => false, 'message' => 'Falta la Sede o es inválida.'];
            if ($documento <= 0) return ['success' => false, 'message' => 'Falta el Documento o es inválido.'];

            try {
                $this->log("Llamando a RegistrarFuncionario en el modelo");
                
                $resultado = $this->modelo->RegistrarFuncionario($cargo, $nombre, $sede, $telefono, $documento, $correo);
                
                $this->log("Resultado del modelo (BD): " . json_encode($resultado, JSON_UNESCAPED_UNICODE));

                if ($resultado['success']) {
                    $idFuncionario = $resultado['id'];
                    $this->log("Registro exitoso, ID: $idFuncionario. Generando QR...");
                    
                    $rutaQR = $this->generarQR($idFuncionario, $nombre, $documento);

                    if ($rutaQR) {
                        $this->modelo->ActualizarQrFuncionario($idFuncionario, $rutaQR);
                        $this->log("QR actualizado en BD con ruta: $rutaQR");
                    } else {
                         $this->log("ADVERTENCIA: No se pudo generar el QR, continuando con el registro.");
                    }

                    return [
                        "success" => true,
                        "message" => "Funcionario registrado correctamente con ID: " . $idFuncionario . 
                                     ($rutaQR ? ". QR generado y guardado en Public/qr/QR_Func/" : ". ADVERTENCIA: No se pudo generar el QR."),
                        "data" => ["IdFuncionario" => $idFuncionario, "QrCodigoFuncionario" => $rutaQR]
                    ];
                } else {
                    $errorMsg = $resultado['error'] ?? 'Error desconocido al registrar en la Base de Datos.';
                    $this->log("ERROR en BD: $errorMsg");
                    return ['success' => false, 'message' => 'Error en BD: ' . $errorMsg];
                }
            } catch (Exception $e) {
                $this->log("EXCEPCIÓN: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error del Controlador: ' . $e->getMessage()];
            }
        }

        public function actualizarFuncionario(int $id, array $datos): array {
            $this->log("actualizarFuncionario llamado para ID: $id");

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de funcionario no válido'];
            }

            try {
                $resultado = $this->modelo->actualizar($id, $datos);
                
                $this->log("Resultado actualización: " . json_encode($resultado, JSON_UNESCAPED_UNICODE));

                if ($resultado['success']) {
                    return [
                        'success' => true,
                        'message' => 'Funcionario actualizado correctamente',
                        'rows_affected' => $resultado['rows']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $resultado['error'] ?? 'Error al actualizar'
                    ];
                }
            } catch (Exception $e) {
                $this->log("EXCEPCIÓN en actualización: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ========================================
        // ✨ MÉTODO: CAMBIAR ESTADO DEL FUNCIONARIO
        // ========================================
        public function cambiarEstado(int $id, string $nuevoEstado): array {
            $this->log("Cambiando estado del funcionario ID: $id a: $nuevoEstado");
            
            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de funcionario inválido'];
            }
            
            if ($nuevoEstado !== 'Activo' && $nuevoEstado !== 'Inactivo') {
                return ['success' => false, 'message' => 'Estado inválido. Solo se permite "Activo" o "Inactivo"'];
            }
            
            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                
                $this->log("Resultado cambio de estado: " . json_encode($resultado, JSON_UNESCAPED_UNICODE));
                
                if ($resultado['success']) {
                    return [
                        'success' => true,
                        'message' => "Estado cambiado a '$nuevoEstado' correctamente",
                        'nuevo_estado' => $nuevoEstado
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $resultado['error'] ?? 'Error al cambiar el estado'
                    ];
                }
                
            } catch (Exception $e) {
                $this->log("EXCEPCIÓN al cambiar estado: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error del servidor: ' . $e->getMessage()
                ];
            }
        }
    }
    
    // =======================================================
    // 🎯 PROCESAR ACCIÓN DEL POST
    // =======================================================
    $controlador = new ControladorFuncionario($conexion); 
    $accion = $_POST['accion'] ?? 'registrar'; 

    file_put_contents($ruta_debug_log, "Acción a ejecutar: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST); 
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id > 0) {
            $datos = [
                'CargoFuncionario' => $_POST['cargo'] ?? null,
                'NombreFuncionario' => $_POST['nombre'] ?? null,
                'IdSede' => $_POST['sede'] ?? null,
                'TelefonoFuncionario' => $_POST['telefono'] ?? null,
                'DocumentoFuncionario' => $_POST['documento'] ?? null,
                'CorreoFuncionario' => $_POST['correo'] ?? null
            ];
            
            $resultado = $controlador->actualizarFuncionario($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de funcionario no válido para actualizar'];
        }
    } 
    // ========================================
    // ✨ ACCIÓN: CAMBIAR ESTADO
    // ========================================
    elseif ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = trim($_POST['estado'] ?? '');
        
        if ($id > 0 && !empty($nuevoEstado)) {
            $resultado = $controlador->cambiarEstado($id, $nuevoEstado);
        } else {
            $resultado = [
                'success' => false,
                'message' => 'Faltan datos requeridos (ID o Estado)'
            ];
        }
    }
    else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents($ruta_debug_log, "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    $error = $e->getMessage();
    file_put_contents($ruta_debug_log, "ERROR FINAL: $error\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error fatal del servidor: ' . $error,
        'error_details' => $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;