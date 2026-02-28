<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Parq/error_log.txt');

// ⚠️ CONFIGURACIÓN DE ZONA HORARIA - Ajustar según tu ubicación
date_default_timezone_set('America/Bogota'); // Colombia

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

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
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
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
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idVehiculo, string $tipo, string $placa, string $descripcion): ?string {
            try {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Generando QR para vehículo ID: $idVehiculo\n", FILE_APPEND);

                // Carpeta en Public/qr/Qr_Parq
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Parq';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Carpeta QR vehículos creada: $rutaCarpeta\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-VEHICULO-" . $idVehiculo . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                
                $contenidoQR = "VEHÍCULO\n";
                $contenidoQR .= "Placa: $placa\n";
                $contenidoQR .= "Tipo: $tipo\n";
                $contenidoQR .= "Descripción: $descripcion\n";
                $contenidoQR .= "Fecha: " . date('Y-m-d H:i:s');

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 8);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                
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
            $IdSede = $datos['IdSede'] ?? null;
            
            // Generar la fecha SIEMPRE en el servidor
            $FechaParqueadero = date('Y-m-d H:i:s');
            
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Fecha generada en servidor: $FechaParqueadero\n", FILE_APPEND);

            // Validaciones de campos obligatorios
            if ($this->campoVacio($TipoVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Tipo de vehículo'];
            }
            if ($this->campoVacio($PlacaVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Placa del vehículo'];
            }
            if ($this->campoVacio($DescripcionVehiculo)) {
                return ['success' => false, 'message' => 'Falta el campo: Descripción del vehículo'];
            }
            if ($this->campoVacio($TarjetaPropiedad)) {
                return ['success' => false, 'message' => 'Falta el campo: Tarjeta de propiedad'];
            }
            if ($this->campoVacio($IdSede)) {
                return ['success' => false, 'message' => 'Falta el campo: Sede'];
            }

            try {
                // 🆕 VALIDACIÓN 1: VERIFICAR SI LA PLACA YA EXISTE
                $placaExiste = $this->modelo->existePlaca($PlacaVehiculo);
                if ($placaExiste['existe']) {
                    $vehiculo = $placaExiste['vehiculo'];
                    $mensaje = "⚠️ La placa '{$PlacaVehiculo}' ya está registrada para un vehículo tipo '{$vehiculo['TipoVehiculo']}'.";
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Placa duplicada - $PlacaVehiculo\n", FILE_APPEND);
                    return ['success' => false, 'message' => $mensaje];
                }

                // 🆕 VALIDACIÓN 2: VERIFICAR SI LA TARJETA DE PROPIEDAD YA EXISTE
                $tarjetaExiste = $this->modelo->existeTarjetaPropiedad($TarjetaPropiedad);
                if ($tarjetaExiste['existe']) {
                    $vehiculo = $tarjetaExiste['vehiculo'];
                    $mensaje = "⚠️ La tarjeta de propiedad '{$TarjetaPropiedad}' ya está registrada para un vehículo tipo '{$vehiculo['TipoVehiculo']}'.";
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Tarjeta duplicada - $TarjetaPropiedad\n", FILE_APPEND);
                    return ['success' => false, 'message' => $mensaje];
                }

                // Si todas las validaciones pasan, proceder con el registro
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
                    $rutaQR = $this->generarQR($idVehiculo, $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo);

                    if ($rutaQR) {
                        $this->modelo->actualizarQR($idVehiculo, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Vehículo registrado correctamente con ID: " . $idVehiculo,
                        "data" => [
                            "IdParqueadero" => $idVehiculo, 
                            "QrVehiculo" => $rutaQR,
                            "FechaRegistro" => $FechaParqueadero
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
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== actualizarVehiculo llamado ===\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "ID: $id\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Datos: " . json_encode($datos, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            try {
                // Obtener datos actuales para el QR
                $vehiculoAnterior = $this->modelo->obtenerPorId($id);
                $qrAnterior = $vehiculoAnterior['QrVehiculo'] ?? null;
                $placa = $vehiculoAnterior['PlacaVehiculo'] ?? '';
                
                $resultado = $this->modelo->actualizarVehiculo(
                    $id,
                    $datos['tipo'] ?? null,
                    $datos['descripcion'] ?? null,
                    $datos['idsede'] ?? null
                );
                
                if ($resultado['success']) {
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

        // 🆕 FUNCIÓN: ENVIAR QR POR CORREO
        public function enviarQRPorCorreo(int $idVehiculo, string $correoDestinatario): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== enviarQRPorCorreo llamado para vehículo ID: $idVehiculo ===\n", FILE_APPEND);

            try {
                // Validar correo
                if (!filter_var($correoDestinatario, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('El correo electrónico no es válido');
                }

                // Cargar PHPMailer
                $rutaPHPMailer = __DIR__ . '/../Libs/PHPMailer-master/src/PHPMailer.php';
                $rutaSMTP = __DIR__ . '/../Libs/PHPMailer-master/src/SMTP.php';
                $rutaException = __DIR__ . '/../Libs/PHPMailer-master/src/Exception.php';

                if (!file_exists($rutaPHPMailer) || !file_exists($rutaSMTP) || !file_exists($rutaException)) {
                    throw new Exception('Librerías PHPMailer no encontradas en /Libs/PHPMailer-master/src/');
                }

                require_once $rutaException;
                require_once $rutaSMTP;
                require_once $rutaPHPMailer;

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "PHPMailer cargado\n", FILE_APPEND);

                // Obtener información del vehículo
                $vehiculo = $this->modelo->obtenerPorId($idVehiculo);

                if (!$vehiculo) {
                    throw new Exception('Vehículo no encontrado');
                }

                if ($vehiculo['Estado'] !== 'Activo') {
                    throw new Exception('El vehículo no está activo');
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Vehículo encontrado: " . json_encode($vehiculo) . "\n", FILE_APPEND);

                if (empty($vehiculo['QrVehiculo'])) {
                    throw new Exception('Este vehículo no tiene código QR generado');
                }

                // Ruta completa del QR
                $rutaQR = __DIR__ . '/../../Public/' . $vehiculo['QrVehiculo'];

                if (!file_exists($rutaQR)) {
                    throw new Exception('El archivo QR no existe: ' . $rutaQR);
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Preparando envío a: $correoDestinatario\n", FILE_APPEND);

                // Configurar PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'seguridad.integral.segtrack@gmail.com';
                $mail->Password = 'fhxj smlq jidt xnqs';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Sistema SEGTRACK');
                $mail->addAddress($correoDestinatario);
                $mail->addAttachment($rutaQR, 'QR-Vehiculo-' . $idVehiculo . '.png');
                $mail->addEmbeddedImage('../../Public/img/LOGO_SEGTRACK-re-con.ico', 'logo_segtrack');

                $mail->isHTML(true);
                $mail->Subject = 'Código QR - Vehículo Registrado';
                $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                        .content { background-color: #f8f9fc; padding: 30px; border: 1px solid #e3e6f0; }
                        .info-box { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #4e73df; }
                        .footer { text-align: center; padding: 20px; color: #858796; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1><img src='cid:logo_segtrack' alt='Logo SEGTRACK' class='logo' style='width:80px; vertical-align:middle;'> SEGTRACK</h1>
                            <p>Sistema de Gestión de Vehículos</p>
                        </div>
                        <div class='content'>
                            <h3>Código QR de su Vehículo</h3>
                            <p>Su vehículo ha sido registrado exitosamente en nuestro sistema de parqueadero.</p>
                            <div class='info-box'>
                                <strong>🚙 Información del Vehículo:</strong><br>
                                <strong>Tipo:</strong> {$vehiculo['TipoVehiculo']}<br>
                                <strong>Placa:</strong> {$vehiculo['PlacaVehiculo']}<br>
                                <strong>Descripción:</strong> {$vehiculo['DescripcionVehiculo']}<br>
                                <strong>Tarjeta de Propiedad:</strong> {$vehiculo['TarjetaPropiedad']}<br>
                                <strong>Fecha de Registro:</strong> {$vehiculo['FechaParqueadero']}
                            </div>
                            <p>Adjunto encontrarás el código QR de tu vehículo.</p>
                            <ul>
                                <li>✅ Presenta este código al ingresar al parqueadero</li>
                                <li>✅ Mantén este código disponible en tu dispositivo móvil</li>
                                <li>✅ Facilita el control de entrada y salida</li>
                            </ul>
                            <p><strong>⚠️ Importante:</strong> Guarda este código en un lugar seguro.</p>
                        </div>
                        <div class='footer'>
                            <p>Este es un correo automático, por favor no responder.</p>
                            <p>&copy; " . date('Y') . " SEGTRACK - Sistema de Gestión de Parqueadero</p>
                        </div>
                    </div>
                </body>
                </html>";

                $mail->send();

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "✓ Correo enviado exitosamente a: $correoDestinatario\n", FILE_APPEND);

                return [
                    'success' => true,
                    'message' => "Código QR enviado exitosamente a: {$correoDestinatario}"
                ];

            } catch (PHPMailer\PHPMailer\Exception $e) {
                $error = "Error PHPMailer: " . $e->getMessage();
                file_put_contents($this->carpetaDebug . '/debug_log.txt', $error . "\n", FILE_APPEND);
                return ['success' => false, 'message' => $error];
            } catch (Exception $e) {
                $error = $e->getMessage();
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: $error\n", FILE_APPEND);
                return ['success' => false, 'message' => $error];
            }
        }
    }

    $controlador = new ControladorParqueadero();
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarVehiculo($_POST);
        
    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        file_put_contents($carpetaDebug . '/debug_log.txt', "Procesando actualización para ID: $id\n", FILE_APPEND);
        
        if ($id > 0) {
            $datos = [
                'tipo' => $_POST['tipo'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null,
                'idsede' => $_POST['idsede'] ?? null
            ];
            
            file_put_contents($carpetaDebug . '/debug_log.txt', "Datos preparados: " . json_encode($datos) . "\n", FILE_APPEND);
            
            $resultado = $controlador->actualizarVehiculo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
        
    } elseif ($accion === 'cambiar_estado') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $nuevoEstado = $_POST['estado'] ?? '';
        
        if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstadoVehiculo($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }
        
    } elseif ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id > 0) {
            $resultado = $controlador->cambiarEstadoVehiculo($id, 'Inactivo');
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo no válido'];
        }
        
    } elseif ($accion === 'enviar_qr') {
        $id = isset($_POST['id_vehiculo']) ? (int)$_POST['id_vehiculo'] : 0;
        $correo = $_POST['correo_destinatario'] ?? '';
        
        if ($id > 0 && !empty($correo)) {
            $resultado = $controlador->enviarQRPorCorreo($id, $correo);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de vehículo o correo no válido'];
        }
        
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    file_put_contents($carpetaDebug . '/debug_log.txt', "=== FIN ===\n\n", FILE_APPEND);
    
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