<?php
require_once __DIR__ . "/../../model/Login/modulousuario.php";

class ControladorLogin {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModuloUsuario();
    }

    public function login($correo, $contrasena) {
        try {
            $resultado = $this->modelo->validarLogin($correo, $contrasena);

            // Si el modelo devolvió un arreglo con ok = true => login correcto
            if (is_array($resultado) && isset($resultado['ok']) && $resultado['ok'] === true) {
                $usuario = $resultado['usuario'];
                session_start();
                $_SESSION["nombre"] = $usuario["NombreFuncionario"];
                $_SESSION["rol"] = $usuario["TipoRol"];

                // Redirigir según rol (ajusta rutas si es necesario)
                switch ($usuario["TipoRol"]) {
                    case "Supervisor":
                        header("Location: ../../View/Funcionariopanel.php");
                        break;
                    case "Personal_Seguridad":
                        header("Location: ../../View/Funcionario.php");
                        break;
                    case "Administrador":
                        header("Location: ../../View/index.html");
                        break;
                    default:
                        header("Location: ../../View/index.html");
                        break;
                }
                exit;
            }

            // Si no fue ok, mostramos depuración (temporal) y redirigimos al login
            if (is_array($resultado)) {
                // Mensajes específicos para saber qué pasa
                if ($resultado['reason'] === 'no_user') {
                    echo "<script>alert('❌ No existe usuario con ese correo o documento.'); window.location.href='../../View/login.html';</script>";
                    exit;
                }
                if ($resultado['reason'] === 'bad_password') {
                    // Muestra mensaje claro (temporal): contraseña incorrecta
                    // Puedes quitar 'stored' y 'given' cuando ya esté solucionado.
                    $stored = htmlspecialchars($resultado['stored']);
                    // no muestres $given en producción por seguridad
                    echo "<script>
                            alert('❌ Contraseña incorrecta.');
                            window.location.href='../../View/login.html';
                          </script>";
                    exit;
                }
                if ($resultado['reason'] === 'exception') {
                    $msg = addslashes($resultado['message']);
                    echo "<script>alert('❌ Error: {$msg}'); window.location.href='../../View/login.html';</script>";
                    exit;
                }
            }

            // Por defecto (seguridad)
            echo "<script>alert('❌ Credenciales incorrectas'); window.location.href='../../View/login.html';</script>";
            exit;

        } catch (Exception $e) {
            $msg = addslashes($e->getMessage());
            echo "<script>alert('❌ Excepción: {$msg}'); window.location.href='../../View/login.html';</script>";
            exit;
        }
    }
}

// EJECUCIÓN al venir POST desde el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");

    $controlador = new ControladorLogin();
    $controlador->login($correo, $contrasena);
}
?>
