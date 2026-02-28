<?php
header('Content-Type: application/json');

// Incluir PHPMailer
// NOTA: Verifica que estas rutas sean correctas. Basado en tu imagen,
// la ruta relativa parece ser correcta: ../../../PHPMailer/src/
require '../../libs/PHPMailer/src/Exception.php';
require '../../libs/PHPMailer/src/PHPMailer.php';
require '../../libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// También puedes agregar 'use PHPMailer\PHPMailer\SMTP;' aunque no es estrictamente necesario si usas el método ::ENCRYPTION_STARTTLS

// Incluir conexión a base de datos
require_once '../../Core/conexion.php';

// Verificar que llegue el correo
if (!isset($_POST['correo']) || empty($_POST['correo'])) {
echo json_encode([
'success' => false,
 'message' => 'Por favor ingresa un correo electrónico'
 ]);
 exit;
}

$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

// Validar formato de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
 echo json_encode([
 'success' => false,
 'message' => 'El correo electrónico no es válido'
 ]);
 exit;
}

try {
/*Verificar si el correo existe en funcionario y obtener el usuario asociado*/
 $stmt = $conexion->prepare("
 SELECT u.IdUsuario, f.CorreoFuncionario, f.NombreFuncionario
 FROM usuario u
 INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
 WHERE f.CorreoFuncionario = ? AND u.Estado = 'Activo'
 ");
 $stmt->execute([$correo]);

if ($stmt->rowCount() === 0) {
 echo json_encode([
 'success' => false,
 'message' => 'El correo electrónico no está registrado en el sistema'
 ]);
 exit;
 }
 $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

//Generar token aleatorio de 6 dígitos
 $token = random_int(100000, 999999);
 $token_expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Guardar token en la base de datos
 $stmt = $conexion->prepare("
UPDATE usuario 
 SET TokenRecuperacion = ?, 
TokenExpiracion = ? 
WHERE IdUsuario = ?
 ");
$stmt->execute([$token, $token_expiracion, $usuario['IdUsuario']]);

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
// Configuración del servidor SMTP
$mail->isSMTP();
 $mail->Host = 'smtp.gmail.com';
 $mail->SMTPAuth = true;
 $mail->Username = 'seguridad.integral.segtrack@gmail.com';
// Usar la Contraseña de Aplicación de Google
$mail->Password = 'lwfc rpts gcog iysv';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587; // Puerto estándar para STARTTLS

// ====================================================================
// 🔑 SOLUCIÓN AL ERROR DE CERTIFICADO SSL:
// Añadir esta opción para deshabilitar la verificación del certificado 
// CA en entornos locales de desarrollo (como XAMPP/WAMP).
// ====================================================================
 $mail->SMTPOptions = array(
'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false,
'allow_self_signed' => true
)
);

// Configuración del correo
$mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Segtrack Sistema');
$mail->addAddress($correo, $usuario['NombreFuncionario']);

// Contenido del correo
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->Subject = 'Recuperación de Contraseña - Segtrack';
$mail->Body = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Recuperación de Contraseña</title>
</head>

<body style='margin:0; padding:0; background-color:#f0f4f8;
             font-family:Arial,Helvetica,sans-serif;'>

<table width='100%' cellpadding='0' cellspacing='0'
       style='background-color:#f0f4f8; padding:30px 0;'>
<tr>
<td align='center'>

<table width='600' cellpadding='0' cellspacing='0'
       style='background-color:#ffffff; border-radius:12px;
              overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.12);
              max-width:600px; width:100%;'>

<!-- ═══════════ HEADER AZUL CORPORATIVO ═══════════ -->
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
             text-shadow:0 2px 8px rgba(0,0,0,0.2);'>
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

<!-- ═══════════ CUERPO ═══════════ -->
<tr>
<td style='padding:36px 40px 30px 40px;'>

<p style='margin:0 0 8px 0; font-size:18px;
          font-weight:700; color:#1a2d4e;'>
Hola, {$usuario['NombreFuncionario']}
</p>

<p style='margin:0 0 24px 0; font-size:14px;
          color:#555; line-height:1.6;'>
Hemos recibido una solicitud para restablecer tu contraseña
en el sistema <strong>SEGTRACK</strong>.
</p>

<!-- CAJA TOKEN ESTILO PREMIUM -->
<table width='100%' cellpadding='0' cellspacing='0'
       style='border-left:4px solid #2979e0;
              background-color:#f5f9ff;
              border-radius:0 8px 8px 0;
              margin-bottom:26px;
              box-shadow:0 2px 8px rgba(41,121,224,0.07);'>
<tr>
<td style='padding:22px 20px; text-align:center;'>

<p style='margin:0 0 10px 0;
          font-size:13px;
          font-weight:700;
          color:#1a5fc8;
          text-transform:uppercase;
          letter-spacing:1px;'>
🔐 Código de Verificación
</p>

<div style='font-size:38px;
            font-weight:900;
            letter-spacing:6px;
            color:#1a5fc8;
            margin:10px 0;'>
{$token}
</div>

<p style='margin:10px 0 0 0;
          font-size:13px;
          color:#444;'>
⏳ Este código es válido por <strong>15 minutos</strong>.
</p>

</td>
</tr>
</table>

<p style='font-size:13px; color:#666; line-height:1.6;'>
Si no solicitaste este cambio, puedes ignorar este mensaje.
Tu cuenta permanecerá segura.
</p>

</td>
</tr>

</table>

<p style='margin:18px 0 0 0; font-size:11px;
          color:#aaa; text-align:center;'>
© " . date('Y') . " SEGTRACK - Sistema de Seguridad Integral<br>
Este correo fue generado automáticamente. Por favor no respondas a este mensaje.
</p>

</td>
</tr>
</table>

</body>
</html>
";

$mail->AltBody = "Hola {$usuario['NombreFuncionario']}, tu token de recuperación es: {$token}. Válido por 15 minutos.";

// Enviar correo
$mail->send();

 echo json_encode([
'success' => true,
'message' => 'Token enviado correctamente a tu correo electrónico. Revisa tu bandeja de entrada.'
 ]);

 } catch (Exception $e) {
// Si ocurre un error de envío (como el error SSL), loguea el detalle
 error_log("PHPMailer Error: " . $mail->ErrorInfo); 

 echo json_encode([
'success' => false,
'message' => 'Error al enviar el correo. Por favor, verifica tu conexión y la configuración SMTP. Detalle: ' . $mail->ErrorInfo
]);
}

} catch (PDOException $e) {
 error_log("PDO Error: " . $e->getMessage()); 

echo json_encode([
'success' => false,
'message' => 'Error en la base de datos. Contacta al administrador.'
]);
}
?>