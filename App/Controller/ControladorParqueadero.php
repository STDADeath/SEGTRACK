<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
<<<<<<< HEAD
ini_set('error_log', __DIR__ . '/Debug_Parq/error_log.txt');

// ⚠️ CONFIGURACIÓN DE ZONA HORARIA - Ajustar según tu ubicación
date_default_timezone_set('America/Bogota'); // Colombia
=======
ini_set('error_log', __DIR__ . '/error_log.txt');
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

<<<<<<< HEAD
// Crear carpeta Debug_Parq si no existe
$carpetaDebug = __DIR__ . '/Debug_Parq';
if (!file_exists($carpetaDebug)) {
    mkdir($carpetaDebug, 0777, true);
}

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO CONTROLADOR PARQUEADERO ===\n", FILE_APPEND);
file_put_contents($carpetaDebug . '/debug_log.txt', "Zona horaria del servidor: " . date_default_timezone_get() . "\n", FILE_APPEND);
file_put_contents($carpetaDebug . '/debug_log.txt', "POST recibido: " . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

try {
    // Ruta a phpqrcode
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
=======
file_put_contents(__DIR__ . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO CONTROLADOR PARQUEADERO ===\n", FILE_APPEND);

try {
    // Cargar librería QR
    $ruta_qrlib = __DIR__ . '/../../libs/phpqrcode/qrlib.php';
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
<<<<<<< HEAD
    file_put_contents($carpetaDebug . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    // Ruta al modelo
    $ruta_modelo = __DIR__ . '/../Model/ModeloParqueadero.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;
    file_put_contents($carpetaDebug . '/debug_log.txt', "Modelo cargado correctamente\n", FILE_APPEND);

    class ControladorParqueadero {
        private $modelo;
        private $carpetaDebug;

        public function __construct() {
            $this->carpetaDebug = __DIR__ . '/Debug_Parq';
            $this->modelo = new ModeloParqueadero();
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Instancia de ControladorParqueadero creada\n", FILE_APPEND);
=======
    file_put_contents(__DIR__ . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    $ruta_modelo = __DIR__ . '/../../model/parqueadero_dispositivo/ModeloParqueadero.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }

    require_once $ruta_modelo;
    file_put_contents(__DIR__ . '/debug_log.txt', "Modelo cargado correctamente\n", FILE_APPEND);

    class ControladorParqueadero {
        private $modelo;
        private $logPath;

        public function __construct() {
            $this->logPath = __DIR__ . '/debug_log.txt';
            $this->modelo = new ModeloParqueadero();
            file_put_contents($this->logPath, "Instancia de ControladorParqueadero creada\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idVehiculo, string $tipo, string $placa, string $descripcion): ?string {
            try {
<<<<<<< HEAD
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Generando QR para vehículo ID: $idVehiculo\n", FILE_APPEND);

                // Carpeta en Public/qr/Qr_Parq
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Parq';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Carpeta QR vehículos creada: $rutaCarpeta\n", FILE_APPEND);
=======
                file_put_contents($this->logPath, "Generando QR para vehículo ID: $idVehiculo\n", FILE_APPEND);

                $rutaCarpeta = __DIR__ . '/../../qr';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($this->logPath, "Carpeta QR vehículos creada: $rutaCarpeta\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                }

                $nombreArchivo = "QR-VEHICULO-" . $idVehiculo . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                
<<<<<<< HEAD
                $contenidoQR = "VEHÍCULO\n";
=======
                // Contenido más detallado para el QR del vehículo
                $contenidoQR = "VEHÍCULO \n";
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                $contenidoQR .= "ID: $idVehiculo\n";
                $contenidoQR .= "Tipo: $tipo\n";
                $contenidoQR .= "Placa: $placa\n";
                $contenidoQR .= "Descripción: $descripcion\n";
                $contenidoQR .= "Fecha: " . date('Y-m-d H:i:s');

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 8);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

<<<<<<< HEAD
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                
                return 'qr/Qr_Parq/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR al generar QR vehículo: " . $e->getMessage() . "\n", FILE_APPEND);
=======
                file_put_contents($this->logPath, "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                return '/qr/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->logPath, "ERROR al generar QR vehículo: " . $e->getMessage() . "\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                return null;
            }
        }

        public function registrarVehiculo(array $datos): array {
<<<<<<< HEAD
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "registrarVehiculo llamado\n", FILE_APPEND);
=======
            file_put_contents($this->logPath, "registrarVehiculo llamado\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)

            $TipoVehiculo = $datos['TipoVehiculo'] ?? null;
            $PlacaVehiculo = $datos['PlacaVehiculo'] ?? null;
            $DescripcionVehiculo = $datos['DescripcionVehiculo'] ?? '';
            $TarjetaPropiedad = $datos['TarjetaPropiedad'] ?? '';
<<<<<<< HEAD
            $IdSede = $datos['IdSede'] ?? null;
            
            // ⭐ CAMBIO CRÍTICO: Generar la fecha SIEMPRE en el servidor
            // Ignorar cualquier fecha que venga del cliente
            $FechaParqueadero = date('Y-m-d H:i:s');
            
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Fecha generada en servidor: $FechaParqueadero\n", FILE_APPEND);

            // Validaciones de campos obligatorios
=======
            $FechaParqueadero = $datos['FechaParqueadero'] ?? date('Y-m-d H:i:s');
            $IdSede = $datos['IdSede'] ?? null;

            // Validaciones
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            if ($this->campoVacio($TipoVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Tipo de vehículo'];
            }
            if ($this->campoVacio($PlacaVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Placa del vehículo'];
            }
<<<<<<< HEAD
            if ($this->campoVacio($DescripcionVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Descripción del vehículo'];
            }
            if ($this->campoVacio($TarjetaPropiedad)) {
                return ['success' => false, 'message' => 'Falta el campo: Tarjeta de propiedad'];
            }
=======
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            if ($this->campoVacio($IdSede)) {
                return ['success' => false, 'message' => 'Falta el campo: Sede'];
            }

<<<<<<< HEAD
            // ✅ Ya no necesitamos validar la fecha porque la generamos aquí
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "✅ Usando fecha del servidor (sin validación de cliente)\n", FILE_APPEND);

=======
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            try {
                $resultado = $this->modelo->registrarVehiculo(
                    $TipoVehiculo, 
                    $PlacaVehiculo, 
                    $DescripcionVehiculo, 
                    $TarjetaPropiedad, 
                    $FechaParqueadero, 
                    $IdSede
                );

                if ($resultado['success']) {
                    $idVehiculo = $resultado['id'];
<<<<<<< HEAD
                    $rutaQR = $this->generarQR($idVehiculo, $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo);

                    if ($rutaQR) {
=======
                    
                    // Generar QR después del registro exitoso
                    $rutaQR = $this->generarQR($idVehiculo, $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo);

                    if ($rutaQR) {
                        // Actualizar el registro con la ruta del QR
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                        $this->modelo->actualizarQR($idVehiculo, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Vehículo registrado correctamente con ID: " . $idVehiculo,
                        "data" => [
                            "IdParqueadero" => $idVehiculo, 
<<<<<<< HEAD
                            "QrVehiculo" => $rutaQR,
                            "FechaRegistro" => $FechaParqueadero
=======
                            "QrVehiculo" => $rutaQR
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                        ]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en BD: ' . ($resultado['error'] ?? 'Desconocido')];
                }
            } catch (Exception $e) {
<<<<<<< HEAD
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en registrarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
=======
                file_put_contents($this->logPath, "EXCEPCIÓN en registrarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarVehiculo(int $id, array $datos): array {
<<<<<<< HEAD
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== actualizarVehiculo llamado ===\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "ID: $id\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Datos: " . json_encode($datos, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            try {
                // Obtener datos actuales para el QR
                $vehiculoAnterior = $this->modelo->obtenerPorId($id);
                $qrAnterior = $vehiculoAnterior['QrVehiculo'] ?? null;
                $placa = $vehiculoAnterior['PlacaVehiculo'] ?? '';
                
=======
            file_put_contents($this->logPath, "actualizarVehiculo llamado con ID: $id\n", FILE_APPEND);

            try {
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                $resultado = $this->modelo->actualizarVehiculo(
                    $id,
                    $datos['tipo'] ?? null,
                    $datos['descripcion'] ?? null,
                    $datos['idsede'] ?? null
                );
                
                if ($resultado['success']) {
<<<<<<< HEAD
                    // Regenerar QR con los nuevos datos
                    $tipo = $datos['tipo'] ?? '';
                    $descripcion = $datos['descripcion'] ?? '';
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Regenerando QR para ID: $id\n", FILE_APPEND);
                    
                    $nuevoQR = $this->generarQR($id, $tipo, $placa, $descripcion);
                    
                    if ($nuevoQR) {
                        $this->modelo->actualizarQR($id, $nuevoQR);
                        
                        // Eliminar QR anterior
                        if ($qrAnterior) {
                            $rutaQrAnterior = __DIR__ . '/../../Public/' . $qrAnterior;
                            if (file_exists($rutaQrAnterior)) {
                                unlink($rutaQrAnterior);
                                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR anterior eliminado: $rutaQrAnterior\n", FILE_APPEND);
                            }
                        }
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "Nuevo QR generado: $nuevoQR\n", FILE_APPEND);
                    }
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Vehículo actualizado exitosamente\n", FILE_APPEND);
                    return [
                        'success' => true, 
                        'message' => 'Vehículo actualizado correctamente',
                        'qr' => $nuevoQR
                    ];
                } else {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en actualizarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
=======
                    file_put_contents($this->logPath, "Vehículo actualizado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
                } else {
                    file_put_contents($this->logPath, "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->logPath, "EXCEPCIÓN en actualizarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function cambiarEstadoVehiculo(int $id, string $nuevoEstado): array {
<<<<<<< HEAD
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "cambiarEstadoVehiculo llamado con ID: $id, Estado: $nuevoEstado\n", FILE_APPEND);
=======
            file_put_contents($this->logPath, "cambiarEstadoVehiculo llamado con ID: $id, Estado: $nuevoEstado\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)

            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                
                if ($resultado['success']) {
                    $mensaje = $nuevoEstado === 'Activo' ? 'activado' : 'desactivado';
<<<<<<< HEAD
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Vehículo $mensaje exitosamente\n", FILE_APPEND);
=======
                    file_put_contents($this->logPath, "Vehículo $mensaje exitosamente\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                    return [
                        'success' => true, 
                        'message' => "Vehículo $mensaje correctamente",
                        'nuevoEstado' => $nuevoEstado
                    ];
                } else {
<<<<<<< HEAD
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al cambiar estado: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al cambiar el estado del vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en cambiar estado: " . $e->getMessage() . "\n", FILE_APPEND);
=======
                    file_put_contents($this->logPath, "Error al cambiar estado: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al cambiar el estado del vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->logPath, "EXCEPCIÓN en cambiar estado: " . $e->getMessage() . "\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    $controlador = new ControladorParqueadero();
    $accion = $_POST['accion'] ?? 'registrar';

<<<<<<< HEAD
    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarVehiculo($_POST);
        
    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        file_put_contents($carpetaDebug . '/debug_log.txt', "Procesando actualización para ID: $id\n", FILE_APPEND);
        
=======
    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarVehiculo($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        if ($id > 0) {
            $datos = [
                'tipo' => $_POST['tipo'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null,
                'idsede' => $_POST['idsede'] ?? null
            ];
<<<<<<< HEAD
            
            file_put_contents($carpetaDebug . '/debug_log.txt', "Datos preparados: " . json_encode($datos) . "\n", FILE_APPEND);
            
=======
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            $resultado = $controlador->actualizarVehiculo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
<<<<<<< HEAD
        
    } elseif ($accion === 'cambiar_estado') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
=======
    } elseif ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id'] ?? 0);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        $nuevoEstado = $_POST['estado'] ?? '';
        
        if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstadoVehiculo($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }
<<<<<<< HEAD
        
    } elseif ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id > 0) {
            $resultado = $controlador->cambiarEstadoVehiculo($id, 'Inactivo');
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
        
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    file_put_contents($carpetaDebug . '/debug_log.txt', "=== FIN ===\n\n", FILE_APPEND);
    
=======
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents(__DIR__ . '/debug_log.txt', "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $error = $e->getMessage();
<<<<<<< HEAD
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR FINAL: $error\n", FILE_APPEND);
=======
    file_put_contents(__DIR__ . '/debug_log.txt', "ERROR FINAL: $error\n", FILE_APPEND);
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $error,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>