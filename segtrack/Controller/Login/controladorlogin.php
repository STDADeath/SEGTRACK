<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../../Model/Login/modulousuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        $nuevoRol = trim($_POST['rol'] ?? ''); // opcional

        if ($correo === '' || $contrasena === '') {
            throw new Exception('Por favor llena todos los campos.');
        }

        $usuarioModel = new ModuloUsuario();
        $resultado = $usuarioModel->validarLogin($correo, $contrasena);

        if (!$resultado['ok']) {
            // Si es contraseña incorrecta o usuario no existe
            throw new Exception($resultado['message']);
        }

        // Actualizar rol si se envió y es diferente al actual
        if ($nuevoRol !== '' && $nuevoRol !== $resultado['usuario']['TipoRol']) {
            $usuarioModel->actualizarRol($resultado['usuario']['IdFuncionario'], $nuevoRol);
            $resultado['usuario']['TipoRol'] = $nuevoRol;
        }

        // Guardar en sesión
        $_SESSION['usuario'] = $resultado['usuario'];

        // Redirección según rol
        switch ($resultado['usuario']['TipoRol']) {
            case 'Administrador':
                $ruta = '../../View/Instituto.php';
                break;
            case 'Supervisor':
                $ruta = '../../View/Sede.php';
                break;
            case 'Personal Seguridad':
                $ruta = '../../View/Instituto.php';
                break;
            default:
                $ruta = '../../View/login.html'; // por defecto
                break;
        }

        // Alerta de éxito y redirección
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
Swal.fire({
    icon:'success',
    title:'Bienvenido',
    text:'{$resultado['usuario']['NombreFuncionario']}',
    allowOutsideClick: false
}).then(() => {
    window.location.href = '$ruta';
});
</script>
</body>
</html>";
        exit;

    } catch (Exception $e) {
        // Alerta de error y volver al login
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
Swal.fire({
    icon:'error',
    title:'Error',
    text:'{$e->getMessage()}',
    allowOutsideClick: false
}).then(() => {
    window.location.href = '../../View/login.html';
});
</script>
</body>
</html>";
        exit;
    }
}

?>

