<?php
$data = array(
    'correo' => 'test@test.com',
    'contrasena' => '123456'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '../../../App/Controller/controladorlogin.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
?>