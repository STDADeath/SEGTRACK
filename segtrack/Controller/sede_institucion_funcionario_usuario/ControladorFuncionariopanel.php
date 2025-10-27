<?php
require_once __DIR__ . '/../../model/sede_institucion_funcionario_usuario/modeloFuncionariopanel.php';

class FuncionariopanelController {
    private $modelo;

    public function __construct() {
        $this->modelo = new modeloFuncionariopanel(); // Instancia correcta del modelo
    }

    public function manejarSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Captura de datos del formulario
            $datos = [
                'NombreFuncionario'   => trim($_POST['NombreFuncionario'] ?? ''),
                'DocumentoFuncionario'=> trim($_POST['DocumentoFuncionario'] ?? ''),
                'TelefonoFuncionario' => trim($_POST['TelefonoFuncionario'] ?? ''),
                'CorreoFuncionario'   => trim($_POST['CorreoFuncionario'] ?? ''),
                'CargoFuncionario'    => trim($_POST['CargoFuncionario'] ?? ''),
                'IdSede'              => trim($_POST['IdSede'] ?? '')
            ];

            // Validación simple de campos vacíos
            foreach ($datos as $campo => $valor) {
                if (empty($valor)) {
                    $this->mostrarAlerta(['error' => true, 'mensaje' => "❌ El campo $campo es obligatorio."]);
                    return;
                }
            }

            // Inserta en la base de datos
            $resultado = $this->modelo->insertarFuncionario($datos);
            $this->mostrarAlerta($resultado);
        }
    }

    // Función para mostrar alerta con SweetAlert2
    private function mostrarAlerta($resultado) {
        $icon = $resultado['error'] ? 'error' : 'success';
        $mensaje = $resultado['mensaje'];

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: 'Resultado',
                html: `$mensaje`,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = '../Models/FuncionarioLista.php';
            });
        </script>";
        exit;
    }
}

// Ejecutar controlador
$controlador = new FuncionariopanelController();
$controlador->manejarSolicitud();
?>
