<?php
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modeloinstituto.php';

class ControladorInstituto {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloInstituto();
    }

    public function manejarSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['NombreInstitucion'] ?? '');
            $nit    = trim($_POST['Nit_Codigo'] ?? '');
            $tipo   = trim($_POST['TipoInstitucion'] ?? '');
            $estado = trim($_POST['EstadoInstitucion'] ?? '');

            if ($nombre === '' || $nit === '' || $tipo === '' || $estado === '') {
                echo "❌ Error: Todos los campos son obligatorios.";
                return;
            }

            // Enviar los datos al modelo
            $datos = [
                'NombreInstitucion' => $nombre,
                'Nit_Codigo'        => $nit,
                'TipoInstitucion'   => $tipo,
                'EstadoInstitucion' => $estado
            ];

            $resultado = $this->modelo->insertarInstituto($datos);

            if ($resultado['error']) {
                echo $resultado['mensaje'];
            } else {
                echo $resultado['mensaje'];
            }
        } else {
            echo "⚠️ Error: Solicitud no válida.";
        }
    }
}

$controlador = new ControladorInstituto();
$controlador->manejarSolicitud();
?>