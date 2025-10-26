<?php
require_once __DIR__ . '/../../model/sede_institucion_funcionario_usuario/modeloinstituto.php';

class ControladorInstituto {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloInstituto();
    }

    public function manejarSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['NombreInstitucion'] ?? '');
            $tipo   = trim($_POST['TipoInstitucion'] ?? '');
            $estado = trim($_POST['EstadoInstitucion'] ?? '');

            if ($nombre === '' || $tipo === '' || $estado === '') {
                echo "Error: Todos los campos son obligatorios.";
            } else {
                // Generar el NIT en el controlador
                $nit = $this->modelo->generarNit();

                $datos = [
                    'NombreInstitucion' => $nombre,
                    'TipoInstitucion'   => $tipo,
                    'EstadoInstitucion' => $estado,
                    'Nit_Codigo' => $nit // Agregar el NIT a los datos
                ];
                $resultado = $this->modelo->insertarInstituto($datos);

                if ($resultado['error']) {
                    echo "Error al registrar: " . $resultado['mensaje'];
                } else {
                    echo "Institución registrada correctamente. NIT: " . $nit; // Mostrar el NIT
                }
            }
        } else {
            echo "Error: Solicitud no válida.";
        }
    }
}

$controlador = new ControladorInstituto();
$controlador->manejarSolicitud();
?>