<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Prueba de Envío de Email</h2>";

// Incluir PHPMailer
require '../../../PHPMailer/src/Exception.php';
require '../../../PHPMailer/src/PHPMailer.php';
require '../../../PHPMailer/src/SMTP.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seguridad.integral.segtrack@gmail.com';
    $mail->Password = 'AQUI_TU_CONTRASEÑA_DE_16_CARACTERES'; // ⚠️ Cámbiala
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->SMTPDebug = 2; // Ver detalles del error
    
    // Configuración del correo
    $mail->setFrom('seguridad.integral.segtrack@gmail.com', 'Segtrack Test');
    $mail->addAddress('TU_CORREO_PERSONAL@gmail.com'); // ⚠️ Pon tu correo aquí
    
    // Contenido
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Prueba de Segtrack';
    $mail->Body = '<h1>Prueba exitosa</h1><p>Si recibes este correo, PHPMailer funciona correctamente.</p>';
    
    $mail->send();
    echo '<br><br><strong style="color: green;">✅ Correo enviado exitosamente!</strong>';
    
} catch (Exception $e) {
    echo "<br><br><strong style='color: red;'>❌ Error al enviar:</strong> {$mail->ErrorInfo}";
}
?>
```

### **Importante en test_email.php:**
- **Línea 22:** Pon tu contraseña de aplicación de Gmail (los 16 caracteres sin espacios)
- **Línea 28:** Pon tu correo personal donde quieres recibir la prueba

### **Accede a:**
```
http://localhost/SEGTRACK/App/View/Login/test_email.php