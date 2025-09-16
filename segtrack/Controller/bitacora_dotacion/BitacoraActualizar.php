<?php
/**
 * Actualizar Bitácora (JSON-only, robusto, con validación de FKs y normalización de Turno)
 * Recibe (POST):
 *   IdBitacora (obligatorio)
 *   Turno, Novedades (obligatorios)
 *   IdFuncionario?, IdIngreso?, IdDispositivo?, IdVisitante? (opcionales, 0/'' => NULL)
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

require_once __DIR__ . '/conexion.php';
if (!class_exists('Conexion')) $send(['ok'=>false,'message'=>'No se halló clase Conexion']);
$cn = new Conexion();
$db = $cn->getConexion();
if (!($db instanceof mysqli)) $send(['ok'=>false,'message'=>'getConexion() no devolvió mysqli']);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db->set_charset('utf8mb4');

// Helpers enum turno
function norm($s){ $s=mb_strtolower(trim((string)$s),'UTF-8'); return strtr($s,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']); }
function get_enum_turnos(mysqli $db,string $table,string $column): array {
  $t=$db->real_escape_string($table); $c=$db->real_escape_string($column);
  $sql="SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$t' AND COLUMN_NAME='$c'";
  $res=$db->query($sql); if(!$res||$res->num_rows===0) return []; $row=$res->fetch_assoc(); $res->free();
  $type=$row['COLUMN_TYPE']; if (stripos($type,'enum(')!==0) return [];
  $inside=substr($type,5,-1); $vals=[]; foreach (explode(',', $inside) as $tok){ $vals[]=trim($tok," '"); } return $vals;
}
function map_turno(mysqli $db,string $table,string $col,string $input): string {
  if ($input==='') return '';
  $allowed=get_enum_turnos($db,$table,$col); if (empty($allowed)) return $input;
  $nin=norm($input);
  foreach ($allowed as $opt){ $no=norm($opt);
    if (strpos($nin,'manana')!==false && strpos($no,'manana')!==false) return $opt;
    if (strpos($nin,'tarde')!==false  && strpos($no,'tarde')!==false)  return $opt;
    if (strpos($nin,'noche')!==false  && strpos($no,'noche')!==false)  return $opt;
    if (strpos($nin,'dia')!==false    && strpos($no,'dia')!==false)    return $opt;
  }
  foreach ($allowed as $opt){ if ($opt===$input) return $opt; }
  return $allowed[0];
}

// --------- Entradas -----------
$IdBitacora = 0;
foreach (['IdBitacora','idBitacora','id'] as $k) {
  if (isset($_POST[$k]) && $_POST[$k] !== '') { $IdBitacora = (int)$_POST[$k]; break; }
}
$Turno     = isset($_POST['Turno'])     ? trim((string)$_POST['Turno'])     : '';
$Novedades = isset($_POST['Novedades']) ? trim((string)$_POST['Novedades']) : '';

$IdFuncionario = isset($_POST['IdFuncionario']) ? (int)$_POST['IdFuncionario'] : null;
$IdIngreso     = isset($_POST['IdIngreso'])     ? (int)$_POST['IdIngreso']     : null;
$IdDispositivo = isset($_POST['IdDispositivo']) ? (int)$_POST['IdDispositivo'] : null;
$IdVisitante   = isset($_POST['IdVisitante'])   ? (int)$_POST['IdVisitante']   : null;

// Normalizar: 0/negativos => NULL
foreach (['IdFuncionario','IdIngreso','IdDispositivo','IdVisitante'] as $k){
  if ($$k !== null && $$k <= 0) $$k = null;
}

if ($IdBitacora <= 0)                 $send(['ok'=>false,'message'=>'ID de bitácora inválido']);
if ($Turno === '')                     $send(['ok'=>false,'message'=>'Turno es requerido']);
if ($Novedades === '')                 $send(['ok'=>false,'message'=>'Novedades es requerido']);
if (mb_strlen($Novedades,'UTF-8')>500) $Novedades = mb_substr($Novedades,0,500,'UTF-8');

// --------- Comprobar existencia ----------
$st = $db->prepare("SELECT 1 FROM `bitacora` WHERE `IdBitacora`=?");
$st->bind_param('i',$IdBitacora);
$st->execute(); $st->store_result();
if ($st->num_rows===0){ $st->close(); $send(['ok'=>false,'message'=>"La bitácora $IdBitacora no existe"]); }
$st->close();

// --------- Normalizar Turno contra el ENUM real ----------
$Turno = map_turno($db,'bitacora','TurnoBitacora',$Turno);

// --------- Helpers FK ----------
function fk_exists(mysqli $db, string $table, string $pk, int $id): bool {
  $sql="SELECT 1 FROM `$table` WHERE `$pk`=? LIMIT 1";
  $st=$db->prepare($sql);
  $st->bind_param('i',$id);
  $st->execute(); $st->store_result();
  $ok=$st->num_rows>0; $st->close();
  return $ok;
}
if ($IdFuncionario !== null && !fk_exists($db,'funcionario','IdFuncionario',$IdFuncionario)){
  $send(['ok'=>false,'message'=>"El Funcionario con ID $IdFuncionario no existe"]);
}
if ($IdIngreso !== null && !fk_exists($db,'ingreso','IdIngreso',$IdIngreso)){
  $send(['ok'=>false,'message'=>"El Ingreso con ID $IdIngreso no existe"]);
}
if ($IdDispositivo !== null && !fk_exists($db,'dispositivo','IdDispositivo',$IdDispositivo)){
  $send(['ok'=>false,'message'=>"El Dispositivo con ID $IdDispositivo no existe"]);
}
if ($IdVisitante !== null && !fk_exists($db,'visitante','IdVisitante',$IdVisitante)){
  $send(['ok'=>false,'message'=>"El Visitante con ID $IdVisitante no existe"]);
}

// --------- UPDATE ----------
$sql = "UPDATE `bitacora`
        SET `TurnoBitacora`=?,
            `NovedadesBitacora`=?,
            `IdFuncionario`=?,
            `IdIngreso`=?,
            `IdDispositivo`=?,
            `IdVisitante`=?
        WHERE `IdBitacora`=?";

$st = $db->prepare($sql);
$st->bind_param(
  'ssiiiii',
  $Turno,
  $Novedades,
  $IdFuncionario,
  $IdIngreso,
  $IdDispositivo,
  $IdVisitante,
  $IdBitacora
);
$st->execute();
$aff = $st->affected_rows;
$st->close();

$send(['ok'=>true,'message'=> $aff>0 ? 'Bitácora actualizada' : 'Sin cambios','updated'=>$aff]);
