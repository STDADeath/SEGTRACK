<?php
// ... (Código inicial de logs, headers y conexión a BD)
// ... (Carga de librerías y modelos)

// =======================================================
// 🏛️ CONTROLADOR DE FUNCIONARIOS (POO)
// =======================================================
class ControladorFuncionario {

    private $modelo;
    private $log;

    public function __construct($conexion, $ruta_log) {
        $this->modelo = new ModeloFuncionario($conexion);
        $this->log = $ruta_log;
    }

    private function log($msg) {
        file_put_contents($this->log, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
    }

    private function campoVacio($campo): bool {
        return !isset($campo) || trim($campo) === '';
    }

    // ----------------------------------------------------
    // 📌 GENERAR QR (Función que ya tienes)
    // ----------------------------------------------------
    private function generarQR($id, $nombre, $documento) {
        // ... (Tu código actual de generarQR)
        $this->log("Generando QR para ID $id");

        $carpeta = __DIR__ . '/../../Public/qr/Qr_Func';

        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
            $this->log("Carpeta QR creada: $carpeta");
        }

        // Importante: El `uniqid()` asegura que el archivo QR sea diferente cada vez
        $archivo = "QR-FUNC-$id-" . uniqid() . ".png"; 
        $rutaCompleta = "$carpeta/$archivo";

        // Importante: El contenido del QR debe reflejar los campos que pueden cambiar
        $contenido = "ID: $id\nNombre: $nombre\nDocumento: $documento";

        QRcode::png($contenido, $rutaCompleta, QR_ECLEVEL_H, 10);

        if (!file_exists($rutaCompleta)) {
            throw new Exception("No se pudo generar el QR");
        }

        return "qr/Qr_Func/$archivo";
    }

    // ----------------------------------------------------
    // 🟢 REGISTRAR FUNCIONARIO (Tu función de registro actual)
    // ----------------------------------------------------
    public function registrarFuncionario($datos) {
        // ... (Tu código actual de registrarFuncionario)
        // ...
    }

    // ----------------------------------------------------
    // 🔄 ACTUALIZAR FUNCIONARIO (Función Adaptada)
    // ----------------------------------------------------
    public function actualizarFuncionario(int $id, array $datos): array {
        $this->log("=== actualizarFuncionario llamado (ID: $id) ===");
        
        // Validaciones mínimas
        if ($this->campoVacio($datos['CargoFuncionario'] ?? null)) return ["success" => false, "message" => "Falta el Cargo"];
        if ($this->campoVacio($datos['NombreFuncionario'] ?? null)) return ["success" => false, "message" => "Falta el Nombre"];
        if ($this->campoVacio($datos['DocumentoFuncionario'] ?? null)) return ["success" => false, "message" => "Falta el Documento"];

        try {
            // 1. Obtener datos anteriores para comparar y eliminar QR
            $funcionarioAnterior = $this->modelo->obtenerPorId($id);

            if (!$funcionarioAnterior) {
                $this->log("ERROR: Funcionario con ID $id no encontrado para actualizar");
                return ['success' => false, 'message' => 'Funcionario no encontrado'];
            }
            
            $qrAnterior = $funcionarioAnterior['QrCodigo'] ?? null;
            
            // 2. Ejecutar la actualización en la BD
            $resultado = $this->modelo->ActualizarFuncionario($id, $datos);

            if (!$resultado['success']) {
                $this->log("Error en el modelo al actualizar: " . $resultado['error']);
                return ['success' => false, 'message' => 'Error al actualizar en BD: ' . ($resultado['error'] ?? 'desconocido')];
            }

            // 3. Determinar si los datos del QR cambiaron
            $regenerarQR = false;
            
            // Comprobamos si el nombre o el documento cambiaron (estos están en el QR)
            if (($funcionarioAnterior['NombreFuncionario'] ?? '') !== $datos['NombreFuncionario'] ||
                ($funcionarioAnterior['DocumentoFuncionario'] ?? '') !== $datos['DocumentoFuncionario']) {
                $regenerarQR = true;
                $this->log("Cambio detectado en Nombre o Documento. Regenerando QR.");
            }

            $nuevoQR = $qrAnterior;

            if ($regenerarQR) {
                // Regenerar el QR con los nuevos datos
                $nuevoNombre = $datos['NombreFuncionario'];
                $nuevoDocumento = $datos['DocumentoFuncionario'];
                
                $nuevoQR = $this->generarQR($id, $nuevoNombre, $nuevoDocumento);
                
                if ($nuevoQR) {
                    // Actualizar la ruta del nuevo QR en la BD
                    $this->modelo->ActualizarQrFuncionario($id, $nuevoQR);
                    
                    // Eliminar el QR anterior si existía
                    if ($qrAnterior) {
                        $rutaQrAnterior = __DIR__ . '/../../Public/' . $qrAnterior;
                        if (file_exists($rutaQrAnterior)) {
                            unlink($rutaQrAnterior);
                            $this->log("QR anterior eliminado: $rutaQrAnterior");
                        }
                    }
                }
            } else {
                $this->log("No se detectaron cambios en Nombre/Documento. QR no regenerado.");
            }

            $this->log("Funcionario actualizado exitosamente. Filas: " . $resultado['rows']);
            return [
                'success' => true, 
                'message' => 'Funcionario actualizado correctamente',
                'rows' => $resultado['rows'] ?? 0,
                'qr' => $nuevoQR
            ];
            
        } catch (Exception $e) {
            $this->log("EXCEPCIÓN en actualizarFuncionario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // ... (Tu función cambiarEstadoFuncionario)
}

// =======================================================
// 🚀 ENRUTADOR PRINCIPAL (Adaptado)
// =======================================================

$controlador = new ControladorFuncionario($conexion, $ruta_debug_log);
$accion = $_POST['accion'] ?? 'registrar';

file_put_contents($ruta_debug_log, "Acción detectada: $accion\n", FILE_APPEND);

if ($accion === 'registrar') {
    $resultado = $controlador->registrarFuncionario($_POST);
    
} elseif ($accion === 'actualizar') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id > 0) {
        // Mapear los nombres de POST a los nombres esperados por el Modelo
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
    
} elseif ($accion === 'cambiar_estado') {
    // ... (Tu lógica para cambiar estado)
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nuevoEstado = $_POST['estado'] ?? '';
    
    if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
        $resultado = $controlador->cambiarEstadoFuncionario($id, $nuevoEstado);
    } else {
        $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
    }
    
} else {
    $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
}

file_put_contents($ruta_debug_log, "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
file_put_contents($ruta_debug_log, "=== FIN DE PETICIÓN ===\n\n", FILE_APPEND);

ob_end_clean();
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

exit;
// ... (Tu código final de manejo de excepciones)