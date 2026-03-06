<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/Debug_Parqueadero/error_log.txt');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Crear carpeta Debug_Parqueadero si no existe
$carpetaDebug = __DIR__ . '/Debug_Parqueadero';
if (!file_exists($carpetaDebug)) {
    mkdir($carpetaDebug, 0777, true);
}

file_put_contents($carpetaDebug . '/debug_log.txt',
    "\n" . date('Y-m-d H:i:s') . " === INICIO CONTROLADOR PARQUEADERO ===\n", FILE_APPEND);

try {
    file_put_contents($carpetaDebug . '/debug_log.txt',
        "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // ── Conexión ──────────────────────────────────────────────────────────────
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }
    require_once $ruta_conexion;

    $conexionObj = new Conexion();
    $conexion    = $conexionObj->getConexion();

    if (!isset($conexion) || !($conexion instanceof PDO)) {
        throw new Exception("La conexión no es una instancia de PDO");
    }

    // ── Modelo ────────────────────────────────────────────────────────────────
    $ruta_modelo = __DIR__ . '/../Model/ModeloParqueadero.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }
    require_once $ruta_modelo;

    // ══════════════════════════════════════════════════════════════════════════
    class ControladorParqueadero {
        private $modelo;
        private $carpetaDebug;
        private $conexion;

        public function __construct($conexion) {
            $this->conexion     = $conexion;
            $this->modelo       = new ModeloParqueadero($conexion);
            $this->carpetaDebug = __DIR__ . '/Debug_Parqueadero';
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim((string)$campo) === '';
        }

        private function log(string $msg): void {
            file_put_contents($this->carpetaDebug . '/debug_log.txt',
                date('Y-m-d H:i:s') . " $msg\n", FILE_APPEND);
        }

        // ── Crear parqueadero ─────────────────────────────────────────────────
        public function crear(array $d): array {
            $this->log("=== crear parqueadero ===");

            $idSede = isset($d['IdSede']) ? (int)$d['IdSede'] : 0;
            $carros = isset($d['Carros']) ? (int)$d['Carros'] : 0;
            $motos  = isset($d['Motos'])  ? (int)$d['Motos']  : 0;
            $bicis  = isset($d['Bicis'])  ? (int)$d['Bicis']  : 0;
            $total  = $carros + $motos + $bicis;

            if ($idSede <= 0) {
                return ['success' => false, 'message' => 'Debe seleccionar una sede válida'];
            }
            if ($total <= 0) {
                return ['success' => false, 'message' => 'La cantidad total de espacios debe ser mayor a 0'];
            }
            if ($carros < 0 || $motos < 0 || $bicis < 0) {
                return ['success' => false, 'message' => 'Las cantidades no pueden ser negativas'];
            }

            if ($this->modelo->existeParqueaderoPorSede($idSede)) {
                return ['success' => false, 'message' => 'Esta sede ya tiene un parqueadero configurado. Use la opción Editar para modificarlo.'];
            }

            $resultado = $this->modelo->crearParqueadero($idSede, $total, $carros, $motos, $bicis);

            if ($resultado['success']) {
                $this->log("✅ Parqueadero creado ID: " . $resultado['id']);
                return [
                    'success' => true,
                    'message' => "Parqueadero creado con $total espacios ($carros carros, $motos motos, $bicis bicicletas)",
                    'data'    => ['IdParqueadero' => $resultado['id'], 'Total' => $total]
                ];
            }
            return ['success' => false, 'message' => 'Error al crear: ' . ($resultado['error'] ?? 'Desconocido')];
        }

        // ── Actualizar parqueadero ────────────────────────────────────────────
        public function actualizar(array $d): array {
            $id     = isset($d['id'])     ? (int)$d['id']     : 0;
            $carros = isset($d['Carros']) ? (int)$d['Carros'] : 0;
            $motos  = isset($d['Motos'])  ? (int)$d['Motos']  : 0;
            $bicis  = isset($d['Bicis'])  ? (int)$d['Bicis']  : 0;
            $total  = $carros + $motos + $bicis;

            $this->log("=== actualizar parqueadero ID: $id ===");

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de parqueadero no válido'];
            }
            if ($total <= 0) {
                return ['success' => false, 'message' => 'La cantidad total debe ser mayor a 0'];
            }
            if ($carros < 0 || $motos < 0 || $bicis < 0) {
                return ['success' => false, 'message' => 'Las cantidades no pueden ser negativas'];
            }

            $resultado = $this->modelo->actualizarParqueadero($id, $total, $carros, $motos, $bicis);

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'message' => "Parqueadero actualizado: $total espacios ($carros carros, $motos motos, $bicis bicicletas)"
                ];
            }
            return ['success' => false, 'message' => $resultado['error'] ?? 'Error al actualizar'];
        }

        // ── Cambiar estado ────────────────────────────────────────────────────
        public function cambiarEstado(int $id, string $estado): array {
            $this->log("=== cambiarEstado ID: $id → $estado ===");

            if ($id <= 0 || !in_array($estado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'message' => 'Datos no válidos'];
            }

            $resultado = $this->modelo->cambiarEstado($id, $estado);

            if ($resultado['success']) {
                $accion = $estado === 'Activo' ? 'activado' : 'desactivado';
                return [
                    'success'     => true,
                    'message'     => "Parqueadero $accion correctamente",
                    'nuevoEstado' => $estado
                ];
            }
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }

        // ── Obtener espacios (para modal grid admin) ──────────────────────────
        public function obtenerEspacios(int $id): array {
            return $this->modelo->obtenerEspacios($id);
        }

        // ── Obtener datos de sede para el guardia ─────────────────────────────
        public function obtenerDatosSede(int $idSede): array {
            $parqueadero = $this->modelo->obtenerParqueaderoPorSede($idSede);
            if (!$parqueadero) {
                return ['success' => false, 'message' => 'Esta sede no tiene parqueadero activo configurado'];
            }

            $idParqueadero = (int)$parqueadero['IdParqueadero'];
            return [
                'success'     => true,
                'parqueadero' => $parqueadero,
                'espacios'    => $this->modelo->obtenerEspaciosDetalle($idParqueadero),
                'resumen'     => $this->modelo->obtenerResumenEspacios($idParqueadero)
            ];
        }

        // ── Ocupar espacio manualmente (guardia sin escáner) ──────────────────
        public function ocuparManual(int $idEspacio, string $placa): array {
            $this->log("=== ocuparManual espacio $idEspacio placa $placa ===");

            if ($idEspacio <= 0)       return ['success' => false, 'message' => 'ID de espacio no válido'];
            if (empty(trim($placa)))   return ['success' => false, 'message' => 'La placa es requerida'];

            $vehiculo = $this->modelo->obtenerVehiculoPorPlaca($placa);
            if (!$vehiculo) {
                return ['success' => false, 'message' => "Vehículo con placa '$placa' no encontrado o inactivo"];
            }

            $r = $this->modelo->ocuparEspacio($idEspacio, (int)$vehiculo['IdVehiculo']);
            if ($r['success']) {
                return ['success' => true, 'message' => "Espacio asignado al vehículo $placa correctamente"];
            }
            return ['success' => false, 'message' => $r['error'] ?? 'Error al ocupar el espacio'];
        }

        // ── Liberar espacio manualmente (guardia) ─────────────────────────────
        public function liberarManual(int $idEspacio): array {
            $this->log("=== liberarManual espacio $idEspacio ===");

            if ($idEspacio <= 0) return ['success' => false, 'message' => 'ID de espacio no válido'];

            $r = $this->modelo->liberarEspacio($idEspacio);
            if ($r['success']) {
                return ['success' => true, 'message' => 'Espacio liberado correctamente'];
            }
            return ['success' => false, 'message' => $r['error'] ?? 'Error al liberar el espacio'];
        }

        // ══════════════════════════════════════════════════════════════════════
        // ACCIÓN PARA EL ESCÁNER (módulo del compañero)
        // ══════════════════════════════════════════════════════════════════════
        /**
         * INTEGRACIÓN ESCÁNER:
         * El módulo escáner debe hacer POST a ControladorParqueadero.php con:
         *   accion      => 'escanear_qr'
         *   placa       => 'ABC123'
         *   id_sede     => 3
         *   tipo_evento => 'entrada' | 'salida'
         *
         * Si el QR almacena IdVehiculo en lugar de placa, adaptar el método
         * procesarEscaneo() en ModeloParqueadero.php para recibirlo por ID.
         */
        public function escanearQR(string $placa, int $idSede, string $tipoEvento): array {
            $this->log("=== escanearQR placa=$placa sede=$idSede evento=$tipoEvento ===");

            if (empty(trim($placa)))   return ['success' => false, 'mensaje' => 'Placa requerida'];
            if ($idSede <= 0)          return ['success' => false, 'mensaje' => 'ID de sede no válido'];
            if (!in_array($tipoEvento, ['entrada', 'salida'])) {
                return ['success' => false, 'mensaje' => "Tipo de evento inválido. Use 'entrada' o 'salida'"];
            }

            return $this->modelo->procesarEscaneo($placa, $idSede, $tipoEvento);
        }
    }

    // ── Router ────────────────────────────────────────────────────────────────
    $controlador = new ControladorParqueadero($conexion);
    $accion      = $_POST['accion'] ?? '';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    switch ($accion) {

        case 'crear':
            $resultado = $controlador->crear($_POST);
            break;

        case 'actualizar':
            $resultado = $controlador->actualizar($_POST);
            break;

        case 'cambiar_estado':
            $id     = isset($_POST['id'])     ? (int)$_POST['id'] : 0;
            $estado = $_POST['estado'] ?? '';
            $resultado = $controlador->cambiarEstado($id, $estado);
            break;

        case 'obtener_espacios':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                $resultado = ['success' => false, 'message' => 'ID no válido'];
            } else {
                $espacios  = $controlador->obtenerEspacios($id);
                $resultado = ['success' => true, 'espacios' => $espacios];
            }
            break;

        // ── Guardia: cargar parqueadero de una sede ───────────────────────────
        case 'obtener_sede':
            $idSede    = isset($_POST['id_sede']) ? (int)$_POST['id_sede'] : 0;
            $resultado = $idSede > 0
                ? $controlador->obtenerDatosSede($idSede)
                : ['success' => false, 'message' => 'ID de sede no válido'];
            break;

        // ── Guardia: ocupar espacio manualmente ───────────────────────────────
        case 'ocupar_manual':
            $idEspacio = isset($_POST['id_espacio']) ? (int)$_POST['id_espacio'] : 0;
            $placa     = trim($_POST['placa'] ?? '');
            $resultado = $controlador->ocuparManual($idEspacio, $placa);
            break;

        // ── Guardia: liberar espacio manualmente ──────────────────────────────
        case 'liberar_manual':
            $idEspacio = isset($_POST['id_espacio']) ? (int)$_POST['id_espacio'] : 0;
            $resultado = $controlador->liberarManual($idEspacio);
            break;

        // ════════════════════════════════════════════════════════════════════
        // ESCÁNER QR — Punto de entrada del módulo escáner del compañero
        // POST: accion=escanear_qr, placa=ABC123, id_sede=3, tipo_evento=entrada|salida
        // ════════════════════════════════════════════════════════════════════
        case 'escanear_qr':
            $placa      = trim($_POST['placa']       ?? '');
            $idSede     = isset($_POST['id_sede'])   ? (int)$_POST['id_sede'] : 0;
            $tipoEvento = trim($_POST['tipo_evento'] ?? '');
            $resultado  = $controlador->escanearQR($placa, $idSede, $tipoEvento);
            break;

        default:
            $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt',
        "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n=== FIN ===\n\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR FINAL: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>