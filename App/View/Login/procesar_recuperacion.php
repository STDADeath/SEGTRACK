<?php
header('Content-Type: application/json');

// Incluir PHPMailer
// NOTA: Verifica que estas rutas sean correctas. Basado en tu imagen,
// la ruta relativa parece ser correcta: ../../../PHPMailer/src/
require '../../../PHPMailer/src/Exception.php';
require '../../../PHPMailer/src/PHPMailer.php';
require '../../../PHPMailer/src/SMTP.php';

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
$mail->Password = 'bnin xnai egbo yris';
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
<html>
<head>
<style>
body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
.container { max-width: 600px; margin: 0 auto; padding: 20px; }
.header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
.content { padding: 20px; background-color: #f9f9f9; }
.token { font-size: 32px; font-weight: bold; color: #4CAF50; text-align: center; padding: 20px; background-color: white; border: 2px dashed #4CAF50; margin: 20px 0; }
.footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
</style>
</head>
<body>
<div class='container'>
<div class='header'>
<h1>Recuperación de Contraseña</h1>
</div>
<div class='content'>
<p>Hola <strong>{$usuario['NombreFuncionario']}</strong>,</p>
<p>Has solicitado recuperar tu contraseña en Segtrack.</p>
<p>Tu token de verificación es:</p>
<div class='token'>{$token}</div>
<p><strong>Este token es válido por 15 minutos.</strong></p>
<p>Si no solicitaste este cambio, ignora este correo.</p>
</div>
<div class='footer'>
<p>&copy; 2024 Segtrack. Todos los derechos reservados.</p>
</div>
</div>
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