<?php
// ══════════════════════════════════════════════════════════════════
//  ControladorDashboard.php — SEGTRACK
//  Ruta: App/Controller/ControladorDashboard.php
//
//  ✅ Funciona para: Personal Seguridad, Supervisor, Administrador
//  ⚠️  Solo se agregaron cases nuevos al final del switch.
//      Todo lo demás es idéntico al que ya funciona.
// ══════════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$ruta_conexion = __DIR__ . '/../Core/conexion.php';

if (!file_exists($ruta_conexion)) {
    die(json_encode([
        'success'      => false,
        'message'      => 'Archivo de conexión no encontrado.',
        'ruta_buscada' => $ruta_conexion
    ]));
}

require_once $ruta_conexion;

// ⚠️ Ruta exacta que ya tenías funcionando
require_once __DIR__ . '/../Model/ModeloDashboard.php';

// ══════════════════════════════════════════════════════════════════
class ControladorDashboard {

    private ModeloDashboard $model;

    public function __construct($conexion) {
        $this->model = new ModeloDashboard($conexion);
    }

    public function manejarSolicitud(string $accion): void {
        switch ($accion) {

            // ── TOTALES ───────────────────────────────────────────
            case 'total_funcionarios':
                echo json_encode(['total_funcionarios' => $this->model->FuncionariosTotal()]);
                break;
            case 'total_visitantes':
                echo json_encode(['total_visitantes' => $this->model->TotalVisitante()]);
                break;
            case 'total_vehiculos':
                echo json_encode(['total_vehiculos' => $this->model->ParqueaderoTotal()]);
                break;
            case 'total_dispositivos':
                echo json_encode(['total_dispositivos' => $this->model->DispositivosTotal()]);
                break;
            case 'total_dotacion':
                echo json_encode(['total_dotacion' => $this->model->DotacionTotal()]);
                break;
            case 'total_bitacora':
                echo json_encode(['total_bitacora' => $this->model->TotalBitacora()]);
                break;

            // ── NUEVOS TOTALES (Admin) ────────────────────────────
            case 'total_sedes':
                echo json_encode(['total_sedes' => $this->model->TotalSedes()]);
                break;
            case 'total_institutos':
                echo json_encode(['total_institutos' => $this->model->TotalInstituciones()]);
                break;

            // ── DISPOSITIVOS ──────────────────────────────────────
            case 'tipos_dispositivos':
                echo json_encode($this->model->DispositivosPorTipo());
                break;

            // ── VEHÍCULOS ─────────────────────────────────────────
            case 'vehiculos_por_tipo':
                echo json_encode($this->model->VehiculosPorTipo());
                break;
            case 'vehiculos_por_sede':
                echo json_encode($this->model->VehiculosPorSede());
                break;

            // ── FUNCIONARIOS ──────────────────────────────────────
            case 'funcionarios_por_cargo':
                echo json_encode($this->model->FuncionariosPorCargo());
                break;
            case 'funcionarios_por_sede':
                echo json_encode($this->model->FuncionariosPorSede());
                break;

            // ── INGRESOS ──────────────────────────────────────────
            case 'visitantes_por_mes':
                echo json_encode($this->model->VisitantesPorMes());
                break;
            case 'ingresos_por_tipo':
                echo json_encode($this->model->IngresosPorTipo());
                break;

            // ── DOTACIÓN ──────────────────────────────────────────
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

            // ── BITÁCORA ──────────────────────────────────────────
            case 'bitacora_por_turno':
                echo json_encode($this->model->BitacoraPorTurno());
                break;
            case 'bitacora_por_mes':
                echo json_encode($this->model->BitacoraPorMes());
                break;

            // ── SEDES (Admin) ─────────────────────────────────────
            case 'sedes_por_ciudad':
                echo json_encode($this->model->SedesPorCiudad());
                break;
            case 'sedes_por_institucion':
                echo json_encode($this->model->SedesPorInstitucion());
                break;
            case 'todas_las_sedes':
                echo json_encode($this->model->TodasLasSedes());
                break;

            // ── INSTITUCIONES (Admin) ─────────────────────────────
            case 'instituciones_por_tipo':
                echo json_encode($this->model->InstitucionesPorTipo());
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => "Acción no válida: '$accion'"]);
                break;
        }
    }
}

if (isset($_GET['accion'])) {
    $dashboard = new ControladorDashboard($conexion);
    $dashboard->manejarSolicitud(trim($_GET['accion']));
}