<?php
/**
 * Eliminar Bitácora - JSON only
 * Acepta: POST { id | IdBitacora }
 * Respuesta: { ok: bool, message: string, deleted?: int }
 */
declare(strict_types=1);
date_default_timezone_set('America/Bogota');

ob_start();
ini_set('display_errors','0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$send = function(array $p){
  while (ob_get_level() > 0) { ob_end_clean(); }
  echo json_encode($p, JSON_UNESCAPED_UNICODE);
  exit;
};

// Manejo uniforme de errores fatales/excepciones
set_exception_handler(function(Throwable $e) use ($send){ $send(['ok'=>false,'message'=>$e->getMessage()]); });
register_shutdown_function(function() use ($send){
  $e = error_get_last();
  if ($e && in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true)){
    $send(['ok'=>false,'message'=>'Fatal: '.$e['message']]);
  }
});
set_error_handler(function($errno,$errstr,$errfile,$errline){
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $send(['ok'=>false,'message'=>'Método no permitido']);
}

require_once __DIR__ . '/conexion.php';
if (!class_exists('Conexion')) $send(['ok'=>false,'message'=>'No se halló clase Conexion']);

$cn = new Conexion();
$db = $cn->getConexion();
if (!($db instanceof mysqli)) $send(['ok'=>false,'message'=>'getConexion() no devolvió mysqli']);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db->set_charset('utf8mb4');

// ===== Entradas (tolerante de nombres) =====
$id = 0;
foreach (['id','IdBitacora'] as $k) {
  if (isset($_POST[$k]) && $_POST[$k] !== '') { $id = (int)$_POST[$k]; break; }
}
if ($id <= 0) $send(['ok'=>false,'message'=>'ID de bitácora inválido']);

// ===== Verificar existencia =====
$st = $db->prepare("SELECT 1 FROM `bitacora` WHERE `IdBitacora`=? LIMIT 1");
$st->bind_param('i',$id);
$st->execute(); $st->store_result();
if ($st->num_rows === 0) {
  $st->close();
  $send(['ok'=>false,'message'=>"La bitácora $id no existe"]);
}
$st->close();

// ===== Eliminar =====
try {
  $st = $db->prepare("DELETE FROM `bitacora` WHERE `IdBitacora`=?");
  $st->bind_param('i',$id);
  $st->execute();
  $deleted = $st->affected_rows;
  $st->close();
  $send(['ok'=>true,'message'=>'Bitácora eliminada correctamente','deleted'=>$deleted]);
} catch (mysqli_sql_exception $e) {
  // 1451: Cannot delete or update a parent row: a foreign key constraint fails
  if ((int)$e->getCode() === 1451) {
    $send(['ok'=>false,'message'=>'No se puede eliminar: la bitácora está referenciada por otros registros']);
  }
  $send(['ok'=>false,'message'=>'Error al eliminar: '.$e->getMessage()]);
}
