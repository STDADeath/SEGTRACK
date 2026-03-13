<?php 

// ================= CONFIGURACIÓN DEBUG =================
$debugPath = __DIR__ . '/Debug_Func';

if (!file_exists($debugPath)) {
    mkdir($debugPath, 0777, true);
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $debugPath . '/error_log.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ================= DEPENDENCIAS =================
require_once __DIR__ . '/../Core/conexion.php';
require_once __DIR__ . '/../libs/phpqrcode/qrlib.php';
require_once __DIR__ . '/../Model/ModeloFuncionarios.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';

try {

    $conexion = (new Conexion())->getConexion();

    class ControladorFuncionario {

        private $modelo;
        private $logPath;
        private $conexion;

        // ╔══════════════════════════════════════════════════════╗
        // ║               CONSTRUCTOR                           ║
        // ╚══════════════════════════════════════════════════════╝
        public function __construct($conexion) {
            $this->conexion = $conexion;
            $this->modelo   = new ModeloFuncionarios($conexion);
            $this->logPath  = __DIR__ . '/Debug_Func/debug_log.txt';
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               HELPERS PRIVADOS                      ║
        // ╚══════════════════════════════════════════════════════╝

        // ── Escribe un mensaje en el log de debug ──────────────
        private function log($msg) {
            file_put_contents($this->logPath, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
        }

        // ── Verifica si un campo está vacío o no existe ────────
        private function campoVacio($campo): bool {
            return !isset($campo) || trim($campo) === '';
        }

        // ── Consulta el nombre (TipoSede) de una sede por ID ──
        // Usado para incluir el nombre de sede en el contenido del QR
        private function obtenerNombreSede(int $idSede): string {
            try {
                $sql  = "SELECT TipoSede FROM sede WHERE IdSede = :id LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':id' => $idSede]);
                return $stmt->fetchColumn() ?: 'Sin sede';
            } catch (Throwable $e) {
                $this->log("Error obtenerNombreSede: " . $e->getMessage());
                return 'Sin sede';
            }
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               GUARDAR FOTO                          ║
        // ╚══════════════════════════════════════════════════════╝
        // Guarda la foto en Public/qr/Fotos/
        // Acepta archivo subido ($_FILES) o foto de cámara (base64)
        // Retorna la ruta relativa o null si no hay foto
        private function guardarFoto(int $idFuncionario): ?string {

            try {
                $raiz        = realpath(__DIR__ . '/../../');
                $rutaCarpeta = $raiz . '/Public/qr/Fotos';

                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    chmod($rutaCarpeta, 0777);
                }

                $nombreArchivo = "FOTO-FUNC-" . $idFuncionario . "-" . uniqid() . ".jpg";
                $rutaCompleta  = $rutaCarpeta . '/' . $nombreArchivo;
                $rutaRelativa  = "qr/Fotos/" . $nombreArchivo;

                // ---- CASO 1: Foto subida como archivo ----
                if (
                    isset($_FILES['FotoFuncionario']) &&
                    $_FILES['FotoFuncionario']['error'] === UPLOAD_ERR_OK
                ) {
                    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $tipoMime = mime_content_type($_FILES['FotoFuncionario']['tmp_name']);

                    if (!in_array($tipoMime, $tiposPermitidos)) {
                        $this->log("Tipo de imagen no permitido: $tipoMime");
                        return null;
                    }

                    if (move_uploaded_file($_FILES['FotoFuncionario']['tmp_name'], $rutaCompleta)) {
                        $this->log("Foto guardada (archivo): $rutaCompleta");
                        return $rutaRelativa;
                    } else {
                        $this->log("Error al mover foto a: $rutaCompleta");
                        return null;
                    }
                }

                // ---- CASO 2: Foto capturada con cámara (base64) ----
                $base64 = $_POST['FotoCapturaBase64'] ?? '';
                if (!empty($base64)) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                        $imageData = substr($base64, strpos($base64, ',') + 1);
                        $imageData = base64_decode($imageData);

                        if ($imageData === false) {
                            $this->log("Error al decodificar base64 de cámara");
                            return null;
                        }

                        file_put_contents($rutaCompleta, $imageData);
                        $this->log("Foto guardada (cámara): $rutaCompleta");
                        return $rutaRelativa;
                    }
                }

                return null;

            } catch (Throwable $e) {
                $this->log("EXCEPCIÓN al guardar foto: " . $e->getMessage());
                return null;
            }
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               GENERAR QR                            ║
        // ╚══════════════════════════════════════════════════════╝
        // Genera el código QR en Public/qr/Qr_Func/
        // ✅ Contenido del QR: Nombre, Cargo, Estado, Sede
        // Retorna la ruta relativa o null si falla
        private function generarQR(
            int    $idFuncionario,
            string $nombre,
            string $cargo,
            string $estado,
            string $sede
        ): ?string {

            try {
                $raiz        = realpath(__DIR__ . '/../../');
                $rutaCarpeta = $raiz . '/Public/qr/Qr_Func';

                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                    chmod($rutaCarpeta, 0777);
                }

                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";
                $rutaCompleta  = $rutaCarpeta . '/' . $nombreArchivo;

                // ✅ Datos que se leen al escanear el QR
                $contenidoQR =
                    "Nombre: $nombre\n"  .
                    "Cargo: $cargo\n"    .
                    "Estado: $estado\n"  .
                    "Sede: $sede";

                ob_start();
                QRcode::png($contenidoQR, false, QR_ECLEVEL_H, 10, 2);
                $imageData = ob_get_contents();
                ob_end_clean();

                file_put_contents($rutaCompleta, $imageData);

                if (!file_exists($rutaCompleta)) {
                    $this->log("ERROR: No se pudo crear el QR en $rutaCompleta");
                    return null;
                }

                $this->log("QR generado exitosamente: $rutaCompleta");
                return "qr/Qr_Func/" . $nombreArchivo;

            } catch (Throwable $e) {
                $this->log("EXCEPCIÓN al generar QR: " . $e->getMessage());
                return null;
            }
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               TEMPLATE HTML DEL CORREO             ║
        // ╚══════════════════════════════════════════════════════╝
        // Genera el cuerpo HTML del correo con los datos del funcionario
        // y la imagen del QR embebida en base64
        private function generarHTMLCorreo(
            string $nombre,
            string $cargo,
            string $documento,
            string $correo,
            string $qrBase64,
            string $asunto
        ): string {

            $qrImgHtml = $qrBase64
                ? "<img src='{$qrBase64}' alt='Código QR'
                        style='width:180px; height:180px; display:block; margin:0 auto;
                               border:4px solid #ffffff; border-radius:8px;
                               box-shadow:0 4px 15px rgba(0,0,0,0.2);'>"
                : "<p style='color:#e74c3c; text-align:center; margin:0;'>No se pudo cargar el código QR.</p>";

            return "
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>{$asunto}</title>
</head>
<body style='margin:0; padding:0; background-color:#f0f4f8; font-family:Arial,Helvetica,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f0f4f8; padding:30px 0;'>
    <tr>
      <td align='center'>
        <table width='600' cellpadding='0' cellspacing='0'
               style='background-color:#ffffff; border-radius:12px;
                      overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.12);
                      max-width:600px; width:100%;'>

          <!-- CABECERA -->
          <tr>
            <td style='background:linear-gradient(135deg,#1a5fc8 0%,#2979e0 60%,#3a8ef6 100%);
                        padding:30px 40px 26px 40px; text-align:center;'>
              <table cellpadding='0' cellspacing='0' style='margin:0 auto;'>
                <tr>
                  <td valign='middle' style='padding-right:14px;'>
                    <img src='cid:logo_segtrack' alt='Logo SEGTRACK'
                         style='width:80px; height:auto; display:block; vertical-align:middle;'>
                  </td>
                  <td valign='middle'>
                    <span style='font-size:32px; font-weight:900; color:#ffffff;
                                 letter-spacing:3px; text-transform:uppercase;
                                 text-shadow:0 2px 8px rgba(0,0,0,0.2);
                                 font-family:Arial,Helvetica,sans-serif;'>
                      SEGTRACK
                    </span>
                  </td>
                </tr>
              </table>
              <p style='margin:10px 0 0 0; color:rgba(255,255,255,0.88);
                          font-size:13px; letter-spacing:1px; text-align:center;'>
                Sistema de Gestión de Seguridad
              </p>
            </td>
          </tr>

          <!-- CUERPO -->
          <tr>
            <td style='padding:36px 40px 20px 40px;'>
              <p style='margin:0 0 4px 0; font-size:18px; font-weight:700; color:#1a2d4e;'>
                Hola, {$nombre}
              </p>
              <p style='margin:0 0 26px 0; font-size:14px; color:#555; line-height:1.6;'>
                Has sido registrado exitosamente en el sistema SEGTRACK.<br>
                A continuación encontrarás tu información y tu código QR de acceso personal.
              </p>

              <!-- DATOS DEL FUNCIONARIO -->
              <table width='100%' cellpadding='0' cellspacing='0'
                     style='border-left:4px solid #2979e0; background-color:#f5f9ff;
                             border-radius:0 8px 8px 0; margin-bottom:26px;
                             box-shadow:0 2px 8px rgba(41,121,224,0.07);'>
                <tr>
                  <td style='padding:18px 20px;'>
                    <p style='margin:0 0 10px 0; font-size:13px; font-weight:700;
                               color:#1a5fc8; text-transform:uppercase; letter-spacing:1px;'>
                      📋 Información del Funcionario
                    </p>
                    <p style='margin:5px 0; font-size:14px; color:#333;'><strong>Nombre:</strong> {$nombre}</p>
                    <p style='margin:5px 0; font-size:14px; color:#333;'><strong>Cargo:</strong> {$cargo}</p>
                    <p style='margin:5px 0; font-size:14px; color:#333;'><strong>Documento:</strong> {$documento}</p>
                    <p style='margin:5px 0; font-size:14px; color:#333;'><strong>Correo:</strong> {$correo}</p>
                    <p style='margin:5px 0; font-size:14px; color:#333;'>
                      <strong>Estado:</strong>
                      <span style='background:#e8f5e9; color:#2e7d32; padding:2px 10px;
                                   border-radius:12px; font-size:12px; font-weight:700;'>
                        Activo
                      </span>
                    </p>
                  </td>
                </tr>
              </table>

              <!-- QR -->
              <table width='100%' cellpadding='0' cellspacing='0'
                     style='background:#f0f7ff; border-radius:8px; margin-bottom:20px;'>
                <tr>
        
                    <p style='margin:12px 0 0 0; font-size:12px; color:#888;'>
                      Presenta este código para registrar tu ingreso/salida
                    </p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

        </table>
        <p style='margin:18px 0 0 0; font-size:11px; color:#aaa; text-align:center;'>
          Este correo fue generado automáticamente. Por favor no respondas a este mensaje.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>";
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               ENVIAR CORREO CON QR                  ║
        // ╚══════════════════════════════════════════════════════╝
        // Envía el correo al funcionario con el QR adjunto
        // y embebido en el HTML usando PHPMailer + Gmail SMTP
        private function enviarCorreoConQR(
            string $correo,
            string $nombre,
            string $cargo,
            string $documento,
            string $rutaQR,
            string $asunto = 'Bienvenido a SEGTRACK — Su Código QR'
        ): bool {

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'seguridad.integral.segtrack@gmail.com';
                $mail->Password   = 'lwfc rpts gcog iysv';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'SEGTRACK - Administración');
                $mail->addAddress($correo, $nombre);

                // Logo embebido en el HTML del correo
                $rutaLogo = realpath(__DIR__ . '/../../Public/img/LOGO_SEGTRACK-re-con.ico');
                if ($rutaLogo && file_exists($rutaLogo)) {
                    $mail->addEmbeddedImage($rutaLogo, 'logo_segtrack');
                }

                // QR adjunto y embebido 
                $rutaFisicaQR = realpath(__DIR__ . '/../../Public/' . $rutaQR);
                $qrBase64     = '';

                if ($rutaFisicaQR && file_exists($rutaFisicaQR)) {
                    $mail->addAttachment($rutaFisicaQR, 'QR-Funcionario-SEGTRACK.png');
                    $qrBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rutaFisicaQR));
                } else {
                    $this->log("QR no encontrado en: " . __DIR__ . '/../../Public/' . $rutaQR);
                }

                $mail->isHTML(true);
                $mail->Subject = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
                $mail->Body    = $this->generarHTMLCorreo($nombre, $cargo, $documento, $correo, $qrBase64, $asunto);
                $mail->AltBody = "Hola $nombre,\n\nCargo: $cargo\nDocumento: $documento\nCorreo: $correo\n\nHas sido registrado en SEGTRACK.\n\nAtentamente,\nEquipo SEGTRACK";

                $mail->send();
                $this->log("Correo enviado correctamente a $correo");
                return true;

            } catch (Exception $e) {
                $this->log("Error al enviar correo a $correo: " . $e->getMessage());
                return false;
            }
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               REGISTRAR FUNCIONARIO                 ║
        // ╚══════════════════════════════════════════════════════╝
        // Valida campos, llama al modelo para insertar,
        // guarda la foto, genera el QR con los 4 datos
        // y envía el correo de bienvenida
        public function registrarFuncionario(array $datos): array {

            $cargo     = trim($datos['CargoFuncionario']    ?? '');
            $nombre    = trim($datos['NombreFuncionario']   ?? '');
            $sede      = $datos['IdSede']                   ?? '';
            $telefono  = trim($datos['TelefonoFuncionario'] ?? '');  // ✅ string
            $documento = trim($datos['DocumentoFuncionario']?? '');  // ✅ string
            $correo    = trim($datos['CorreoFuncionario']   ?? '');

            if ($this->campoVacio($cargo))     return ['success' => false, 'message' => 'Cargo requerido'];
            if ($this->campoVacio($nombre))    return ['success' => false, 'message' => 'Nombre requerido'];
            if ($this->campoVacio($sede))      return ['success' => false, 'message' => 'Sede requerida'];
            if ($this->campoVacio($documento)) return ['success' => false, 'message' => 'Documento requerido'];

            $duplicado = $this->modelo->validarDuplicados($documento, $correo, null);
            if ($duplicado) return ['success' => false, 'message' => 'Documento o correo ya existe'];

            $resultado = $this->modelo->RegistrarFuncionario(
                $cargo,
                $nombre,
                (int)$sede,
                $telefono,   // ✅ ya no se castea a int
                $documento,  // ✅ ya no se castea a int
                $correo
            );

            if ($resultado['success']) {

                $id = $resultado['id'];

                // ---- GUARDAR FOTO ----
                $rutaFoto = $this->guardarFoto($id);
                if ($rutaFoto) {
                    $this->modelo->actualizarFotoFuncionario($id, $rutaFoto);
                    $this->log("Foto guardada para funcionario $id: $rutaFoto");
                }

                // ---- OBTENER NOMBRE SEDE para el QR ----
                $nombreSede = $this->obtenerNombreSede((int)$sede);

                // ---- GENERAR QR con Nombre, Cargo, Estado, Sede ----
                $qr = $this->generarQR(
                    $id,
                    $nombre,
                    $cargo,
                    'Activo',       // Al registrar siempre es Activo
                    $nombreSede
                );

                if ($qr) {
                    $this->modelo->ActualizarQrFuncionario($id, $qr);
                    $this->enviarCorreoConQR(
                        $correo, $nombre, $cargo, $documento, $qr,
                        '¡Bienvenido a SEGTRACK! — Su Código QR de Acceso'
                    );
                }

                return [
                    'success' => true,
                    'message' => 'Funcionario registrado correctamente y QR enviado por correo',
                    'data'    => [
                        'IdFuncionario'       => $id,
                        'QrCodigoFuncionario' => $qr,
                        'FotoFuncionario'     => $rutaFoto
                    ]
                ];
            }

            return ['success' => false, 'message' => $resultado['error'] ?? 'Error al registrar'];
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               ACTUALIZAR FUNCIONARIO                ║
        // ╚══════════════════════════════════════════════════════╝
        // Actualiza los datos en BD, elimina el QR anterior,
        // regenera el QR con los datos actualizados y lo reenvía
        public function actualizarFuncionario(int $id, array $datos): array {

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID inválido'];
            }

            $rutaAnterior = $this->modelo->obtenerQrActual($id);
            $resultado    = $this->modelo->actualizar($id, $datos);

            if ($resultado['success']) {

                // Eliminar QR anterior del servidor
                if ($rutaAnterior) {
                    $rutaFisica = realpath(__DIR__ . '/../../Public/' . $rutaAnterior);
                    if ($rutaFisica && file_exists($rutaFisica)) {
                        unlink($rutaFisica);
                    }
                }

                $nombre    = trim($datos['NombreFuncionario']    ?? '');
                $cargo     = trim($datos['CargoFuncionario']     ?? '');
                $documento = trim($datos['DocumentoFuncionario'] ?? '');
                $correo    = trim($datos['CorreoFuncionario']    ?? '');
                $idSede    = (int)($datos['IdSede']              ?? 0);

                // Obtener estado actual desde BD
                $funcionario = $this->modelo->obtenerPorId($id);
                $estado      = $funcionario['Estado'] ?? 'Activo';

                // Obtener nombre de sede para el QR
                $nombreSede = $this->obtenerNombreSede($idSede);

                // Regenerar QR con datos actualizados
                $qr = $this->generarQR($id, $nombre, $cargo, $estado, $nombreSede);

                if ($qr) {
                    $this->modelo->ActualizarQrFuncionario($id, $qr);
                    $this->enviarCorreoConQR(
                        $correo, $nombre, $cargo, $documento, $qr,
                        'SEGTRACK — Tu Código QR ha sido actualizado'
                    );
                }

                return [
                    'success'             => true,
                    'message'             => 'Funcionario actualizado correctamente y QR reenviado',
                    'QrCodigoFuncionario' => $qr
                ];
            }

            return ['success' => false, 'message' => $resultado['error'] ?? 'Error al actualizar'];
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               CAMBIAR ESTADO                        ║
        // ╚══════════════════════════════════════════════════════╝
        // Cambia el estado del funcionario (Activo / Inactivo)
        // Validación: solo acepta esos dos valores exactos
        public function cambiarEstado(int $id, string $estado): array {

            if (!in_array($estado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'message' => 'Estado inválido'];
            }

            $resultado = $this->modelo->cambiarEstado($id, $estado);
            if ($resultado) return ['success' => true, 'message' => 'Estado actualizado correctamente'];

            return ['success' => false, 'message' => 'No se pudo actualizar estado'];
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               REGENERAR QR                          ║
        // ╚══════════════════════════════════════════════════════╝
        // Elimina el QR anterior, genera uno nuevo con los datos
        // actuales desde BD y lo envía por correo
        public function actualizarQR(int $id): array {

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID inválido'];
            }

            $funcionario = $this->modelo->obtenerPorId($id);
            if (!$funcionario) {
                return ['success' => false, 'message' => 'Funcionario no encontrado'];
            }

            // Eliminar QR anterior
            $rutaAnterior = $this->modelo->obtenerQrActual($id);
            if ($rutaAnterior) {
                $rutaFisica = realpath(__DIR__ . '/../../Public/' . $rutaAnterior);
                if ($rutaFisica && file_exists($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }

            // Obtener nombre de sede y estado actuales desde BD
            $nombreSede = $this->obtenerNombreSede((int)($funcionario['IdSede'] ?? 0));
            $estado     = $funcionario['Estado'] ?? 'Activo';

            // Regenerar QR con los 4 datos actuales
            $qr = $this->generarQR(
                $id,
                $funcionario['NombreFuncionario'],
                $funcionario['CargoFuncionario'],
                $estado,
                $nombreSede
            );

            if (!$qr) {
                return ['success' => false, 'message' => 'No se pudo generar el QR'];
            }

            $this->modelo->ActualizarQrFuncionario($id, $qr);
            $this->enviarCorreoConQR(
                $funcionario['CorreoFuncionario'],
                $funcionario['NombreFuncionario'],
                $funcionario['CargoFuncionario'],
                $funcionario['DocumentoFuncionario'],
                $qr,
                'SEGTRACK — Tu Código QR ha sido regenerado'
            );

            return [
                'success'             => true,
                'message'             => 'QR regenerado y enviado por correo correctamente',
                'QrCodigoFuncionario' => $qr
            ];
        }


        // ╔══════════════════════════════════════════════════════╗
        // ║               ENVIAR QR POR CORREO                  ║
        // ╚══════════════════════════════════════════════════════╝
        // Consulta la BD para obtener correo y ruta QR del funcionario
        // y reenvía el correo sin regenerar el QR
        public function enviarQRPorCorreo(int $idFuncionario): array {

            $this->log("=== enviarQRPorCorreo llamado para ID: $idFuncionario ===");

            try {
                $sql = "SELECT IdFuncionario, NombreFuncionario, CargoFuncionario,
                               DocumentoFuncionario, CorreoFuncionario, QrCodigoFuncionario
                        FROM funcionario
                        WHERE IdFuncionario = :id
                        LIMIT 1";

                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':id' => $idFuncionario]);
                $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$funcionario) {
                    throw new \Exception('Funcionario no encontrado');
                }

                $correoDestinatario = $funcionario['CorreoFuncionario'] ?? null;
                $nombreDestinatario = $funcionario['NombreFuncionario'] ?? null;

                if (!$correoDestinatario) {
                    throw new \Exception('El funcionario no tiene correo electrónico registrado');
                }

                if (empty($funcionario['QrCodigoFuncionario'])) {
                    throw new \Exception('Este funcionario no tiene código QR generado');
                }

                $rutaQR       = $funcionario['QrCodigoFuncionario'];
                $rutaFisicaQR = realpath(__DIR__ . '/../../Public/' . $rutaQR);

                if (!$rutaFisicaQR || !file_exists($rutaFisicaQR)) {
                    throw new \Exception('El archivo QR no existe en el servidor. Ruta: ' . $rutaQR);
                }

                $enviado = $this->enviarCorreoConQR(
                    $correoDestinatario,
                    $nombreDestinatario,
                    $funcionario['CargoFuncionario'],
                    $funcionario['DocumentoFuncionario'],
                    $rutaQR,
                    'SEGTRACK — Tu Código QR de Acceso'
                );

                if ($enviado) {
                    return [
                        'success' => true,
                        'message' => "Código QR enviado exitosamente a: {$correoDestinatario}"
                    ];
                } else {
                    throw new \Exception('No se pudo enviar el correo. Revise la configuración SMTP.');
                }

            } catch (\Exception $e) {
                $this->log("ERROR en enviarQRPorCorreo: " . $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

    } // fin clase ControladorFuncionario


    // ╔══════════════════════════════════════════════════════╗
    // ║               PUNTO DE ENTRADA AJAX                 ║
    // ╚══════════════════════════════════════════════════════╝
    // Recibe el POST con 'accion' y despacha al método correcto

    $controlador = new ControladorFuncionario($conexion);
    $accion      = $_POST['accion'] ?? '';

    switch ($accion) {

        case 'registrar':
            $resultado = $controlador->registrarFuncionario($_POST);
            break;

        case 'actualizar':
            $id        = (int)($_POST['IdFuncionario'] ?? 0);
            $resultado = $controlador->actualizarFuncionario($id, $_POST);
            break;

        case 'cambiar_estado':
            $id        = (int)($_POST['IdFuncionario'] ?? 0);
            $estado    = $_POST['Estado'] ?? '';
            $resultado = $controlador->cambiarEstado($id, $estado);
            break;

        case 'regenerar_qr':
            $id        = (int)($_POST['IdFuncionario'] ?? 0);
            $resultado = $controlador->actualizarQR($id);
            break;

        case 'enviar_qr':
            $id = (int)($_POST['IdFuncionario'] ?? 0);
            if ($id > 0) {
                $resultado = $controlador->enviarQRPorCorreo($id);
            } else {
                $resultado = ['success' => false, 'message' => 'ID de funcionario no válido'];
            }
            break;

        default:
            $resultado = ['success' => false, 'message' => 'Acción inválida: ' . $accion];
            break;
    }

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;