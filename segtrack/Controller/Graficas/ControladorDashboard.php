<?php
// =============================
// ControladorDashboard.php
// =============================

// Ruta de conexión
$ruta_conexion = __DIR__ . '/../../core/conexion.php';

// Validar que exista el archivo de conexión
if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Error: Archivo de conexión no encontrado en: ' . $ruta_conexion
    ]));
}

// ✅ Nombre del modelo corregido
require_once __DIR__ . "/../../Model/Graficas/ModeloDashboard.php";

class ControladorDashboard {

    private $model;

    public function __construct($conexion) {
        $this->model = new ModeloDashboard($conexion);
        header('Content-Type: application/json; charset=utf-8');
    }

    public function manejarSolicitud($accion) {
        switch ($accion) {
            case 'tipos_dispositivos':
                echo json_encode($this->model->DispositivosPorTipo());
                break;

            case 'total_dispositivos':
                echo json_encode([
                    "total_dispositivos" => $this->model->DispositivosTotal()
                ]);
                break;

            case 'total_funcionarios':
                echo json_encode([
                    "total_funcionarios" => $this->model->FuncionariosTotal()
                ]);
                break;

            case 'total_visitantes':
                echo json_encode([
                    "total_visitantes" => $this->model->TotalVisitante()
                ]);
                break;

            default:
                echo json_encode(["error" => "Acción no válida"]);
                break;
        }
    }
}

// ✅ Este bloque ejecuta el controlador cuando se accede vía AJAX
if (isset($_GET['accion'])) {
    $dashboard = new ControladorDashboard($conexion);
    $dashboard->manejarSolicitud($_GET['accion']);
}
?>
