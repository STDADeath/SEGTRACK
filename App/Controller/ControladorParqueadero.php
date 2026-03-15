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

file_put_contents($carpetaDebug . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

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

    // ═════════════════════════════════════════════════════════════════════════
    class ControladorParqueadero {
        private $modelo;
        private $carpetaDebug;

        public function __construct($conexion) {
            $this->modelo       = new ModeloParqueadero($conexion);
            $this->carpetaDebug = __DIR__ . '/Debug_Parqueadero';
        }

        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim((string)$campo) === '';
        }

        // ── Crear parqueadero ─────────────────────────────────────────────────
        public function crear(array $d): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== crear parqueadero ===\n", FILE_APPEND);

            $idSede = isset($d['IdSede']) ? (int)$d['IdSede'] : 0;
            $carros = isset($d['Carros'])  ? (int)$d['Carros']  : 0;
            $motos  = isset($d['Motos'])   ? (int)$d['Motos']   : 0;
            $bicis  = isset($d['Bicis'])   ? (int)$d['Bicis']   : 0;
            $total  = $carros + $motos + $bicis;

            if ($idSede <= 0) {
                return ['success' => false, 'message' => 'Debe seleccionar una sede válida'];
            }
            if ($total <= 0) {
                return ['success' => false, 'message' => 'Debe ingresar al menos un espacio (carros, motos o bicicletas)'];
            }
            if ($this->modelo->existeParqueaderoPorSede($idSede)) {
                return ['success' => false, 'message' => 'Esta sede ya tiene un parqueadero registrado'];
            }

            try {
                $resultado = $this->modelo->crearParqueadero($idSede, $total, $carros, $motos, $bicis);

                if ($resultado['success']) {
                    return [
                        'success' => true,
                        'message' => "Parqueadero creado correctamente con ID: " . $resultado['id'],
                        'data'    => ['IdParqueadero' => $resultado['id']]
                    ];
                }
                return ['success' => false, 'message' => 'Error al crear en BD: ' . ($resultado['error'] ?? 'desconocido')];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN crear: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Actualizar parqueadero ────────────────────────────────────────────
        public function actualizar(array $d): array {
            $id = isset($d['id']) ? (int)$d['id'] : 0;
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== actualizar parqueadero ID: $id ===\n", FILE_APPEND);

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de parqueadero no válido'];
            }

            $carros = isset($d['Carros']) ? (int)$d['Carros'] : 0;
            $motos  = isset($d['Motos'])  ? (int)$d['Motos']  : 0;
            $bicis  = isset($d['Bicis'])  ? (int)$d['Bicis']  : 0;
            $total  = $carros + $motos + $bicis;

            if ($total <= 0) {
                return ['success' => false, 'message' => 'Debe ingresar al menos un espacio'];
            }

            try {
                $resultado = $this->modelo->actualizarParqueadero($id, $total, $carros, $motos, $bicis);

                if ($resultado['success']) {
                    return ['success' => true, 'message' => 'Parqueadero actualizado correctamente'];
                }
                return ['success' => false, 'message' => 'Error al actualizar: ' . ($resultado['error'] ?? 'desconocido')];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN actualizar: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Cambiar estado (Activo <-> Inactivo) ──────────────────────────────
        public function cambiarEstado(int $id, string $estado): array {
            try {
                $resultado = $this->modelo->cambiarEstado($id, $estado);

                if ($resultado['success']) {
                    $accion = $estado === 'Activo' ? 'activado' : 'desactivado';
                    return ['success' => true, 'message' => "Parqueadero $accion correctamente", 'nuevoEstado' => $estado];
                }
                return ['success' => false, 'message' => 'Error al cambiar estado del parqueadero'];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN cambiarEstado: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Obtener espacios (admin — modal ver espacios) ─────────────────────
        public function obtenerEspacios(int $id): array {
            return $this->modelo->obtenerEspacios($id);
        }

        // ── Obtener datos de sede para el guardia ─────────────────────────────
        public function obtenerDatosSede(int $idSede): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== obtenerDatosSede — Sede: $idSede ===\n", FILE_APPEND);

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

        // ── Obtener vehículos activos por tipo (para el select del guardia) ─────
        public function obtenerVehiculosPorTipo(string $tipo): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt', "=== obtenerVehiculosPorTipo — Tipo: $tipo ===\n", FILE_APPEND);

            $tipos_validos = ['Carro', 'Moto', 'Bicicleta'];
            if (!in_array($tipo, $tipos_validos)) {
                return ['success' => false, 'message' => "Tipo de vehículo inválido: $tipo"];
            }

            $vehiculos = $this->modelo->obtenerVehiculosPorTipo($tipo);
            return ['success' => true, 'vehiculos' => $vehiculos];
        }

        // ── Ocupar espacio manualmente (guardia sin escáner) ──────────────────
        public function ocuparManual(int $idEspacio, int $idVehiculo): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt',
                "=== ocuparManual — Espacio: $idEspacio, IdVehiculo: $idVehiculo ===\n", FILE_APPEND);

            if ($idEspacio <= 0) {
                return ['success' => false, 'message' => 'ID de espacio no válido'];
            }
            if ($idVehiculo <= 0) {
                return ['success' => false, 'message' => 'Debe seleccionar un vehículo'];
            }

            try {
                $r = $this->modelo->ocuparEspacio($idEspacio, $idVehiculo);
                if ($r['success']) {
                    return ['success' => true, 'message' => 'Espacio asignado correctamente'];
                }
                return ['success' => false, 'message' => $r['error'] ?? 'Error al ocupar el espacio'];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN ocuparManual: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // ── Liberar espacio manualmente (guardia) ─────────────────────────────
        public function liberarManual(int $idEspacio): array {
            file_put_contents($this->carpetaDebug . '/debug_log.txt',
                "=== liberarManual — Espacio: $idEspacio ===\n", FILE_APPEND);

            if ($idEspacio <= 0) {
                return ['success' => false, 'message' => 'ID de espacio no válido'];
            }

            try {
                $r = $this->modelo->liberarEspacio($idEspacio);
                if ($r['success']) {
                    return ['success' => true, 'message' => 'Espacio liberado correctamente'];
                }
                return ['success' => false, 'message' => $r['error'] ?? 'Error al liberar el espacio'];

            } catch (Exception $e) {
                file_put_contents($this->carpetaDebug . '/debug_log.txt', "EXCEPCIÓN liberarManual: " . $e->getMessage() . "\n", FILE_APPEND);
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

    }
    // ═════════════════════════════════════════════════════════════════════════

    $controlador = new ControladorParqueadero($conexion);
    $accion      = $_POST['accion'] ?? '';

    file_put_contents($carpetaDebug . '/debug_log.txt', "Acción detectada: $accion\n", FILE_APPEND);

    if ($accion === 'crear') {
        $resultado = $controlador->crear($_POST);

    } elseif ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $resultado = $controlador->actualizar($_POST);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de parqueadero no válido'];
        }

    } elseif ($accion === 'cambiar_estado') {
        $id    = isset($_POST['id'])     ? (int)$_POST['id'] : 0;
        $estado = $_POST['estado']       ?? '';
        if ($id > 0 && in_array($estado, ['Activo', 'Inactivo'])) {
            $resultado = $controlador->cambiarEstado($id, $estado);
        } else {
            $resultado = ['success' => false, 'message' => 'Datos no válidos para cambiar estado'];
        }

    } elseif ($accion === 'obtener_espacios') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $espacios  = $controlador->obtenerEspacios($id);
            $resultado = ['success' => true, 'espacios' => $espacios];
        } else {
            $resultado = ['success' => false, 'message' => 'ID no válido'];
        }

    } elseif ($accion === 'obtener_sede') {
        $idSede    = isset($_POST['id_sede']) ? (int)$_POST['id_sede'] : 0;
        $resultado = $idSede > 0
            ? $controlador->obtenerDatosSede($idSede)
            : ['success' => false, 'message' => 'ID de sede no válido'];

    } elseif ($accion === 'obtener_vehiculos_tipo') {
        $tipo      = trim($_POST['tipo'] ?? '');
        $resultado = $controlador->obtenerVehiculosPorTipo($tipo);

    } elseif ($accion === 'ocupar_manual') {
        $idEspacio  = isset($_POST['id_espacio'])  ? (int)$_POST['id_espacio']  : 0;
        $idVehiculo = isset($_POST['id_vehiculo']) ? (int)$_POST['id_vehiculo'] : 0;
        $resultado  = $controlador->ocuparManual($idEspacio, $idVehiculo);

    } elseif ($accion === 'liberar_manual') {
        $idEspacio = isset($_POST['id_espacio']) ? (int)$_POST['id_espacio'] : 0;
        $resultado = $controlador->liberarManual($idEspacio);

    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida: ' . $accion];
    }

    file_put_contents($carpetaDebug . '/debug_log.txt',
        "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n=== FIN ===\n\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    file_put_contents($carpetaDebug . '/debug_log.txt', "ERROR GENERAL: " . $e->getMessage() . "\n=== FIN ===\n\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>