<?php
session_start();
require_once "../../model/usuario.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($correo) || empty($contrasena)) {
        $_SESSION['error'] = "Por favor completa todos los campos.";
        header("Location: ../vistas/login.php");
        exit;
    }

    $usuarioModel = new Usuario();
    $resultado = $usuarioModel->validarLogin($correo, $contrasena);

    if ($resultado['success']) {
        $usuario = $resultado['usuario'];

        $_SESSION['usuario'] = [
            'id' => $usuario['IdUsuario'],
            'nombre' => $usuario['NombreFuncionario'],
            'rol' => $usuario['TipoRol'],
            'correo' => $usuario['CorreoFuncionario']
        ];

        // 🔁 Redirección por rol
        switch ($usuario['TipoRol']) {
            case 'Supervisor':
                header("Location: ../vistas/dashboard_supervisor.php");
                break;
            case 'Seguridad':
                header("Location: ../vistas/dashboard_seguridad.php");
                break;
            case 'Personal':
                header("Location: ../vistas/dashboard_personal.php");
                break;
            case 'Admin':
                header("Location: ../vistas/dashboard_admin.php");
                break;
            default:
                header("Location: ../vistas/login.php");
        }
        exit;
    } else {
        $_SESSION['error'] = $resultado['mensaje'];
        header("Location: ../vistas/login.php");
        exit;
    }
}
?>