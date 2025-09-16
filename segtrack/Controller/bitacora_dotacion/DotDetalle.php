<?php
/**
 * SEGTRACK – Detalle de Dotación (GET)
 * URL: backed/dotdetalle.php?id=123
 * Responde: { ok, data: { id, codigo, nombre, tipo, estado, novedad } }
 * - "codigo" es virtual DOT-###### si no existe columna en BD.
 * - Tolerante a variantes de nombres (espacios, guiones, acentos, mayúsculas/minúsculas).
 * - ?debug=1 agrega mapeo de columnas para diagnóstico.
 */
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método no permitido (use GET).'], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'message' => 'Parámetro id inválido.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$conn = (new Conexion())->getConexion();
$conn->set_charset('utf8mb4');

try {
  $st = $conn->prepare('SELECT * FROM dotacion WHERE IdDotacion = ? LIMIT 1');
  if (!$st) throw new RuntimeException('Error en prepare: ' . $conn->error);
  $st->bind_param('i', $id);
  $st->execute();
  $res = $st->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $st->close();

  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Dotación no encontrada.'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // ---------- Normalizador robusto (quita acentos + no alfanum) ----------
  $normalize = function($s) {
    $s = (string)$s;
    // convertir acentos a ASCII
    if (function_exists('iconv')) {
      $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
      if ($t !== false) $s = $t;
    } else {
      $s = strtr($s, [
        'á'=>'a','à'=>'a','ä'=>'a','â'=>'a','Á'=>'A','À'=>'A','Ä'=>'A','Â'=>'A',
        'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e','É'=>'E','È'=>'E','Ë'=>'E','Ê'=>'E',
        'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','Í'=>'I','Ì'=>'I','Ï'=>'I','Î'=>'I',
        'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o','Ó'=>'O','Ò'=>'O','Ö'=>'O','Ô'=>'O',
        'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','Ú'=>'U','Ù'=>'U','Ü'=>'U','Û'=>'U',
        'ñ'=>'n','Ñ'=>'N'
      ]);
    }
    $s = strtolower($s);
    return preg_replace('/[^a-z0-9]/', '', $s); // quita espacios,_,-,etc.
  };

  // construir índice normalizado: normKey => originalKey
  $normIndex = [];
  foreach ($row as $k => $_) { $normIndex[$normalize($k)] = $k; }

  // busca la primera clave existente entre candidatos
  $pick = function(array $cands) use ($normIndex, $row, $normalize) {
    foreach ($cands as $c) {
      $norm = $normalize($c);
      if (isset($normIndex[$norm])) {
        $orig = $normIndex[$norm];
        $val = $row[$orig];
        if ($val !== null && $val !== '') return (string)$val;
      }
    }
    return '';
  };

  // Código: persistido si existe; si no, virtual
  $codigo = $pick(['CodigoDotacion','codigo_dotacion','codigo','Codigo']);
  if ($codigo === '') $codigo = sprintf('DOT-%06d', (int)$row['IdDotacion']);

  $tipo    = $pick(['TipoDotacion','tipo_dotacion','Tipo','tipo','Tipo Dotacion','Tipo de Dotacion']);
  $estado  = $pick(['EstadoDotacion','estado_dotacion','Estado','estado','Estado Disponibilidad','Estado de Dotacion']);
  $novedad = $pick(['Novedad','novedad','Observacion','observacion','Observación']);

  $data = [
    'id'      => (int)$row['IdDotacion'],
    'codigo'  => $codigo,
    'nombre'  => (string)$row['NombreDotacion'],
    'tipo'    => $tipo,
    'estado'  => $estado,
    'novedad' => $novedad,
  ];

  $resp = ['ok' => true, 'data' => $data];

  // Debug opcional
  if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    $resp['debug'] = [
      'columns' => array_keys($row),
      'normMap' => $normIndex
    ];
  }

  http_response_code(200);
  echo json_encode($resp, JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
