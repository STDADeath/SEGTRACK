<<<<<<< HEAD
<?php
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modelofuncionario.php';

class ControladorFuncionario {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloFuncionario();
    }

    public function manejarSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'insertar';
            $datos = [
                'NombreFuncionario'       => $_POST['NombreFuncionario'] ?? '',
                'DocumentoFuncionario'    => $_POST['DocumentoFuncionario'] ?? '',
                'CorreoFuncionario'       => $_POST['CorreoFuncionario'] ?? '',
                'TelefonoFuncionario'     => $_POST['TelefonoFuncionario'] ?? '',
                'CargoFuncionario'        => $_POST['CargoFuncionario'] ?? '',
                'IdSede'                  => $_POST['IdSede'] ?? ''
            ];

            switch ($accion) {
                case 'insertar':
                    $resultado = $this->modelo->insertarFuncionario($datos);
                    break;
                case 'actualizar':
                    $id = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->actualizarFuncionario($id, $datos);
                    break;
                case 'eliminar':
                    $id = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->eliminarFuncionario($id);
                    break;
                case 'sincronizarCargo':
                    $idFuncionario = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->actualizarCargoSegunRol($idFuncionario);
                    break;
                default:
                    $resultado = ['error' => '⚠️ Acción no reconocida.'];
            }

            $this->mostrarAlerta($resultado);
        }
    }

    private function mostrarAlerta($resultado) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        $icon = isset($resultado['error']) ? 'error' : 'success';
        $titulo = isset($resultado['error']) ? 'Error' : 'Éxito';
        $mensaje = isset($resultado['error']) ? $resultado['error'] : $resultado['mensaje'];
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$titulo',
                html: '$mensaje',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            }).then(() => { window.location.reload(); });
        </script>";
    }
}

// Autoejecución
$controlador = new ControladorFuncionario();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->manejarSolicitud();
}
?>
=======
>>>>>>> 5117bf3459d7c75113b2c6c82144a473bf2194c3
