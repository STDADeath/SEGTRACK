<?php
/**
 * SEGTRACK QR - Dotaciones
 * Endpoint: backed/DotacionIngreso.php
 *
 * Crea una dotación (SIN QR por ahora).
 * - Devuelve JSON consistente (201/422/500).
 * - Valida y sanea entrada.
 * - Operación atómica en transacción.
 * - Estado inicial: 'disponible'.
 * - Genera 'codigo' virtual (DOT-######) y lo persiste SOLO si existe columna compatible.
 * - Registra en Bitácora si existe la tabla.
 *
 * HOOKS (comentados):
 *  - HOOK_QR_BACKEND: punto para conectar el escáner/QR cuando se defina.
 *  - HOOK_ENTREGA_INMEDIATA: crear+entregar en un paso (no activo).
 */

require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

// 1) Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método no permitido. Use POST.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$conn = (new Conexion())->getConexion();
$conn->set_charset('utf8mb4');

// 2) Entrada y validación
$input = filter_input_array(INPUT_POST, [
  'NombreDotacion' => ['filter' => FILTER_UNSAFE_RAW],
  'TipoDotacion'   => ['filter' => FILTER_UNSAFE_RAW],
  'Novedad'        => ['filter' => FILTER_UNSAFE_RAW, 'flags' => FILTER_NULL_ON_FAILURE],
  'IdFuncionario'  => ['filter' => FILTER_UNSAFE_RAW, 'flags' => FILTER_NULL_ON_FAILURE], // opcional (no se usa aquí)
], false);

$NombreDotacion = limpiarTexto($input['NombreDotacion'] ?? '');
$TipoDotacion   = strtolower(limpiarTexto($input['TipoDotacion'] ?? ''));
$Novedad        = limpiarTexto($input['Novedad'] ?? '');
$IdFuncionario  = $input['IdFuncionario'] !== null ? trim((string)$input['IdFuncionario']) : null;

$errors = [];
if ($NombreDotacion === '') { $errors['NombreDotacion'] = 'El nombre es obligatorio.'; }
if ($TipoDotacion === '')   { $errors['TipoDotacion']   = 'El tipo es obligatorio.'; }

$allowedTipos = ['radio','linterna','chaleco','llaves','tablet','arma','uniforme','otro'];
if ($TipoDotacion && !in_array($TipoDotacion, $allowedTipos, true)) {
  $TipoDotacion = 'otro';
}

if (!empty($errors)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'message' => 'Errores de validación.', 'errors' => $errors], JSON_UNESCAPED_UNICODE);
  exit;
}

// 3) Transacción
try {
  $conn->begin_transaction();

  // 3.1) INSERT principal
  $sql = 'INSERT INTO dotacion (NombreDotacion, TipoDotacion, EstadoDotacion, Novedad)
          VALUES (?, ?, "disponible", ?)';
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    throw new RuntimeException('Error en prepare (insert): ' . $conn->error);
  }
  $stmt->bind_param('sss', $NombreDotacion, $TipoDotacion, $Novedad);
  if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    throw new RuntimeException('Error al insertar: ' . $err);
  }
  $nuevoId = (int)$conn->insert_id;
  $stmt->close();

  // 3.2) Generar código humano (virtual) y persistir si hay columna
  $codigo = sprintf('DOT-%06d', $nuevoId);
  $columnaCodigo = resolverColumnaCodigoDotacion($conn);
  if ($columnaCodigo !== null) {
    $upd = $conn->prepare("UPDATE dotacion SET {$columnaCodigo} = ? WHERE IdDotacion = ?");
    if ($upd) {
      $upd->bind_param('si', $codigo, $nuevoId);
      $upd->execute();
      $upd->close();
    }
  }
  // SIN else: si no hay columna, conservamos $codigo para la respuesta JSON

  // ---------------- HOOK_QR_BACKEND ----------------
  // if (isset($input['qr_payload'])) { ... }

  // ---------------- HOOK_ENTREGA_INMEDIATA (COMENTADO) ----------------
  // if ($IdFuncionario !== null && $IdFuncionario !== '') { ... }

  // 3.3) Bitácora (si existe)
  bitacoraLog($conn, 'dotacion_creada', [
    'id'     => $nuevoId,
    'nombre' => $NombreDotacion,
    'tipo'   => $TipoDotacion,
    'codigo' => $codigo,
    'estado' => 'disponible',
  ]);

  $conn->commit();

  // 4) Respuesta
  http_response_code(201);
  echo json_encode([
    'ok'      => true,
    'message' => 'Dotación creada correctamente.',
    'id'      => $nuevoId,
    'codigo'  => $codigo,       // puede no estar persistido, pero siempre se devuelve
    'estado'  => 'disponible',
  ], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  // Rollback seguro
  try { $conn->rollback(); } catch (Throwable $e2) {}
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}

/* ==================== Helpers ==================== */

function limpiarTexto(?string $v): string {
  $v = (string)$v;
  $v = trim($v);
  return strip_tags($v);
}

function columnaExiste(mysqli $conn, string $tabla, string $columna): bool {
  $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = ?
             AND COLUMN_NAME  = ?
           LIMIT 1";
  $st = $conn->prepare($sql);
  if (!$st) return false;
  $st->bind_param('ss', $tabla, $columna);
  $st->execute();
  $st->store_result();
  $ok = $st->num_rows > 0;
  $st->close();
  return $ok;
}

function tablaExiste(mysqli $conn, string $tabla): bool {
  $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = ?
           LIMIT 1";
  $st = $conn->prepare($sql);
  if (!$st) return false;
  $st->bind_param('s', $tabla);
  $st->execute();
  $st->store_result();
  $ok = $st->num_rows > 0;
  $st->close();
  return $ok;
}

/**
 * Retorna el nombre de la columna de "código" si existe:
 *  - `CodigoDotacion` | `codigo_dotacion` | `codigo`
 * Devuelve con backticks para usar en SQL, o null si no existe.
 */
function resolverColumnaCodigoDotacion(mysqli $conn): ?string {
  $candidatas = ['CodigoDotacion', 'codigo_dotacion', 'codigo'];
  foreach ($candidatas as $col) {
    if (columnaExiste($conn, 'dotacion', $col)) {
      return '`' . $col . '`';
    }
  }
  return null;
}

/**
 * Inserta en bitácora si la tabla existe. Intenta con variantes de columnas.
 */
function bitacoraLog(mysqli $conn, string $accion, array $detalle): void {
  if (!tablaExiste($conn, 'bitacora')) return;

  $detalleJson = json_encode($detalle, JSON_UNESCAPED_UNICODE);

  $intentos = [
    'INSERT INTO bitacora (Accion, Detalle, Fecha) VALUES (?, ?, NOW())',
    'INSERT INTO bitacora (accion, detalle, fecha) VALUES (?, ?, NOW())',
    'INSERT INTO bitacora (accion, detalle) VALUES (?, ?)', // si fecha tiene default
  ];

  foreach ($intentos as $sql) {
    $st = $conn->prepare($sql);
    if ($st) {
      if (substr_count($sql, '?') === 3) {
        $st->bind_param('sss', $accion, $detalleJson, $dummy = null);
      } else {
        $st->bind_param('ss', $accion, $detalleJson);
      }
      $st->execute();
      $st->close();
      return;
    }
  }
}
