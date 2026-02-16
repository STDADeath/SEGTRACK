<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Disp/error_log.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Crear carpeta Debug_Disp si no existe
$carpetaDebug = __DIR__ . '/Debug_Disp';
if (!file_exists($carpetaDebug)) {
    mkdir($carpetaDebug, 0777, true);
}

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    file_put_contents($carpetaDebug . '/debug_log.txt', "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // Ruta a conexion.php
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }

    require_once $ruta_conexion;
    file_put_contents($carpetaDebug . '/debug_log.txt', "Conexión cargada\n", FILE_APPEND);

    // Crear instancia de la clase Conexion y obtener el objeto PDO
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    if (!isset($conexion)) {
        throw new Exception("Variable \$conexion no inicializada");
    }

    if (!($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Conexión verificada como PDO\n", FILE_APPEND);

    // Ruta a phpqrcode
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents($carpetaDebug . '/debug_log.txt', "Librería QR cargada\n", FILE_APPEND);

    // Ruta al modelo
    $ruta_modelo = __DIR__ . "/../Model/ModeloDispositivo.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;
    file_put_contents($carpetaDebug . '/debug_log.txt', "Modelo cargado\n", FILE_APPEND);

    class ControladorDispositivo {
        private $modelo;
        private $carpetaDebug;
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
            $this->modelo = new ModeloDispositivo($conexion);
            $this->carpetaDebug = __DIR__ . '/Debug_Disp';
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idDispositivo, string $tipo, string $marca): ?string {
            try {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Generando QR para dispositivo ID: $idDispositivo\n", FILE_APPEND);

                // Carpeta qr_dipo en Public
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Dipo';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Carpeta QR creada: $rutaCarpeta\n", FILE_APPEND);
                }

                $nombreArchivo = "QR-DISP-" . $idDispositivo . "-" . uniqid() . ".png";
                $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR = "ID: $idDispositivo\nTipo: $tipo\nMarca: $marca";

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR generado exitosamente: $rutaCompleta\n", FILE_APPEND);
                
                // Retornar ruta relativa para la BD
                return 'qr/Qr_Dipo/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        public function registrarDispositivo(array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "registrarDispositivo llamado\n", FILE_APPEND);

            $tipo = $datos['TipoDispositivo'] ?? null;
            $marca = $datos['MarcaDispositivo'] ?? null;
            $numeroSerial = $datos['NumeroSerial'] ?? null;
            $otroTipo = $datos['OtroTipoDispositivo'] ?? null;
            $idFuncionario = $datos['IdFuncionario'] ?? null;
            $idVisitante = $datos['IdVisitante'] ?? null;

            if ($this->campoVacio($tipo)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Tipo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Tipo de dispositivo'];
            }

            if ($this->campoVacio($marca)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Marca vacía\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Falta el campo: Marca del dispositivo'];
            }

            // VALIDACIÓN NÚMERO SERIAL (OPCIONAL)
            if ($this->campoVacio($numeroSerial)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "WARNING: Número serial vacío\n", FILE_APPEND);
                $numeroSerial = '';
            }

            if ($tipo === 'Otro' && $this->campoVacio($otroTipo)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Otro tipo vacío\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Debe especificar el tipo de dispositivo'];
            }

            $tipoFinal = ($tipo === 'Otro') ? $otroTipo : $tipo;

            try {
                $idFunc = $this->campoVacio($idFuncionario) ? null : (int)$idFuncionario;
                $idVis = $this->campoVacio($idVisitante) ? null : (int)$idVisitante;

                // 🆕 VALIDACIÓN 1: VERIFICAR SI EL NÚMERO SERIAL YA EXISTE
                if (!empty($numeroSerial)) {
                    $serialExiste = $this->modelo->existeNumeroSerial($numeroSerial);
                    if ($serialExiste['existe']) {
                        $disp = $serialExiste['dispositivo'];
                        $mensaje = "⚠️ El número serial '{$numeroSerial}' ya está registrado";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Serial duplicado - $numeroSerial\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // 🆕 VALIDACIÓN 2: VERIFICAR SI EL FUNCIONARIO YA TIENE UN DISPOSITIVO DEL MISMO TIPO
                if ($idFunc !== null) {
                    $funcionarioTiene = $this->modelo->funcionarioTieneDispositivo($idFunc, $tipoFinal);
                    if ($funcionarioTiene['existe']) {
                        $disp = $funcionarioTiene['dispositivo'];
                        $serialInfo = !empty($disp['NumeroSerial']) ? " (Serial: {$disp['NumeroSerial']})" : "";
                        $mensaje = "⚠️ Este funcionario ya tiene un dispositivo tipo '{$tipoFinal}' registrado";
                        $mensaje .= "No se puede registrar otro dispositivo del mismo tipo para el mismo funcionario.";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Funcionario ya tiene dispositivo tipo $tipoFinal\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // 🆕 VALIDACIÓN 3: VERIFICAR SI EL VISITANTE YA TIENE UN DISPOSITIVO DEL MISMO TIPO
                if ($idVis !== null) {
                    $visitanteTiene = $this->modelo->visitanteTieneDispositivo($idVis, $tipoFinal);
                    if ($visitanteTiene['existe']) {
                        $disp = $visitanteTiene['dispositivo'];
                        $serialInfo = !empty($disp['NumeroSerial']) ? " (Serial: {$disp['NumeroSerial']})" : "";
                        $mensaje = "⚠️ Este visitante ya tiene un dispositivo tipo '{$tipoFinal}' registrado:\n";
                        $mensaje .= "• Dispositivo ID: {$disp['IdDispositivo']}\n";
                        $mensaje .= "• Marca: {$disp['MarcaDispositivo']}{$serialInfo}\n\n";
                        $mensaje .= "No se puede registrar otro dispositivo del mismo tipo para el mismo visitante.";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Visitante ya tiene dispositivo tipo $tipoFinal\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // Si todas las validaciones pasan, proceder con el registro
                $resultado = $this->modelo->registrarDispositivo($tipoFinal, $marca, $numeroSerial, $idFunc, $idVis);

                if ($resultado['success']) {
                    $idDispositivo = $resultado['id'];
                    $rutaQR = $this->generarQR($idDispositivo, $tipoFinal, $marca);

                    if ($rutaQR) {
                        $this->modelo->actualizarQR($idDispositivo, $rutaQR);
                    }

                    return [
                        "success" => true,
                        "message" => "Dispositivo registrado correctamente con ID: " . $idDispositivo,
                        "data" => ["IdDispositivo" => $idDispositivo, "QrDispositivo" => $rutaQR]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Error al registrar en BD: ' . ($resultado['error'] ?? 'desconocido')];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function actualizarDispositivo(int $id, array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== actualizarDispositivo llamado ===\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "ID: $id\n", FILE_APPEND);
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "Datos recibidos: " . json_encode($datos, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            // Validaciones básicas
            if ($this->campoVacio($datos['TipoDispositivo'] ?? null)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Tipo vacío en actualización\n", FILE_APPEND);
                return ['success' => false, 'message' => 'El tipo de dispositivo es obligatorio'];
            }

            if ($this->campoVacio($datos['MarcaDispositivo'] ?? null)) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Marca vacía en actualización\n", FILE_APPEND);
                return ['success' => false, 'message' => 'La marca del dispositivo es obligatoria'];
            }

            try {
                $numeroSerial = $datos['NumeroSerial'] ?? '';
                $tipoDispositivo = $datos['TipoDispositivo'];
                $idFuncionario = !empty($datos['IdFuncionario']) ? (int)$datos['IdFuncionario'] : null;
                $idVisitante = !empty($datos['IdVisitante']) ? (int)$datos['IdVisitante'] : null;

                // 🆕 VALIDACIÓN 1: VERIFICAR SI EL NÚMERO SERIAL YA EXISTE (EXCLUYENDO EL ACTUAL)
                if (!empty($numeroSerial)) {
                    $serialExiste = $this->modelo->existeNumeroSerial($numeroSerial, $id);
                    if ($serialExiste['existe']) {
                        $disp = $serialExiste['dispositivo'];
                        $mensaje = "⚠️ El número serial '{$numeroSerial}' ya está registrado en otro dispositivo:\n";
                        $mensaje .= "• Dispositivo ID: {$disp['IdDispositivo']}\n";
                        $mensaje .= "• Tipo: {$disp['TipoDispositivo']}\n";
                        $mensaje .= "• Marca: {$disp['MarcaDispositivo']}";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Serial duplicado al actualizar - $numeroSerial\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // 🆕 VALIDACIÓN 2: VERIFICAR SI EL FUNCIONARIO YA TIENE OTRO DISPOSITIVO DEL MISMO TIPO
                if ($idFuncionario !== null) {
                    $funcionarioTiene = $this->modelo->funcionarioTieneDispositivo($idFuncionario, $tipoDispositivo, $id);
                    if ($funcionarioTiene['existe']) {
                        $disp = $funcionarioTiene['dispositivo'];
                        $serialInfo = !empty($disp['NumeroSerial']) ? " (Serial: {$disp['NumeroSerial']})" : "";
                        $mensaje = "⚠️ Este funcionario ya tiene otro dispositivo tipo '{$tipoDispositivo}' registrado:\n";
                        $mensaje .= "• Dispositivo ID: {$disp['IdDispositivo']}\n";
                        $mensaje .= "• Marca: {$disp['MarcaDispositivo']}{$serialInfo}\n\n";
                        $mensaje .= "No se puede asignar otro dispositivo del mismo tipo al mismo funcionario.";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Funcionario ya tiene otro dispositivo tipo $tipoDispositivo\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // 🆕 VALIDACIÓN 3: VERIFICAR SI EL VISITANTE YA TIENE OTRO DISPOSITIVO DEL MISMO TIPO
                if ($idVisitante !== null) {
                    $visitanteTiene = $this->modelo->visitanteTieneDispositivo($idVisitante, $tipoDispositivo, $id);
                    if ($visitanteTiene['existe']) {
                        $disp = $visitanteTiene['dispositivo'];
                        $serialInfo = !empty($disp['NumeroSerial']) ? " (Serial: {$disp['NumeroSerial']})" : "";
                        $mensaje = "⚠️ Este visitante ya tiene otro dispositivo tipo '{$tipoDispositivo}' registrado:\n";
                        $mensaje .= "• Dispositivo ID: {$disp['IdDispositivo']}\n";
                        $mensaje .= "• Marca: {$disp['MarcaDispositivo']}{$serialInfo}\n\n";
                        $mensaje .= "No se puede asignar otro dispositivo del mismo tipo al mismo visitante.";
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: Visitante ya tiene otro dispositivo tipo $tipoDispositivo\n", FILE_APPEND);
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                // Obtener el QR anterior para eliminarlo después
                $dispositivoAnterior = $this->modelo->obtenerPorId($id);
                $qrAnterior = $dispositivoAnterior['QrDispositivo'] ?? null;
                
                // Si todas las validaciones pasan, proceder con la actualización
                $resultado = $this->modelo->actualizar($id, $datos);
                
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Resultado del modelo: " . json_encode($resultado) . "\n", FILE_APPEND);
                
                if ($resultado['success']) {
                    // Regenerar el QR con los nuevos datos
                    $tipo = $datos['TipoDispositivo'];
                    $marca = $datos['MarcaDispositivo'];
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Regenerando QR para ID: $id\n", FILE_APPEND);
                    
                    $nuevoQR = $this->generarQR($id, $tipo, $marca);
                    
                    if ($nuevoQR) {
                        // Actualizar la ruta del QR en la BD
                        $this->modelo->actualizarQR($id, $nuevoQR);
                        
                        // Eliminar el QR anterior si existe
                        if ($qrAnterior) {
                            $rutaQrAnterior = __DIR__ . '/../../Public/' . $qrAnterior;
                            if (file_exists($rutaQrAnterior)) {
                                unlink($rutaQrAnterior);
                                file_put_contents($this->carpetaDebug . '/debug_log.txt', "QR anterior eliminado: $rutaQrAnterior\n", FILE_APPEND);
                            }
                        }
                        
                        file_put_contents($this->carpetaDebug . '/debug_log.txt', "Nuevo QR generado: $nuevoQR\n", FILE_APPEND);
                    }
                    
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Dispositivo actualizado exitosamente\n", FILE_APPEND);
                    return [
                        'success' => true, 
                        'message' => 'Dispositivo actualizado correctamente',
                        'rows' => $resultado['rows'] ?? 0,
                        'qr' => $nuevoQR
                    ];
                } else {
                    $errorMsg = $resultado['error'] ?? 'Error desconocido al actualizar';
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al actualizar: $errorMsg\n", FILE_APPEND);
                    return ['success' => false, 'message' => $errorMsg];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function cambiarEstadoDispositivo(int $id, string $nuevoEstado): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "cambiarEstadoDispositivo llamado con ID: $id, Estado: $nuevoEstado\n", FILE_APPEND);

            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                
                if ($resultado['success']) {
                    $mensaje = $nuevoEstado === 'Activo' ? 'activado' : 'desactivado';
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Dispositivo $mensaje exitosamente\n", FILE_APPEND);
                    return [
                        'success' => true, 
                        'message' => "Dispositivo $mensaje correctamente",
                        'nuevoEstado' => $nuevoEstado
                    ];
                } else {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Error al cambiar estado: " . ($resultado['error'] ?? 'desconocido') . "\n", FILE_APPEND);
                    return ['success' => false, 'message' => 'Error al cambiar el estado del dispositivo'];
                }
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en cambiar estado: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // 🆕 NUEVA FUNCIÓN: ENVIAR QR POR CORREO
        public function enviarQRPorCorreo(int $idDispositivo): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== enviarQRPorCorreo llamado para ID: $idDispositivo ===\n", FILE_APPEND);

            try {
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

                // Consultar información del dispositivo y correo
                $sql = "SELECT 
                            d.IdDispositivo,
                            d.TipoDispositivo,
                            d.MarcaDispositivo,
                            d.NumeroSerial,
                            d.QrDispositivo,
                            f.NombreFuncionario,
                            f.CorreoFuncionario
                        FROM dispositivo d
                        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                        WHERE d.IdDispositivo = :id AND d.Estado = 'Activo'";
                
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':id' => $idDispositivo]);
                $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$dispositivo) {
                    throw new Exception('Dispositivo no encontrado o inactivo');
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Dispositivo encontrado: " . json_encode($dispositivo) . "\n", FILE_APPEND);

                // Validar correo (por ahora solo funcionarios)
                $correoDestinatario = $dispositivo['CorreoFuncionario'] ?? null;
                $nombreDestinatario = $dispositivo['NombreFuncionario'] ?? null;

                if (!$correoDestinatario) {
                    throw new Exception('No se encontró correo electrónico del funcionario');
                }

                if (empty($dispositivo['QrDispositivo'])) {
                    throw new Exception('Este dispositivo no tiene código QR generado');
                }

                // Ruta completa del QR
                $rutaQR = __DIR__ . '/../../Public/' . $dispositivo['QrDispositivo'];

                if (!file_exists($rutaQR)) {
                    throw new Exception('El archivo QR no existe: ' . $rutaQR);
                }

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "Preparando envío a: $correoDestinatario\n", FILE_APPEND);

                // Configurar PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'seguridad.integral.segtrack@gmail.com'; // ⚠️ TU CORREO
                $mail->Password = 'fhxj smlq jidt xnqs'; // ⚠️ TU CONTRASEÑA DE APLICACIÓN
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Sistema SEGTRACK'); // ⚠️ TU CORREO
                $mail->addAddress($correoDestinatario, $nombreDestinatario);
                $mail->addAttachment($rutaQR, 'QR-Dispositivo-' . $idDispositivo . '.png');
                $mail->addEmbeddedImage('../../Public/img/LOGO_SEGTRACK-re-con.ico', 'logo_segtrack');
                
                $mail->isHTML(true);
                $mail->Subject = 'Código QR - Dispositivo Registrado';
                
                // 🆕 INCLUIR NÚMERO SERIAL EN EL CORREO SI EXISTE
                $serialInfo = !empty($dispositivo['NumeroSerial']) 
                    ? "<strong>Número Serial:</strong> {$dispositivo['NumeroSerial']}<br>" 
                    : '';
                
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
                            <p>Sistema de Gestión de Seguridad</p>
                        </div>
                        <div class='content'>
                            <h3>Hola, {$nombreDestinatario}</h3>
                            <p>Tu dispositivo ha sido registrado exitosamente.</p>
                            <div class='info-box'>
                                <strong>📱 Información del Dispositivo:</strong><br>
                                <strong>Tipo:</strong> {$dispositivo['TipoDispositivo']}<br>
                                <strong>Marca:</strong> {$dispositivo['MarcaDispositivo']}<br>
                                {$serialInfo}
                            </div>
                            <p>Adjunto encontrarás el código QR de tu dispositivo.</p>
                            <p><strong>⚠️ Importante:</strong> Guarda este código en un lugar seguro.</p>
                            <ul>
                                <li>✅ Presenta este código al ingresar al parqueadero</li>
                                <li>✅ Mantén este código disponible en tu dispositivo móvil</li>
                                <li>✅ Facilita el control de entrada y salida</li>
                            </ul>
                        </div>
                        <div class='footer'>
                            <p>Este es un correo automático, por favor no responder.</p>
                            <p>&copy; " . date('Y') . " SEGTRACK</p>
                        </div>
                    </div>
                </body>
                </html>";

                $mail->send();

                file_put_contents($this->carpetaDebug . '/debug_log.txt', "✓ Correo enviado exitosamente\n", FILE_APPEND);

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

    $controlador = new ControladorDispositivo($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarDispositivo($_POST);
        
    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        file_put_contents($carpetaDebug . '/debug_log.txt', "Procesando actualización para ID: $id\n", FILE_APPEND);
        
        if ($id > 0) {
            $datos = [
                'TipoDispositivo' => $_POST['tipo'] ?? null,
                'MarcaDispositivo' => $_POST['marca'] ?? null,
                'NumeroSerial' => $_POST['serial'] ?? null, // 🆕 NUEVO
                'IdFuncionario' => $_POST['id_funcionario'] ?? null,
                'IdVisitante' => $_POST['id_visitante'] ?? null
            ];
            
            file_put_contents($carpetaDebug . '/debug_log.txt', "Datos preparados para actualizar: " . json_encode($datos) . "\n", FILE_APPEND);
            
            $resultado = $controlador->actualizarDispositivo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de dispositivo no válido'];
        }
        
    } elseif ($accion === 'cambiar_estado') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $nuevoEstado = $_POST['estado'] ?? '';
        
        if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstadoDispositivo($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }
        
    } elseif ($accion === 'enviar_qr') {
        // 🆕 NUEVA ACCIÓN: ENVIAR QR
        $id = isset($_POST['id_dispositivo']) ? (int)$_POST['id_dispositivo'] : 0;
        
        if ($id > 0) {
            $resultado = $controlador->enviarQRPorCorreo($id);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de dispositivo no válido'];
        }
        
    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta final: " . json_encode($resultado) . "\n", FILE_APPEND);
    file_put_contents($carpetaDebug . '/debug_log.txt', "=== FIN ===\n\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    
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