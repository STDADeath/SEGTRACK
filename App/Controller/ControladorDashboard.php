<?php
$ruta_conexion = __DIR__ . '../core/conexion.php';

if (file_exists($ruta_conexion)) {
    require_once $ruta_conexion;
} else {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Error: Archivo de conexión no encontrado.'
    ]));
}

require_once __DIR__ . "/../../Model/Graficas/ModeloDashboard.php";

class ControladorDashboard {

    private $model;

    public function __construct($conexion) {
        $this->model = new ModeloDashboard($conexion);
        header('Content-Type: application/json; charset=utf-8');
    }

    public function manejarSolicitud($accion) {
        switch ($accion) {

            // =============================
            // 📊 GRÁFICAS
            // =============================
            case 'tipos_dispositivos':
                echo json_encode($this->model->DispositivosPorTipo());
                break;

            case 'vehiculos_por_tipo':
                echo json_encode($this->model->VehiculosPorTipo());
                break;

            case 'vehiculos_por_sede':
                echo json_encode($this->model->VehiculosPorSede());
                break;

            case 'dotacion_por_tipo':
                echo json_encode($this->model->DotacionPorTipo());
                break;

            case 'dotacion_por_estado':
                echo json_encode($this->model->DotacionPorEstado());
                break;

            case 'dotaciones_por_mes':
                echo json_encode($this->model->DotacionesPorMes());
                break;

            case 'dotaciones_por_devolucion':
                echo json_encode($this->model->DotacionesPorDevolucionMes());
                break;

            case 'funcionarios_por_cargo':
                echo json_encode($this->model->FuncionariosPorCargo());
                break;

            case 'funcionarios_por_sede':
                echo json_encode($this->model->FuncionariosPorSede());
                break;

            case 'visitantes_por_mes':
                echo json_encode($this->model->VisitantesPorMes());
                break;

            case 'ingresos_por_tipo':
                echo json_encode($this->model->IngresosPorTipo());
                break;

            // =============================
            // 📖 BITÁCORA
            // =============================
            case 'total_bitacora':
                echo json_encode([
                    "total_bitacora" => $this->model->TotalBitacora()
                ]);
                break;

            case 'bitacora_por_turno':
                echo json_encode($this->model->BitacoraPorTurno());
                break;

            case 'bitacora_por_mes':
                echo json_encode($this->model->BitacoraPorMes());
                break;

            // =============================
            // 🔢 TOTALES
            // =============================
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

            case 'total_vehiculos':
                echo json_encode([
                    "total_vehiculos" => $this->model->ParqueaderoTotal()
                ]);
                break;

            case 'total_dotacion':
                echo json_encode([
                    "total_dotacion" => $this->model->DotacionTotal()
                ]);
                break;

            default:
                echo json_encode(["error" => "Acción no válida"]);
                break;
        }
    }
}

if (isset($_GET['accion'])) {
    $dashboard = new ControladorDashboard($conexion);
    $dashboard->manejarSolicitud($_GET['accion']);
}
?>