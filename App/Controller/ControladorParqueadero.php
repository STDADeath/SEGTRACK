<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Parq/error_log.txt');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ✅ Crear carpeta Debug_Parq si no existe
$carpetaDebug = __DIR__ . '/Debug_Parq';
if (!file_exists($carpetaDebug)) {
    mkdir($carpetaDebug, 0777, true);
}

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO CONTROLADOR PARQUEADERO ===\n", FILE_APPEND);

try {
    // ✅ Ruta corregida a phpqrcode
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents($carpetaDebug . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    // ✅ Ruta corregida al modelo
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
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idVehiculo, string $tipo, string $placa, string $descripcion): ?string {
            try {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Generando QR para vehículo ID: $idVehiculo\n", FILE_APPEND);

                // ✅ Carpeta en Public/qr/Qr_Parq
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Parq';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Carpeta QR vehículos creada: $rutaCarpeta\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-VEHICULO-" . $idVehiculo . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                
                // Contenido más detallado para el QR del vehículo
                $contenidoQR = "VEHÍCULO \n";
                $contenidoQR .= "ID: $idVehiculo\n";
                $contenidoQR .= "Tipo: $tipo\n";
                $contenidoQR .= "Placa: $placa\n";
                $contenidoQR .= "Descripción: $descripcion\n";
                $contenidoQR .= "Fecha: " . date('Y-m-d H:i:s');

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 8);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                
                // ✅ Retornar ruta relativa para la BD
                return 'qr/Qr_Parq/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR al generar QR vehículo: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        public function registrarVehiculo(array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "registrarVehiculo llamado\n", FILE_APPEND);

            $TipoVehiculo = $datos['TipoVehiculo'] ?? null;
            $PlacaVehiculo = $datos['PlacaVehiculo'] ?? null;
            $DescripcionVehiculo = $datos['DescripcionVehiculo'] ?? '';
            $TarjetaPropiedad = $datos['TarjetaPropiedad'] ?? '';
            $FechaParqueadero = $datos['FechaParqueadero'] ?? date('Y-m-d H:i:s');
            $IdSede = $datos['IdSede'] ?? null;

            // Validaciones
            if ($this->campoVacio($TipoVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Tipo de vehículo'];
            }
            if ($this->campoVacio($PlacaVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Placa del vehículo'];
            }
            if ($this->campoVacio($IdSede)) {
                return ['success' => false, 'message' => 'Falta el campo: Sede'];
            }

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
                    
                    // Generar QR después del registro exitoso
                    $rutaQR = $this->generarQR($idVehiculo, $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo);

                    if ($rutaQR) {
                        // Actualizar el registro con la ruta del QR
                        $this->modelo->actualizarQR($idVehiculo, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Vehículo registrado correctamente con ID: " . $idVehiculo,
                        "data" => [
                            "IdParqueadero" => $idVehiculo, 
                            "QrVehiculo" => $rutaQR
                        ]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en BD: ' . ($resultado['error'] ?? 'Desconocido')];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en registrarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarVehiculo(int $id, array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "actualizarVehiculo llamado con ID: $id\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->actualizarVehiculo(
                    $id,
                    $datos['tipo'] ?? null,
                    $datos['descripcion'] ?? null,
                    $datos['idsede'] ?? null
                );
                
                if ($resultado['success']) {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Vehículo actualizado exitosamente\n", FILE_APPEND);
                    return ['success' => true, 'message' => 'Vehículo actualizado correctamente'];
                } else {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al actualizar: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al actualizar vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en actualizarVehiculo: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function cambiarEstadoVehiculo(int $id, string $nuevoEstado): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "cambiarEstadoVehiculo llamado con ID: $id, Estado: $nuevoEstado\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                
                if ($resultado['success']) {
                    $mensaje = $nuevoEstado === 'Activo' ? 'activado' : 'desactivado';
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Vehículo $mensaje exitosamente\n", FILE_APPEND);
                    return [
                        'success' => true, 
                        'message' => "Vehículo $mensaje correctamente",
                        'nuevoEstado' => $nuevoEstado
                    ];
                } else {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al cambiar estado: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al cambiar el estado del vehículo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en cambiar estado: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    $controlador = new ControladorParqueadero();
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarVehiculo($_POST);
    } elseif ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $datos = [
                'tipo' => $_POST['tipo'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null,
                'idsede' => $_POST['idsede'] ?? null
            ];
            $resultado = $controlador->actualizarVehiculo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
    } elseif ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';
        
        if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstadoVehiculo($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $error = $e->getMessage();
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR FINAL: $error\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $error,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>