<?php
require_once __DIR__ . "/../Core/conexion.php";
require_once __DIR__ . "/../Model/ModeloBitacora.php";

class ControladorBitacora {

    private BitacoraModelo $modelo;

    // Carpeta donde se almacenan los PDFs subidos
    private string $dirPDF = __DIR__ . "/../../Public/uploads/bitacoras/";

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    // ──────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────

    private function campoVacio(array $data, string $campo): bool {
        return empty(trim($data[$campo] ?? ""));
    }

    private function convertirFecha(?string $fecha): ?string {
        if (empty($fecha)) return null;
        $obj = DateTime::createFromFormat('Y-m-d\TH:i', $fecha);
        return $obj ? $obj->format('Y-m-d H:i:s') : null;
    }

    private function validarFechas(array &$datos, array $campos): array {
        foreach ($campos as $campo) {
            if (!empty($datos[$campo])) {
                $convertida = $this->convertirFecha($datos[$campo]);
                if (!$convertida) {
                    return ['success' => false, 'message' => "Formato de fecha inválido en: $campo"];
                }
                $datos[$campo] = $convertida;
            }
        }
        return ['success' => true];
    }

    /**
     * Procesa el PDF adjunto en $_FILES['ReportePDF'].
     * Retorna:
     *   - null          → no se adjuntó ningún archivo (campo vacío)
     *   - string        → ruta relativa guardada en BD  (ej: "uploads/bitacoras/bitacora_abc.pdf")
     *   - array         → ['success'=>false, 'message'=>'...'] en caso de error
     */
    private function procesarPDF(): string|array|null {
        if (!isset($_FILES['ReportePDF']) || $_FILES['ReportePDF']['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // Sin archivo adjunto → OK
        }

        $archivo = $_FILES['ReportePDF'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => "Error al subir el PDF (código {$archivo['error']})"];
        }

        // Validar que sea realmente un PDF por MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            return ['success' => false, 'message' => 'El archivo adjunto no es un PDF válido'];
        }

        // Máximo 5 MB
        if ($archivo['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El PDF no debe superar los 5 MB'];
        }

        // Crear directorio si no existe
        if (!is_dir($this->dirPDF)) {
            mkdir($this->dirPDF, 0755, true);
        }

        $nombreArchivo = uniqid('bitacora_', true) . '.pdf';
        $rutaCompleta  = $this->dirPDF . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return ['success' => false, 'message' => 'No se pudo guardar el PDF en el servidor'];
        }

        return 'uploads/bitacoras/' . $nombreArchivo; // ruta relativa para BD
    }

    // ──────────────────────────────────────────────
    // REGISTRAR BITÁCORA
    // ──────────────────────────────────────────────
    public function registrarBitacora(array $data): array {
        $obligatorios = ['TurnoBitacora', 'NovedadesBitacora', 'FechaBitacora', 'IdFuncionario', 'TieneVisitante'];

        foreach ($obligatorios as $c) {
            if ($this->campoVacio($data, $c)) {
                return ['success' => false, 'message' => "Falta el campo: $c"];
            }
        }

        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        // Visitante y dispositivo
        if (($data['TieneVisitante'] ?? 'no') === 'si') {
            if ($this->campoVacio($data, 'IdVisitante')) {
                return ['success' => false, 'message' => 'El ID del visitante es obligatorio'];
            }
            if (($data['TraeDispositivo'] ?? 'no') === 'si' && $this->campoVacio($data, 'IdDispositivo')) {
                return ['success' => false, 'message' => 'El ID del dispositivo es obligatorio'];
            }
            $data['IdDispositivo'] = $data['IdDispositivo'] ?? null;
        } else {
            $data['IdVisitante']     = null;
            $data['IdDispositivo']   = null;
            $data['TraeDispositivo'] = 'no';
        }

        // Procesar PDF adjunto
        $pdf = $this->procesarPDF();
        if (is_array($pdf)) return $pdf;          // Error en el PDF
        $data['ReporteBitacora'] = $pdf;           // null si no hay PDF

        try {
            $res = $this->modelo->insertar($data);
            return $res['success']
                ? ['success' => true,  'message' => 'Bitácora registrada correctamente',
                   'data'    => ['IdBitacora' => $res['id']]]
                : ['success' => false, 'message' => 'No se pudo registrar en la base de datos',
                   'error'   => $res['error'] ?? 'Error BD'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    // OBTENER TODAS LAS BITÁCORAS
    // ──────────────────────────────────────────────
    public function obtenerBitacoras(array $filtros = [], array $params = []): array {
        return $this->modelo->obtenerBitacoras($filtros, $params);
    }

    // ──────────────────────────────────────────────
    // OBTENER BITÁCORA POR ID
    // ──────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array {
        return $this->modelo->obtenerPorId($id);
    }

    // ──────────────────────────────────────────────
    // ACTUALIZAR BITÁCORA
    // ──────────────────────────────────────────────
    public function actualizar(int $id, array $data): array {
        $validacion = $this->validarFechas($data, ['FechaBitacora']);
        if (!$validacion['success']) return $validacion;

        $pdf = $this->procesarPDF();
        if (is_array($pdf)) return $pdf;
        if ($pdf !== null) $data['ReporteBitacora'] = $pdf;

        return $this->modelo->actualizar($id, $data);
    }

    // ──────────────────────────────────────────────
    // PERSONAL DE SEGURIDAD (para el dropdown)
    // ──────────────────────────────────────────────
    public function obtenerPersonalSeguridad(): array {
        return $this->modelo->obtenerPersonalSeguridad();
    }

    // ──────────────────────────────────────────────
    // VISITANTES ACTIVOS (para el dropdown)
    // ──────────────────────────────────────────────
    public function obtenerVisitantes(): array {
        return $this->modelo->obtenerVisitantes();
    }

    // ──────────────────────────────────────────────
    // DISPOSITIVOS ACTIVOS (para el dropdown)
    // ──────────────────────────────────────────────
    public function obtenerDispositivos(?int $idVisitante = null): array {
        return $this->modelo->obtenerDispositivos($idVisitante);
    }
}

// ════════════════════════════════════════════════
// RUTEO
// ════════════════════════════════════════════════
try {
    if (!isset($conexion)) throw new Exception("No hay conexión a la base de datos");

    $controlador = new ControladorBitacora($conexion);
    $accion      = $_POST['accion'] ?? $_GET['accion'] ?? "";
    $id          = isset($_POST['IdBitacora']) ? (int)$_POST['IdBitacora'] : 0;

    header('Content-Type: application/json; charset=utf-8');

    switch ($accion) {
        case 'registrar':
            echo json_encode($controlador->registrarBitacora($_POST));
            break;
        case 'mostrar':
            echo json_encode($controlador->obtenerBitacoras());
            break;
        case 'obtener':
            echo json_encode($controlador->obtenerPorId($id));
            break;
        case 'actualizar':
            echo json_encode($controlador->actualizar($id, $_POST));
            break;
        case 'personal_seguridad':
            // Endpoint para cargar el dropdown desde JS
            echo json_encode($controlador->obtenerPersonalSeguridad());
            break;

        case 'visitantes':
            echo json_encode($controlador->obtenerVisitantes());
            break;

        case 'dispositivos':
            // Filtra por visitante si se envía, para mostrar solo sus dispositivos
            $idVisitante = isset($_POST['IdVisitante']) && $_POST['IdVisitante'] !== ''
                ? (int)$_POST['IdVisitante']
                : null;
            echo json_encode($controlador->obtenerDispositivos($idVisitante));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error servidor: ' . $e->getMessage()]);
}
?>