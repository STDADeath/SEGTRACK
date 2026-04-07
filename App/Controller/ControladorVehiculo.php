<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Vehiculo/error_log.txt');

date_default_timezone_set('America/Bogota');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$carpetaDebug = __DIR__ . '/Debug_Vehiculo';
if (!file_exists($carpetaDebug)) mkdir($carpetaDebug, 0777, true);

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO CONTROLADOR VEHÍCULO ===\n", FILE_APPEND);
file_put_contents($carpetaDebug . '/debug_log.txt', "POST recibido: " . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

try {
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    require_once $ruta_qrlib;

    $ruta_modelo = __DIR__ . '/../Model/ModeloVehiculo.php';
    if (!file_exists($ruta_modelo)) throw new Exception("Modelo no encontrado: $ruta_modelo");
    require_once $ruta_modelo;

    class ControladorVehiculo {
        private $modelo;
        private $carpetaDebug;

        public function __construct() {
            $this->carpetaDebug = __DIR__ . '/Debug_Vehiculo';
            $this->modelo = new ModeloVehiculo();
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        private function generarQR(int $idVehiculo, string $tipo, string $placa, string $descripcion): ?string {
            try {
                $rutaCarpeta = __DIR__ . '/../../Public/qr/Qr_Vehiculo';
                if (!file_exists($rutaCarpeta)) mkdir($rutaCarpeta, 0777, true);

                $nombreArchivo = "QR-VEHICULO-" . $idVehiculo . "-" . uniqid() . ".png";
                $rutaCompleta  = $rutaCarpeta . '/' . $nombreArchivo;

              $contenidoQR  = "IdVehiculo: $idVehiculo\n";
$contenidoQR .= "VEHÍCULO\n";
$contenidoQR .= "Placa: $placa\n";
$contenidoQR .= "Tipo: $tipo\n";
$contenidoQR .= "Descripción: $descripcion\n";
$contenidoQR .= "Fecha: " . date('Y-m-d H:i:s');

                QRcode::png($contenidoQR, $rutaCompleta, QR_ECLEVEL_H, 8);

                if (!file_exists($rutaCompleta)) throw new Exception("El archivo QR no se creó");
                return 'qr/Qr_Vehiculo/' . $nombreArchivo;

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR al generar QR: " . $e->getMessage() . "\n", FILE_APPEND);
                return null;
            }
        }

        // ── Registrar vehículo ────────────────────────────────────────────────
        public function registrarVehiculo(array $datos): array {
            $TipoVehiculo        = $datos['TipoVehiculo']        ?? null;
            $PlacaVehiculo       = $datos['PlacaVehiculo']       ?? null;
            $DescripcionVehiculo = $datos['DescripcionVehiculo'] ?? '';
            $TarjetaPropiedad    = $datos['TarjetaPropiedad']    ?? '';
            $IdSede              = $datos['IdSede']              ?? null;
            $TipoPersona         = $datos['TipoPersona']         ?? null;
            $IdFuncionario       = $datos['IdFuncionario']       ?? null;
            $IdVisitante         = $datos['IdVisitante']         ?? null;
            $FechaDeVehiculo     = date('Y-m-d H:i:s');

            if ($this->campoVacio($TipoVehiculo))        return ['success' => false, 'message' => 'Falta el campo: Tipo de vehículo'];
            if ($this->campoVacio($PlacaVehiculo))       return ['success' => false, 'message' => 'Falta el campo: Placa del vehículo'];
            if ($this->campoVacio($DescripcionVehiculo)) return ['success' => false, 'message' => 'Falta el campo: Descripción'];
            if ($this->campoVacio($TarjetaPropiedad))    return ['success' => false, 'message' => 'Falta el campo: Tarjeta de propiedad'];
            if ($this->campoVacio($IdSede))              return ['success' => false, 'message' => 'Falta el campo: Sede'];
            if ($this->campoVacio($TipoPersona))         return ['success' => false, 'message' => 'Debe seleccionar si el vehículo es de un Funcionario o Visitante'];
            if ($TipoPersona === 'Funcionario' && $this->campoVacio($IdFuncionario)) return ['success' => false, 'message' => 'Debe seleccionar el funcionario'];
            if ($TipoPersona === 'Visitante'   && $this->campoVacio($IdVisitante))   return ['success' => false, 'message' => 'Debe seleccionar el visitante'];

            if ($TipoPersona === 'Funcionario') $IdVisitante   = null;
            if ($TipoPersona === 'Visitante')   $IdFuncionario = null;

            // Normalizar placa de bicicleta siempre a "N-A"
            if ($TipoVehiculo === 'Bicicleta') {
                $PlacaVehiculo = 'N-A';
            }

            try {
                // ── Validar placa duplicada SOLO si NO es bicicleta (N-A puede repetirse)
                if ($TipoVehiculo !== 'Bicicleta') {
                    $placaExiste = $this->modelo->existePlaca($PlacaVehiculo);
                    if ($placaExiste['existe']) {
                        $v = $placaExiste['vehiculo'];
                        return ['success' => false, 'message' => "⚠️ La placa '{$PlacaVehiculo}' ya está registrada para un vehículo tipo '{$v['TipoVehiculo']}'."];
                    }
                }

                $tarjetaExiste = $this->modelo->existeTarjetaPropiedad($TarjetaPropiedad);
                if ($tarjetaExiste['existe']) {
                    $v = $tarjetaExiste['vehiculo'];
                    return ['success' => false, 'message' => "⚠️ La tarjeta '{$TarjetaPropiedad}' ya está registrada para un vehículo tipo '{$v['TipoVehiculo']}'."];
                }

                $resultado = $this->modelo->registrarVehiculo(
                    $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo,
                    $TarjetaPropiedad, $FechaDeVehiculo, $IdSede,
                    $IdFuncionario, $IdVisitante
                );

                if ($resultado['success']) {
                    $idVehiculo = $resultado['id'];
                    $rutaQR = $this->generarQR($idVehiculo, $TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo);
                    if ($rutaQR) $this->modelo->actualizarQR($idVehiculo, $rutaQR);

                    return [
                        'success' => true,
                        'message' => "Vehículo registrado correctamente",
                        'data'    => ['IdVehiculo' => $idVehiculo, 'QrVehiculo' => $rutaQR, 'FechaRegistro' => $FechaDeVehiculo]
                    ];
                }
                return ['success' => false, 'message' => 'Error al registrar en BD: ' . ($resultado['error'] ?? 'Desconocido')];

            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Actualizar vehículo ───────────────────────────────────────────────
        public function actualizarVehiculo(int $id, array $datos): array {
            try {
                $vehiculoAnterior = $this->modelo->obtenerPorId($id);
                $qrAnterior = $vehiculoAnterior['QrVehiculo'] ?? null;
                $placa      = $vehiculoAnterior['PlacaVehiculo'] ?? '';

                $resultado = $this->modelo->actualizarVehiculo($id, $datos['tipo'] ?? null, $datos['descripcion'] ?? null, $datos['idsede'] ?? null);

                if ($resultado['success']) {
                    $nuevoQR = $this->generarQR($id, $datos['tipo'] ?? '', $placa, $datos['descripcion'] ?? '');
                    if ($nuevoQR) {
                        $this->modelo->actualizarQR($id, $nuevoQR);
                        if ($qrAnterior) {
                            $rutaAnterior = __DIR__ . '/../../Public/' . $qrAnterior;
                            if (file_exists($rutaAnterior)) unlink($rutaAnterior);
                        }
                    }
                    return ['success' => true, 'message' => 'Vehículo actualizado correctamente', 'qr' => $nuevoQR];
                }
                return ['success' => false, 'message' => 'Error al actualizar vehículo'];

            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Cambiar estado ────────────────────────────────────────────────────
        public function cambiarEstadoVehiculo(int $id, string $nuevoEstado): array {
            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                if ($resultado['success']) {
                    $msg = $nuevoEstado === 'Activo' ? 'activado' : 'desactivado';
                    return ['success' => true, 'message' => "Vehículo $msg correctamente", 'nuevoEstado' => $nuevoEstado];
                }
                return ['success' => false, 'message' => 'Error al cambiar el estado del vehículo'];
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Obtener sedes por institución ─────────────────────────────────────
        // ✅ NUEVO: acción que faltaba en el router
        public function obtenerSedesPorInstitucion(int $idInstitucion): array {
            try {
                $sedes = $this->modelo->obtenerSedesPorInstitucion($idInstitucion);
                if (empty($sedes)) {
                    return [
                        'success' => false,
                        'sedes'   => [],
                        'message' => 'Esta institución no tiene sedes activas registradas'
                    ];
                }
                return [
                    'success' => true,
                    'sedes'   => $sedes
                ];
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR obtenerSedesPorInstitucion: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        // ── Obtener funcionarios y visitantes por sede ────────────────────────
        public function obtenerPersonasPorSede(int $idSede): array {
            try {
                $funcionarios = $this->modelo->obtenerFuncionariosPorSede($idSede);
                $visitantes   = $this->modelo->obtenerVisitantesPorSede($idSede);
                return [
                    'success'      => true,
                    'funcionarios' => $funcionarios,
                    'visitantes'   => $visitantes
                ];
            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        // ── Enviar QR por correo ──────────────────────────────────────────────
        public function enviarQRPorCorreo(int $idVehiculo, string $correoManual = ''): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "enviarQRPorCorreo vehículo ID: $idVehiculo\n", FILE_APPEND);

            try {
                $vehiculo = $this->modelo->obtenerPorId($idVehiculo);
                if (!$vehiculo)                       throw new Exception('Vehículo no encontrado');
                if ($vehiculo['Estado'] !== 'Activo') throw new Exception('El vehículo no está activo');
                if (empty($vehiculo['QrVehiculo']))   throw new Exception('Este vehículo no tiene código QR generado');

                $correoDestino = '';
                $esFuncionario = !empty($vehiculo['IdFuncionario']);

                if ($esFuncionario) {
                    $correoDestino = $this->modelo->obtenerCorreoFuncionarioPorVehiculo($idVehiculo);
                    if (empty($correoDestino)) throw new Exception('El funcionario asociado no tiene correo registrado');
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Correo automático de funcionario: $correoDestino\n", FILE_APPEND);
                } else {
                    $correoDestino = $this->modelo->obtenerCorreoVisitantePorVehiculo($idVehiculo);
                    file_put_contents($this->carpetaDebug . '/debug_log.txt', "Correo visitante en BD: " . ($correoDestino ?? 'ninguno') . "\n", FILE_APPEND);
                    if (empty($correoDestino)) {
                        $correoDestino = trim($correoManual);
                    }
                    if (empty($correoDestino)) throw new Exception('El visitante no tiene correo registrado. Ingresa uno manualmente.');
                }

                if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL))
                    throw new Exception('El correo electrónico no es válido: ' . $correoDestino);

                $rutaPHPMailer = __DIR__ . '/../Libs/PHPMailer-master/src/PHPMailer.php';
                $rutaSMTP      = __DIR__ . '/../Libs/PHPMailer-master/src/SMTP.php';
                $rutaException = __DIR__ . '/../Libs/PHPMailer-master/src/Exception.php';

                if (!file_exists($rutaPHPMailer) || !file_exists($rutaSMTP) || !file_exists($rutaException))
                    throw new Exception('Librerías PHPMailer no encontradas');

                require_once $rutaException;
                require_once $rutaSMTP;
                require_once $rutaPHPMailer;

                $rutaQR = __DIR__ . '/../../Public/' . $vehiculo['QrVehiculo'];
                if (!file_exists($rutaQR)) throw new Exception('El archivo QR no existe: ' . $rutaQR);

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'Segtracksecurity@gmail.com';
                $mail->Password   = 'zujj ccjo niaa krdo';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Sistema SEGTRACK');
                $mail->addAddress($correoDestino);
                $mail->addAttachment($rutaQR, 'QR-Vehiculo-' . $idVehiculo . '.png');
                $rutaLogo = __DIR__ . '/../../Public/img/LOGO_SEGTRACK-re-con.png';
                if (file_exists($rutaLogo)) {
                    $mail->addEmbeddedImage($rutaLogo, 'logo_segtrack');
                }

                $mail->isHTML(true);
                $mail->Subject = 'Código QR - Vehículo Registrado';
                $mail->Body    = "
                <html><head>
                <style>
                    body{font-family:Arial,sans-serif;line-height:1.6;color:#333}
                    .container{max-width:600px;margin:0 auto;padding:20px}
                    .header{background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;padding:30px 20px;text-align:center;border-radius:8px 8px 0 0}
                    .content{background:#f8f9fc;padding:30px;border:1px solid #e3e6f0}
                    .info-box{background:#fff;padding:20px;margin:20px 0;border-left:4px solid #4e73df}
                    .footer{text-align:center;padding:20px;color:#858796;font-size:12px}
                </style>
                </head><body>
                <div class='container'>
                    <div class='header'>
                        <h1><img src='cid:logo_segtrack' alt='Logo SEGTRACK' style='width:80px;vertical-align:middle'> SEGTRACK</h1>
                        <p>Sistema de Gestión de Vehículos</p>
                    </div>
                    <div class='content'>
                        <h3>Código QR de su Vehículo</h3>
                        <p>Su vehículo ha sido registrado exitosamente en nuestro sistema.</p>
                        <div class='info-box'>
                            <strong>🚙 Información del Vehículo:</strong><br>
                            <strong>Tipo:</strong> {$vehiculo['TipoVehiculo']}<br>
                            <strong>Placa:</strong> {$vehiculo['PlacaVehiculo']}<br>
                            <strong>Descripción:</strong> {$vehiculo['DescripcionVehiculo']}<br>
                            <strong>Tarjeta de Propiedad:</strong> {$vehiculo['TarjetaPropiedad']}<br>
                            <strong>Fecha de Registro:</strong> {$vehiculo['FechaDeVehiculo']}
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
                        <p>&copy; " . date('Y') . " SEGTRACK - Sistema de Gestión de Vehículos</p>
                    </div>
                </div>
                </body></html>";

                $mail->send();
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "✅ Correo enviado a: $correoDestino\n", FILE_APPEND);

                return [
                    'success'       => true,
                    'message'       => "Código QR enviado exitosamente a: {$correoDestino}",
                    'esFuncionario' => $esFuncionario,
                    'correo'        => $correoDestino
                ];

            } catch (PHPMailer\PHPMailer\Exception $e) {
                $error = "Error PHPMailer: " . $e->getMessage();
                file_put_contents($this->carpetaDebug . '/debug_log.txt', $error . "\n", FILE_APPEND);
                return ['success' => false, 'message' => $error];
            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        // ── Verificar tipo de propietario ─────────────────────────────────────
        public function obtenerTipoPropietario(int $idVehiculo): array {
            try {
                $vehiculo = $this->modelo->obtenerPorId($idVehiculo);
                if (!$vehiculo) return ['success' => false, 'message' => 'Vehículo no encontrado'];

                if (!empty($vehiculo['IdFuncionario'])) {
                    return ['success' => true, 'tipo' => 'Funcionario'];
                } elseif (!empty($vehiculo['IdVisitante'])) {
                    return ['success' => true, 'tipo' => 'Visitante'];
                }
                return ['success' => true, 'tipo' => 'SinAsignar'];

            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
    }

    // ── Router ────────────────────────────────────────────────────────────────
    $controlador = new ControladorVehiculo();
    $accion      = $_POST['accion'] ?? 'registrar';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarVehiculo($_POST);

    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $resultado = $id > 0
            ? $controlador->actualizarVehiculo($id, ['tipo' => $_POST['tipo'] ?? null, 'descripcion' => $_POST['descripcion'] ?? null, 'idsede' => $_POST['idsede'] ?? null])
            : ['success' => false, 'message' => 'ID de vehículo no válido'];

    } elseif ($accion === 'cambiar_estado') {
        $id          = isset($_POST['id'])     ? (int)$_POST['id'] : 0;
        $nuevoEstado = $_POST['estado']        ?? '';
        $resultado   = ($id > 0 && in_array($nuevoEstado, ['Activo', 'Inactivo']))
            ? $controlador->cambiarEstadoVehiculo($id, $nuevoEstado)
            : ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];

    } elseif ($accion === 'eliminar') {
        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $resultado = $id > 0
            ? $controlador->cambiarEstadoVehiculo($id, 'Inactivo')
            : ['success' => false, 'message' => 'ID de vehículo no válido'];

    } elseif ($accion === 'enviar_qr') {
        $id     = isset($_POST['id_vehiculo']) ? (int)$_POST['id_vehiculo'] : 0;
        $correo = $_POST['correo_destinatario'] ?? '';
        $resultado = $id > 0
            ? $controlador->enviarQRPorCorreo($id, $correo)
            : ['success' => false, 'message' => 'ID de vehículo no válido'];

    } elseif ($accion === 'obtener_tipo_propietario') {
        $id        = isset($_POST['id_vehiculo']) ? (int)$_POST['id_vehiculo'] : 0;
        $resultado = $id > 0
            ? $controlador->obtenerTipoPropietario($id)
            : ['success' => false, 'message' => 'ID no válido'];

    // ✅ CORREGIDO: acción para cargar sedes por institución (faltaba en el router original)
    } elseif ($accion === 'obtener_sedes_por_institucion') {
        $idInstitucion = isset($_POST['id_institucion']) ? (int)$_POST['id_institucion'] : 0;
        $resultado = $idInstitucion > 0
            ? $controlador->obtenerSedesPorInstitucion($idInstitucion)
            : ['success' => false, 'message' => 'ID de institución no válido'];

    } elseif ($accion === 'obtener_personas_por_sede') {
        $idSede    = isset($_POST['id_sede']) ? (int)$_POST['id_sede'] : 0;
        $resultado = $idSede > 0
            ? $controlador->obtenerPersonasPorSede($idSede)
            : ['success' => false, 'message' => 'ID de sede no válido'];

    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt', "Respuesta: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n=== FIN ===\n\n", FILE_APPEND);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $error = $e->getMessage();
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR FINAL: $error\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $error], JSON_UNESCAPED_UNICODE);
}
exit;
?>