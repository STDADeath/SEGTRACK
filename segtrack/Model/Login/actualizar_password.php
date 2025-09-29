<?php
header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de que esta ruta sea correcta

$response = ['success' => false, 'message' => ''];
const LIMITE_CAMBIOS = 3;

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idFuncionario = $data['idFuncionario'] ?? null;
    $newPassword = $data['newPassword'] ?? '';

    if (empty($idFuncionario) || empty($newPassword)) {
        throw new Exception('Datos incompletos.');
    }

    // 1. Obtener el número de intentos y la fecha del último cambio
    $sql_check = "SELECT intentos_cambio_pass, ultimo_cambio_pass FROM usuario WHERE IdFuncionario = :id";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute(['id' => $idFuncionario]);
    $usuario = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado.');
    }

    $intentos = $usuario['intentos_cambio_pass'];
    $ultimo_cambio = $usuario['ultimo_cambio_pass'];
    $mes_actual = date('Y-m');
    $mes_ultimo_cambio = date('Y-m', strtotime($ultimo_cambio));

    // 2. Lógica para resetear o incrementar el contador
    if ($mes_actual !== $mes_ultimo_cambio) {
        // Reiniciar el contador si el cambio es en un nuevo mes
        $intentos = 1;
    } else {
        // Incrementar el contador si el cambio es en el mismo mes
        $intentos++;
        if ($intentos > LIMITE_CAMBIOS) {
            throw new Exception('Ha excedido el límite de 3 cambios de contraseña este mes.');
        }
    }
    
    // 3. Hashear la nueva contraseña y actualizar
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $currentDate = date('Y-m-d');

    $sql_update = "UPDATE usuario 
                   SET Contrasena = :newPassword, 
                       intentos_cambio_pass = :intentos,
                       ultimo_cambio_pass = :fecha 
                   WHERE IdFuncionario = :id";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->execute([
        'newPassword' => $hashedPassword,
        'intentos' => $intentos,
        'fecha' => $currentDate,
        'id' => $idFuncionario
    ]);

    $response['success'] = true;
    $response['message'] = 'Contraseña actualizada exitosamente.';

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>