<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Disp/error_log.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$carpetaDebug = __DIR__ . '/Debug_Disp';
if (!file_exists($carpetaDebug)) {
    mkdir($carpetaDebug, 0777, true);
}

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }
    require_once $ruta_conexion;

    $conexionObj = new Conexion();
    $conexion    = $conexionObj->getConexion();

    if (!isset($conexion) || !($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;

    $ruta_modelo = __DIR__ . "/../Model/ModeloDispositivo.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;

    class ControladorDispositivo {
        private $modelo;
        private $carpetaDebug;
        private $conexion;

        public function __construct($conexion) {
            $this->conexion     = $conexion;
            $this->modelo       = new ModeloDispositivo($conexion);
            $this->carpetaDebug = __DIR__ . '/Debug_Disp';
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idDispositivo, string $tipo, string $marca, string $numeroSerial = ''): ?string {
            try {
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Dipo';
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                }

                $nombreArchivo = "QR-DISP-" . $idDispositivo . "-" . uniqid() . ".png";
                $rutaCompleta  = $rutaCarpeta . '/' . $nombreArchivo;
                $contenidoQR   = "Serial: $numeroSerial\nTipo: $tipo\nMarca: $marca";

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 10);

                if (!file_exists($rutaCompleta)) {
                    throw new Exception("El archivo QR no se creó correctamente");
                }

                return 'qr/Qr_Dipo/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        // ════════════════════════════════════════════════════════════
        // MÉTODOS PARA CASCADA
        // ════════════════════════════════════════════════════════════

        public function obtenerInstituciones(): array {
            return $this->modelo->obtenerInstituciones();
        }

        public function obtenerSedesPorInstitucion(int $idInstitucion): array {
            $sedes = $this->modelo->obtenerSedesPorInstitucion($idInstitucion);
            return ['success' => true, 'sedes' => $sedes];
        }

        public function obtenerFuncionariosPorSede(int $idSede): array {
            $funcionarios = $this->modelo->obtenerFuncionariosPorSede($idSede);
            return ['success' => true, 'funcionarios' => $funcionarios];
        }

        public function obtenerVisitantesPorSede(int $idSede): array {
            $visitantes = $this->modelo->obtenerVisitantesPorSede($idSede);
            return ['success' => true, 'visitantes' => $visitantes];
        }

        // ════════════════════════════════════════════════════════════
        // REGISTRAR DISPOSITIVO
        // ════════════════════════════════════════════════════════════
        public function registrarDispositivo(array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "registrarDispositivo llamado\n", FILE_APPEND);

            $tipo          = $datos['TipoDispositivo']     ?? null;
            $marca         = $datos['MarcaDispositivo']    ?? null;
            $numeroSerial  = $datos['NumeroSerial']        ?? null;
            $otroTipo      = $datos['OtroTipoDispositivo'] ?? null;
            $idFuncionario = $datos['IdFuncionario']       ?? null;
            $idVisitante   = $datos['IdVisitante']         ?? null;

            if ($this->campoVacio($tipo))  return ['success' => false, 'message' => 'Falta el campo: Tipo de dispositivo'];
            if ($this->campoVacio($marca)) return ['success' => false, 'message' => 'Falta el campo: Marca del dispositivo'];
            
            if ($tipo === 'Otro' && $this->campoVacio($otroTipo)) {
                return ['success' => false, 'message' => 'Debe especificar el tipo de dispositivo'];
            }

            $tieneFuncionario = !$this->campoVacio($idFuncionario);
            $tieneVisitante   = !$this->campoVacio($idVisitante);
            
            if (!$tieneFuncionario && !$tieneVisitante) {
                return ['success' => false, 'message' => 'Debe seleccionar un funcionario o un visitante'];
            }
            
            if ($tieneFuncionario && $tieneVisitante) {
                return ['success' => false, 'message' => 'No puede seleccionar funcionario y visitante al mismo tiempo'];
            }

            $tipoFinal = ($tipo === 'Otro') ? $otroTipo : $tipo;
            $numeroSerial = $numeroSerial ?: '';

            try {
                $idFunc = $tieneFuncionario ? (int)$idFuncionario : null;
                $idVis  = $tieneVisitante   ? (int)$idVisitante   : null;

                if (!empty($numeroSerial)) {
                    $serialExiste = $this->modelo->existeNumeroSerial($numeroSerial);
                    if ($serialExiste['existe']) {
                        $disp    = $serialExiste['dispositivo'];
                        $mensaje = "⚠️ El número serial '{$numeroSerial}' ya está registrado en otro dispositivo "
                                 . "(ID: {$disp['IdDispositivo']} - {$disp['TipoDispositivo']} {$disp['MarcaDispositivo']}).";
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                $resultado = $this->modelo->registrarDispositivo($tipoFinal, $marca, $numeroSerial, $idFunc, $idVis);

                if ($resultado['success']) {
                    $idDispositivo = $resultado['id'];
                    $rutaQR = $this->generarQR($idDispositivo, $tipoFinal, $marca, $numeroSerial);
                    if ($rutaQR) $this->modelo->actualizarQR($idDispositivo, $rutaQR);

                    return [
                        "success" => true,
                        "message" => "Dispositivo registrado correctamente con ID: " . $idDispositivo,
                        "data"    => ["IdDispositivo" => $idDispositivo, "QrDispositivo" => $rutaQR]
                    ];
                }
                return ['success' => false, 'message' => 'Error al registrar en BD: ' . ($resultado['error'] ?? 'desconocido')];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ════════════════════════════════════════════════════════════
        // ACTUALIZAR DISPOSITIVO
        // ════════════════════════════════════════════════════════════
        public function actualizarDispositivo(int $id, array $datos): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== actualizarDispositivo ID: $id ===\n", FILE_APPEND);

            if ($this->campoVacio($datos['TipoDispositivo']  ?? null)) {
                return ['success' => false, 'message' => 'El tipo de dispositivo es obligatorio'];
            }
            if ($this->campoVacio($datos['MarcaDispositivo'] ?? null)) {
                return ['success' => false, 'message' => 'La marca del dispositivo es obligatoria'];
            }

            try {
                $numeroSerial  = $datos['NumeroSerial']  ?? '';
                $idFuncionario = !empty($datos['IdFuncionario']) ? (int)$datos['IdFuncionario'] : null;
                $idVisitante   = !empty($datos['IdVisitante'])   ? (int)$datos['IdVisitante']   : null;

                if (!empty($numeroSerial)) {
                    $serialExiste = $this->modelo->existeNumeroSerial($numeroSerial, $id);
                    if ($serialExiste['existe']) {
                        $disp    = $serialExiste['dispositivo'];
                        $mensaje = "⚠️ El número serial '{$numeroSerial}' ya está registrado en otro dispositivo "
                                 . "(ID: {$disp['IdDispositivo']} - {$disp['TipoDispositivo']} {$disp['MarcaDispositivo']}).";
                        return ['success' => false, 'message' => $mensaje];
                    }
                }

                $dispositivoAnterior = $this->modelo->obtenerPorId($id);
                $qrAnterior          = $dispositivoAnterior['QrDispositivo'] ?? null;
                
                $datosActualizar = [
                    'TipoDispositivo'  => $datos['TipoDispositivo'],
                    'MarcaDispositivo' => $datos['MarcaDispositivo'],
                    'NumeroSerial'     => $numeroSerial,
                    'IdFuncionario'    => $idFuncionario,
                    'IdVisitante'      => $idVisitante,
                ];
                
                $resultado = $this->modelo->actualizar($id, $datosActualizar);

                if ($resultado['success']) {
                    $tipo    = $datos['TipoDispositivo'];
                    $marca   = $datos['MarcaDispositivo'];
                    $nuevoQR = $this->generarQR($id, $tipo, $marca, $numeroSerial);

                    if ($nuevoQR) {
                        $this->modelo->actualizarQR($id, $nuevoQR);
                        if ($qrAnterior) {
                            $rutaQrAnterior = __DIR__ . '/../../Public/' . $qrAnterior;
                            if (file_exists($rutaQrAnterior)) unlink($rutaQrAnterior);
                        }
                    }

                    return ['success' => true, 'message' => 'Dispositivo actualizado correctamente', 'rows' => $resultado['rows'] ?? 0, 'qr' => $nuevoQR];
                }
                return ['success' => false, 'message' => $resultado['error'] ?? 'Error desconocido al actualizar'];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN en actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function cambiarEstadoDispositivo(int $id, string $nuevoEstado): array {
            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                if ($resultado['success']) {
                    $mensaje = $nuevoEstado === 'Activo' ? 'activado' : 'desactivado';
                    return ['success' => true, 'message' => "Dispositivo $mensaje correctamente", 'nuevoEstado' => $nuevoEstado];
                }
                return ['success' => false, 'message' => 'Error al cambiar el estado del dispositivo'];
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        public function enviarQRPorCorreo(int $idDispositivo): array {
            try {
                $rutaPHPMailer = __DIR__ . '/../Libs/PHPMailer-master/src/PHPMailer.php';
                $rutaSMTP      = __DIR__ . '/../Libs/PHPMailer-master/src/SMTP.php';
                $rutaException = __DIR__ . '/../Libs/PHPMailer-master/src/Exception.php';

                if (!file_exists($rutaPHPMailer) || !file_exists($rutaSMTP) || !file_exists($rutaException)) {
                    throw new Exception('Librerías PHPMailer no encontradas');
                }

                require_once $rutaException;
                require_once $rutaSMTP;
                require_once $rutaPHPMailer;

                $sql = "SELECT d.IdDispositivo, d.TipoDispositivo, d.MarcaDispositivo, d.NumeroSerial,
                               d.QrDispositivo, f.NombreFuncionario, f.CorreoFuncionario,
                               v.NombreVisitante, v.CorreoVisitante
                        FROM dispositivo d
                        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                        LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante
                        WHERE d.IdDispositivo = :id AND d.Estado = 'Activo'";

                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':id' => $idDispositivo]);
                $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$dispositivo) throw new Exception('Dispositivo no encontrado o inactivo');

                if (!empty($dispositivo['CorreoFuncionario'])) {
                    $correoDestinatario = $dispositivo['CorreoFuncionario'];
                    $nombreDestinatario = $dispositivo['NombreFuncionario'];
                } elseif (!empty($dispositivo['CorreoVisitante'])) {
                    $correoDestinatario = $dispositivo['CorreoVisitante'];
                    $nombreDestinatario = $dispositivo['NombreVisitante'];
                } else {
                    throw new Exception('No se encontró correo electrónico del funcionario ni del visitante');
                }

                if (empty($dispositivo['QrDispositivo'])) throw new Exception('Este dispositivo no tiene código QR generado');

                $rutaQR = __DIR__ . '/../../Public/' . $dispositivo['QrDispositivo'];
                if (!file_exists($rutaQR)) throw new Exception('El archivo QR no existe: ' . $rutaQR);

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'seguridad.integral.segtrack@gmail.com';
                $mail->Password   = 'bhrs xjis zvan fxkw';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->SMTPDebug   = 0;
                $mail->Debugoutput = function($str, $level) {
                    file_put_contents($this->carpetaDebug . '/debug_log.txt',
                        "[SMTP DEBUG nivel $level] $str\n", FILE_APPEND);
                };

                $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Sistema SEGTRACK');
                $mail->addAddress($correoDestinatario, $nombreDestinatario);
                $mail->addAttachment($rutaQR, 'QR-Dispositivo-' . $idDispositivo . '.png');

                $rutaLogo = __DIR__ . '/../../Public/img/LOGO_SEGTRACK-re-con.png';
                if (file_exists($rutaLogo)) {
                    $mail->addEmbeddedImage($rutaLogo, 'logo_segtrack');
                    $imgLogo = "<img src='cid:logo_segtrack' alt='SEGTRACK' style='width:80px;vertical-align:middle;margin-right:10px;'>";
                } else {
                    $imgLogo = '';
                }

                $serialInfo = !empty($dispositivo['NumeroSerial'])
                    ? "<strong>Número Serial:</strong> {$dispositivo['NumeroSerial']}<br>"
                    : '';

                $mail->isHTML(true);
                $mail->Subject = 'Código QR - Dispositivo Registrado';
                $mail->Body    = "
                <html><head><style>
                    body{font-family:Arial,sans-serif;line-height:1.6;color:#333}
                    .container{max-width:600px;margin:0 auto;padding:20px}
                    .header{background:linear-gradient(135deg,#4e73df 0%,#224abe 100%);color:white;padding:30px 20px;text-align:center;border-radius:8px 8px 0 0}
                    .content{background-color:#f8f9fc;padding:30px;border:1px solid #e3e6f0}
                    .info-box{background-color:white;padding:20px;margin:20px 0;border-left:4px solid #4e73df}
                    .footer{text-align:center;padding:20px;color:#858796;font-size:12px}
                </style></head><body>
                <div class='container'>
                    <div class='header'>
                        <h1>{$imgLogo} SEGTRACK</h1>
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
                </div></body></html>";

                $mail->send();
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "✅ Correo enviado a: $correoDestinatario\n", FILE_APPEND);

                return ['success' => true, 'message' => "Código QR enviado exitosamente a: {$correoDestinatario}"];

            } catch (PHPMailer\PHPMailer\Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR PHPMailer: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => "Error PHPMailer: " . $e->getMessage()];
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
    }

    // ════════════════════════════════════════════════════════════
    // ROUTER
    // ════════════════════════════════════════════════════════════
    $controlador = new ControladorDispositivo($conexion);
    $accion      = $_POST['accion'] ?? 'registrar';

    file_put_contents($carpetaDebug . '/debug_log.txt', "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarDispositivo($_POST);

    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $datos = [
                'TipoDispositivo'  => $_POST['tipo']           ?? null,
                'MarcaDispositivo' => $_POST['marca']          ?? null,
                'NumeroSerial'     => $_POST['serial']         ?? null,
                'IdFuncionario'    => $_POST['id_funcionario'] ?? null,
                'IdVisitante'      => $_POST['id_visitante']   ?? null,
            ];
            $resultado = $controlador->actualizarDispositivo($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de dispositivo no válido'];
        }

    } elseif ($accion === 'cambiar_estado') {
        $id          = isset($_POST['id'])     ? (int)$_POST['id'] : 0;
        $nuevoEstado = $_POST['estado']        ?? '';
        if ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstadoDispositivo($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }

    } elseif ($accion === 'enviar_qr') {
        $id        = isset($_POST['id_dispositivo']) ? (int)$_POST['id_dispositivo'] : 0;
        $resultado = $id > 0
            ? $controlador->enviarQRPorCorreo($id)
            : ['success' => false, 'message' => 'ID de dispositivo no válido'];

    } elseif ($accion === 'obtener_instituciones') {
        $resultado = $controlador->obtenerInstituciones();

    } elseif ($accion === 'obtener_sedes_por_institucion') {
        $idInst    = isset($_POST['id_institucion']) ? (int)$_POST['id_institucion'] : 0;
        $resultado = $controlador->obtenerSedesPorInstitucion($idInst);

    } elseif ($accion === 'obtener_funcionarios_por_sede') {
        $idSede    = isset($_POST['id_sede']) ? (int)$_POST['id_sede'] : 0;
        $resultado = $controlador->obtenerFuncionariosPorSede($idSede);

    } elseif ($accion === 'obtener_visitantes_por_sede') {
        $idSede    = isset($_POST['id_sede']) ? (int)$_POST['id_sede'] : 0;
        $resultado = $controlador->obtenerVisitantesPorSede($idSede);

    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta final: " . json_encode($resultado) . "\n=== FIN ===\n\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR GENERAL: " . $e->getMessage() . "\n=== FIN ===\n\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>