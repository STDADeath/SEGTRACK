<?php
require_once __DIR__ . '/../../model/sede_institucion_funcionario_usuario/modeloFuncionariopanel.php';

class Funcionario {
    private $modelo;

    public function __construct() {
        $this->modelo = new FuncionarioModel();
    }

    public function procesarFormulario() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $datos = [
                    'NombreFuncionario' => $_POST['NombreFuncionario'],
                    'DocumentoFuncionario' => $_POST['DocumentoFuncionario'],
                    'TelefonoFuncionario' => $_POST['TelefonoFuncionario'],
                    'CorreoFuncionario' => $_POST['CorreoFuncionario'],
                    'CargoFuncionario' => $_POST['CargoFuncionario'],
                    'SedeFuncionario' => $_POST['SedeFuncionario']
                ];

                $resultado = $this->modelo->agregarFuncionario($datos);

                if ($resultado) {
                    echo "<script>
                        alert('✅ Funcionario registrado correctamente');
                        window.location.href='../../View/FuncionarioLista.php';
                    </script>";
                } else {
                    echo "<script>
                        alert('❌ Error al registrar el funcionario');
                        window.history.back();
                    </script>";
                }
            }
        } catch (Exception $e) {
            echo "<script>
                alert('⚠️ Error inesperado: " . $e->getMessage() . "');
                window.history.back();
            </script>";
        }
    }
}

$funcionario = new Funcionario();
$funcionario->procesarFormulario();
?>
