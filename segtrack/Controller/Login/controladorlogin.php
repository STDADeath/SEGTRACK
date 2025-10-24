<?php
require_once __DIR__ . "/../../model/Login/modulousuario.php";

class ControladorLogin {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModuloUsuario();
    }

    public function login($correo, $contrasena) {
        try {
            $usuario = $this->modelo->validarLogin($correo, $contrasena);

            if ($usuario) {
                session_start();
                $_SESSION["nombre"] = $usuario["NombreFuncionario"];
                $_SESSION["rol"] = $usuario["TipoRol"];

                switch ($usuario["TipoRol"]) {
                    case "Supervisor":
                        header("Location: ../../View/Funcionario.php");
                        break;
                    case "Personal_Seguridad":
                        header("Location: ../../View/RegistroFun.html");
                        break;
                    case "Administrador":
                        header("Location: ../../View/index.html");
                        break;
                    default:
                        header("Location: ../../View/login.html");
                        break;
                }
                exit;
            } else {
                throw new Exception("Credenciales incorrectas");
            }
        } catch (Exception $e) {
            echo "<script>
                    alert('❌ " . $e->getMessage() . "');
                    window.location.href='../../View/login.html';
                  </script>";
        }
    }
}

// ======================================================
// ✅ EJECUCIÓN (cuando viene del formulario POST)
// ======================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST["correo"] ?? "";
    $contrasena = $_POST["contrasena"] ?? "";

    $controlador = new ControladorLogin();
    $controlador->login($correo, $contrasena);
}
?>
