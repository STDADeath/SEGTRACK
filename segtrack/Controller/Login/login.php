<?php
require_once "../../model/Usuario.php";
require_once "../../Controller/Conexion/conexion.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"]);
    $contrasena = trim($_POST["contrasena"]);

    $usuario = new Usuario();
    $datos = $usuario->validarLogin($correo, $contrasena);

    if ($datos) {
        $_SESSION["nombre"] = $datos["NombreFuncionario"];
        $_SESSION["rol"] = $datos["TipoRol"];

        // Redirección según rol
        switch ($datos["TipoRol"]) {
            case "Supervisor":
                echo "<script>
                        alert('Bienvenido Supervisor: ".$_SESSION["nombre"]."');
                        window.location.href='../../model/Dispositivos.php';
                      </script>";
                break;

            case "Personal_Seguridad":
                echo "<script>
                        alert('Bienvenido Personal de Seguridad: ".$_SESSION["nombre"]."');
                        window.location.href='../../View/RegistroFun.html';
                      </script>";
                break;

            case "Administrador":
                echo "<script>
                        alert('Bienvenido Administrador: ".$_SESSION["nombre"]."');
                        window.location.href='../../View/index.html';
                      </script>";
                break;

            default:
                echo "<script>
                        alert('Rol no válido o no asignado.');
                        window.location.href='../../View/login.html';
                      </script>";
                break;
        }
        exit;
    } else {
        echo "<script>
                alert('❌ Credenciales incorrectas. Verifica tu correo o contraseña.');
                window.location.href='../../View/login.html';
              </script>";
        exit;
    }
}
?>
