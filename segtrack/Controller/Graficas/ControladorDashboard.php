<?php

// Ruta de conexión
$ruta_conexion = __DIR__ . '/../../core/conexion.php';

// Verifica que exista el archivo de conexión
if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Error: Archivo de conexión no encontrado en: ' . $ruta_conexion
    ]));
}

// Modelo
require_once __DIR__ . "/../../Model/Graficas/ModeloDasboard.php";

class ControladorDashboard {

    private $model;

    public function __construct($conexion) {
        // Inicializa el modelo correctamente
        $this->model = new ModeloDashboard($conexion);
        header('Content-Type: application/json; charset=utf-8');
    }

    public function manejarSolicitud($accion) {
        switch ($accion) {
            case 'tipos_dispositivos':
                echo json_encode($this->model->dispositivosPorTipo());
                break;

            case 'total_dispositivos':
                echo json_encode(["total_dispositivos" => $this->model->DispositivosTotal()]);
                break;

            case 'total_funcionarios':
                echo json_encode(["total_funcionarios" => $this->model->FuncionariosTotal()]);
                break;

            case 'total_visitantes':
                echo json_encode(["total_visitantes" => $this->model->TotalVisitante()]);
                break;

            default:
                echo json_encode(["error" => "Acción no válida"]);
                break;
        }
    }

    // Método estático para iniciar el controlador desde la URL
    public static function iniciar($conexion) {
        $accion = $_GET['accion'] ?? '';
        $controlador = new self($conexion);
        $controlador->manejarSolicitud($accion);
    }
}

// Ejecutar controlador pasando la conexión directamente
ControladorDashboard::iniciar($conexion);

?>
