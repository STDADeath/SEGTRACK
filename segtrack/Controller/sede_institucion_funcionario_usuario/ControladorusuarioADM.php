ControladorusuarioADM.php


<?php
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modelousuarioAdm.php';


class ControladorUsuario {
    private $modelo;

    public function __construct() {
        $this->modelo = new Modelo_Usuario();
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipoRol = $_POST['tipo_rol'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $idFuncionario = $_POST['id_funcionario'] ?? '';

            if (!empty($tipoRol) && !empty($contrasena) && !empty($idFuncionario)) {
                try {
                    // ✅ VALIDAR SI EL FUNCIONARIO YA TIENE UN USUARIO REGISTRADO
                    if ($this->modelo->usuarioExiste($idFuncionario)) {
                        echo "<script>
                                alert('❌ Usuario ya existente');
                                window.history.back();
                              </script>";
                        exit;
                    }

                    $resultado = $this->modelo->registrarUsuario($tipoRol, $contrasena, $idFuncionario);

                    if ($resultado) {
                        echo "<script>alert('✅ Usuario registrado correctamente'); 
                              window.location.href='../../View/Sede.php';</script>";
                    } else {
                        echo "<script>alert('❌ No se pudo registrar el usuario'); 
                              window.history.back();</script>";
                    }
                } catch (Exception $e) {
                    echo "<script>alert('⚠️ Error: " . $e->getMessage() . "'); 
                          window.history.back();</script>";
                }
            } else {
                echo "<script>alert('⚠️ Todos los campos son obligatorios'); 
                      window.history.back();</script>";
            }
        }
    }
}

// Instancia del controlador
$controlador = new ControladorUsuario();
$controlador->registrar();