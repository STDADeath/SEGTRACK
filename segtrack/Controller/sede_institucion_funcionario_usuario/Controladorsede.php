<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modelosede.php';

class ControladorSede {
    private $modeloSede;

    public function __construct() {
        $this->modeloSede = new Modelo_Sede();
    }

    public function manejarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $tipo_sede     = trim($_POST['tipo_sede'] ?? '');
            $ciudad_sede   = trim($_POST['ciudad_sede'] ?? '');
            $id_institucion = trim($_POST['id_institucion'] ?? '');
            $estado_sede   = trim($_POST['estado_sede'] ?? '');

            // Validación básica
            if (empty($tipo_sede) || empty($ciudad_sede) || empty($id_institucion) || empty($estado_sede)) {
                $this->mostrarAlerta('warning', 'Datos incompletos', 'Por favor, completa todos los campos antes de continuar.');
                return;
            }

            $resultado = $this->modeloSede->registrarSede($tipo_sede, $ciudad_sede, $id_institucion, $estado_sede);

            if ($resultado['error']) {
                // Error en la inserción
                $this->mostrarAlerta('error', 'Error al registrar', $resultado['mensaje']);
            } else {
                // Éxito
                $this->mostrarAlerta('success', '¡Sede registrada!', 'La sede fue registrada correctamente.');
            }

        } else {
            $this->mostrarAlerta('error', 'Acceso no permitido', 'Solo se permiten solicitudes POST.');
        }
    }

    /**
     * Muestra una alerta SweetAlert2 personalizada.
     * Luego redirige de nuevo al formulario Sede.php
     */
    private function mostrarAlerta($icono, $titulo, $mensaje) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$icono',
                    title: '$titulo',
                    text: '$mensaje',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../View/Sede.php';
                    }
                });
            });
        </script>";
    }
}

$controlador = new ControladorSede();
$controlador->manejarRegistro();
?>
