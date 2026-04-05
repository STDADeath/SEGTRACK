<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloDotacion.php";

// ══════════════════════════════════════════════════════════
// ZONA HORARIA: Colombia (UTC-5)
// ══════════════════════════════════════════════════════════
date_default_timezone_set('America/Bogota');

class ControladorDotacion {

    private DotacionModelo $modelo;

    private const MARGEN_MINUTOS = 10;

    public function __construct($conexion) {
        $this->modelo = new DotacionModelo($conexion);
    }

    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    private function parsearFecha(string $fecha): ?DateTime {
        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $formato) {
            $d = DateTime::createFromFormat($formato, $fecha);
            if ($d !== false) return $d;
        }
        return null;
    }

    private function convertirFecha(array &$datos, array $campos): array {
        foreach ($campos as $campo) {
            if (empty($datos[$campo])) continue;
            $d = $this->parsearFecha($datos[$campo]);
            if ($d === null) {
                return ['success' => false, 'message' => "Formato de fecha inválido en $campo: " . $datos[$campo]];
            }
            $datos[$campo] = $d->format('Y-m-d H:i:s');
        }
        return ['success' => true];
    }

    private function limiteInferior(): DateTime {
        $limite = new DateTime();
        $limite->modify('-' . self::MARGEN_MINUTOS . ' minutes');
        return $limite;
    }

    // ══════════════════════════════════════════════
    // ROL DE SESIÓN ACTIVO
    // Lee TipoRol desde $_SESSION
    // ══════════════════════════════════════════════
    private function getRolSesion(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['TipoRol'] ?? '';
    }

    private function esPersonalSeguridad(): bool {
        return $this->getRolSesion() === 'Personal Seguridad';
    }

    // ══════════════════════════════════════════════
    // REGISTRAR
    // ══════════════════════════════════════════════
    public function registrarDotacion(array $datos): array {
        foreach (['EstadoDotacion', 'TipoDotacion', 'FechaEntrega', 'IdFuncionario'] as $campo) {
            if ($this->campoVacio($datos, $campo)) {
                return ['success' => false, 'message' => "Falta el campo: $campo"];
            }
        }

        $val = $this->convertirFecha($datos, ['FechaEntrega', 'FechaDevolucion']);
        if (!$val['success']) return $val;

        $limite = $this->limiteInferior();

        $fechaEntrega = $this->parsearFecha($datos['FechaEntrega']);
        if ($fechaEntrega === null) {
            return ['success' => false, 'message' => 'No se pudo interpretar la fecha de entrega'];
        }

        if ($fechaEntrega < $limite) {
            return ['success' => false, 'message' => 'La fecha de entrega no puede ser anterior a la hora actual'];
        }

        if (!empty($datos['FechaDevolucion'])) {
            $fechaDev = $this->parsearFecha($datos['FechaDevolucion']);
            if ($fechaDev === null) {
                return ['success' => false, 'message' => 'No se pudo interpretar la fecha de devolución'];
            }
            if ($fechaDev < $limite) {
                return ['success' => false, 'message' => 'La fecha de devolución no puede ser anterior a la hora actual'];
            }
            if ($fechaDev < $fechaEntrega) {
                return ['success' => false, 'message' => 'La fecha de devolución no puede ser anterior a la fecha de entrega'];
            }
        }

        try {
            $res = $this->modelo->insertar($datos);
            return $res['success']
                ? ['success' => true,  'message' => 'Dotación registrada correctamente',
                   'data'    => ['IdDotacion' => $res['id']]]
                : ['success' => false, 'message' => 'No se pudo registrar',
                   'error'   => $res['error'] ?? 'Error BD'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════
    // MOSTRAR CON FILTROS
    // Personal Seguridad: forzado a ver solo Activos
    // ══════════════════════════════════════════════
    public function mostrarDotaciones(): array {
        $filtros = [];
        $params  = [];

        if (!empty($_POST['estado'])) {
            $filtros[]         = "d.EstadoDotacion = :estado";
            $params[':estado'] = $_POST['estado'];
        }

        if (!empty($_POST['tipo'])) {
            $filtros[]       = "d.TipoDotacion = :tipo";
            $params[':tipo'] = $_POST['tipo'];
        }

        if (!empty($_POST['funcionario'])) {
            $filtros[]              = "f.NombreFuncionario LIKE :funcionario";
            $params[':funcionario'] = '%' . $_POST['funcionario'] . '%';
        }

        if ($this->esPersonalSeguridad()) {
            $filtros[]            = "d.Estado = :estadoReg";
            $params[':estadoReg'] = 'Activo';
        } elseif (!empty($_POST['estadoReg'])) {
            $filtros[]            = "d.Estado = :estadoReg";
            $params[':estadoReg'] = $_POST['estadoReg'];
        }

        return $this->modelo->obtenerTodos($filtros, $params);
    }

    // ══════════════════════════════════════════════
    // OBTENER POR ID
    // Personal Seguridad solo ve registros Activos
    // ══════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array {
        $registro = $this->modelo->obtenerPorId($id);

        if ($registro && $this->esPersonalSeguridad() && ($registro['Estado'] ?? '') !== 'Activo') {
            return null;
        }

        return $registro;
    }

    // ══════════════════════════════════════════════
    // ACTUALIZAR
    // ══════════════════════════════════════════════
    public function actualizarDotacion(int $id, array $datos): array {
        $val = $this->convertirFecha($datos, ['FechaEntrega', 'FechaDevolucion']);
        if (!$val['success']) return $val;
        return $this->modelo->actualizar($id, $datos);
    }

    // ══════════════════════════════════════════════
    // ELIMINAR
    // ══════════════════════════════════════════════
    public function eliminarDotacion(int $id): array {
        return $this->modelo->eliminar($id);
    }

    // ══════════════════════════════════════════════
    // PERSONAL DE SEGURIDAD (dropdown admin)
    // TipoRol = 'Personal Seguridad'
    // ══════════════════════════════════════════════
    public function obtenerFuncionarios(): array {
        return $this->modelo->obtenerFuncionarios();
    }

    // ══════════════════════════════════════════════
    // SUPERVISORES (dropdown vista supervisor)
    // TipoRol = 'Supervisor'
    // ══════════════════════════════════════════════
    public function obtenerSupervisores(): array {
        return $this->modelo->obtenerSupervisores();
    }

    // ══════════════════════════════════════════════
    // CAMBIAR ESTADO
    // ══════════════════════════════════════════════
    public function cambiarEstado(int $id, string $estado): array {
        return $this->modelo->cambiarEstado($id, $estado);
    }
}

// ════════════════════════════════════════════════
// RUTEO
// ════════════════════════════════════════════════
try {
    if (!isset($conexion)) throw new Exception("Conexión no disponible");

    $controlador = new ControladorDotacion($conexion);
    $accion      = $_POST['accion'] ?? null;
    $id          = isset($_POST['IdDotacion']) ? (int)$_POST['IdDotacion'] : 0;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarDotacion($_POST));
            break;
        case 'mostrar':
            echo json_encode($controlador->mostrarDotaciones());
            break;
        case 'obtener':
            echo json_encode($controlador->obtenerPorId($id));
            break;
        case 'actualizar':
            echo json_encode($controlador->actualizarDotacion($id, $_POST));
            break;
        case 'eliminar':
            echo json_encode($controlador->eliminarDotacion($id));
            break;
        case 'cambiar_estado':
            $nuevo = $_POST['nuevoEstado'] ?? '';
            if (!in_array($nuevo, ['Activo', 'Inactivo'])) {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
                break;
            }
            echo json_encode($controlador->cambiarEstado($id, $nuevo));
            break;

        // ── Dropdown admin: Personal Seguridad ──
        case 'personal_seguridad':
        case 'funcionarios':
            echo json_encode($controlador->obtenerFuncionarios());
            break;

        // ── Dropdown supervisor: Supervisores ──
        case 'supervisores':
            echo json_encode($controlador->obtenerSupervisores());
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida: ' . htmlspecialchars($accion ?? '')]);
            break;
    }

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error servidor: ' . $e->getMessage()]);
}
?>