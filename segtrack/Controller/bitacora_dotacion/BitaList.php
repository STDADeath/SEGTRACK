<?php
/**
 * BitaList.php — Lista de bitácora (JSON + CSV)
 * GET: q, turno, funcionario, desde, hasta, page, size, sort, dir, csv=1
 */
declare(strict_types=1);
date_default_timezone_set('America/Bogota');

ob_start();
ini_set('display_errors','0');
error_reporting(E_ALL);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$send_json = function(array $p){
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($p, JSON_UNESCAPED_UNICODE);
  exit;
};
$send_csv = function(string $name, array $rows){
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.$name.'"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['IdBitacora','Fecha','Turno','Novedades','IdFuncionario','IdIngreso','IdDispositivo']);
  foreach($rows as $r){
    fputcsv($out, [
      $r['IdBitacora'] ?? $r['ID'] ?? $r['id'] ?? '',
      $r['Fecha'] ?? $r['fecha'] ?? '',
      $r['Turno'] ?? $r['turno'] ?? '',
      $r['Novedades'] ?? $r['novedades'] ?? '',
      $r['IdFuncionario'] ?? $r['id_funcionario'] ?? '',
      $r['IdIngreso'] ?? $r['id_ingreso'] ?? '',
      $r['IdDispositivo'] ?? $r['id_dispositivo'] ?? ''
    ]);
  }
  fclose($out);
  exit;
};

// manejadores de error
set_exception_handler(function(Throwable $e) use ($send_json){ $send_json(['ok'=>false,'message'=>$e->getMessage()]); });
register_shutdown_function(function() use ($send_json){
  $e = error_get_last();
  if ($e && in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true)){
    $send_json(['ok'=>false,'message'=>'Fatal: '.$e['message']]);
  }
});

// ========= localizar conexion.php de forma tolerante =========
(function() use ($send_json){
  $candidates = [
    __DIR__ . '/conexion.php',
    __DIR__ . '/../backed/conexion.php',
    __DIR__ . '/../models/conexion.php',
    dirname(__DIR__) . '/backed/conexion.php',
    dirname(__DIR__) . '/models/conexion.php',
  ];
  $found = false;
  foreach ($candidates as $c) {
    if (is_file($c)) { require_once $c; $found = true; break; }
  }
  if (!$found) { $send_json(['ok'=>false,'message'=>'No se encontró conexion.php']); }
})();

if (!class_exists('Conexion')) $send_json(['ok'=>false,'message'=>'No se encontró clase Conexion']);
$cn = new Conexion();
$db = $cn->getConexion();
if (!($db instanceof mysqli)) $send_json(['ok'=>false,'message'=>'getConexion() no devolvió mysqli']);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db->set_charset('utf8mb4');

// helpers
function parse_date($s): ?string {
  $s = trim((string)$s);
  if ($s==='') return null;
  if (preg_match('~^(\d{2})/(\d{2})/(\d{4})$~',$s,$m)) return sprintf('%04d-%02d-%02d',(int)$m[3],(int)$m[2],(int)$m[1]);
  if (preg_match('~^\d{4}-\d{2}-\d{2}$~',$s)) return $s;
  return null;
}
function norm($s){ $s=mb_strtolower(trim((string)$s),'UTF-8'); return strtr($s,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']); }
function get_enum_turnos(mysqli $db,string $table,string $column): array {
  $t=$db->real_escape_string($table); $c=$db->real_escape_string($column);
  $sql="SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
        AND TABLE_NAME='$t' AND COLUMN_NAME='$c'";
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

// columnas reales
$table='bitacora'; $cols=[];
if ($res=$db->query("DESCRIBE `$table`")){ while($r=$res->fetch_assoc()){ $cols[$r['Field']]=true; } $res->free(); }
$C_ID    = isset($cols['IdBitacora'])       ? 'IdBitacora'       : 'id';
$C_TURNO = isset($cols['TurnoBitacora'])    ? 'TurnoBitacora'    : 'turno';
$C_NOV   = isset($cols['NovedadesBitacora'])? 'NovedadesBitacora': 'novedades';
$C_FECHA = isset($cols['FechaBitacora'])    ? 'FechaBitacora'    : 'fecha';
$C_FUN   = isset($cols['IdFuncionario'])    ? 'IdFuncionario'    : 'id_funcionario';
$C_ING   = isset($cols['IdIngreso'])        ? 'IdIngreso'        : 'id_ingreso';
$C_DIS   = isset($cols['IdDispositivo'])    ? 'IdDispositivo'    : 'id_dispositivo';

// filtros
$q            = trim($_GET['q'] ?? '');
$turno_filter= trim($_GET['turno'] ?? '');
$funcionario = trim($_GET['funcionario'] ?? '');
$desde       = parse_date($_GET['desde'] ?? '');
$hasta       = parse_date($_GET['hasta'] ?? '');
$page        = max(1,(int)($_GET['page'] ?? 1));
$size        = (int)($_GET['size'] ?? 10); if($size<5)$size=5; if($size>100)$size=100;
$sort        = strtolower(trim($_GET['sort'] ?? 'fecha'));
$dir         = strtolower(trim($_GET['dir']  ?? 'desc'))==='asc'?'ASC':'DESC';

// WHERE
$conds=[]; $params=[]; $types='';
if ($q!==''){ $conds[]="`$C_NOV` LIKE ?"; $params[]="%$q%"; $types.='s'; }
if ($turno_filter!=='' && strtolower($turno_filter)!=='(todos)'){
  $tv=map_turno($db,$table,$C_TURNO,$turno_filter); $conds[]="`$C_TURNO` = ?"; $params[]=$tv; $types.='s';
}
if ($funcionario!=='' && ctype_digit($funcionario)){ $conds[]="`$C_FUN` = ?"; $params[]=(int)$funcionario; $types.='i'; }
if ($desde){ $conds[]="`$C_FECHA` >= ?"; $params[]=$desde.' 00:00:00'; $types.='s'; }
if ($hasta){ $conds[]="`$C_FECHA` <= ?"; $params[]=$hasta.' 23:59:59'; $types.='s'; }
$where = empty($conds) ? '' : ('WHERE '.implode(' AND ',$conds));

// total
$sqlCount="SELECT COUNT(1) AS c FROM `$table` $where";
$st=$db->prepare($sqlCount); if($types!=='') $st->bind_param($types, ...$params);
$st->execute(); $r=$st->get_result()->fetch_assoc(); $st->close();
$total=(int)($r['c'] ?? 0);

// orden
$sortable = [ 'id' => $C_ID, 'fecha'=>$C_FECHA, 'turno'=>$C_TURNO, 'funcionario'=>$C_FUN ];
$orderBy = "ORDER BY `".($sortable[$sort] ?? $C_FECHA)."` $dir, `$C_ID` $dir";

// CSV?
$isCsv = isset($_GET['csv']) && (string)$_GET['csv'] === '1';

// datos
if ($isCsv) {
  $sql = "SELECT `$C_ID` AS ID, `$C_FECHA` AS Fecha, `$C_TURNO` AS Turno,
                 `$C_NOV` AS Novedades, `$C_FUN` AS IdFuncionario,
                 `$C_ING` AS IdIngreso, `$C_DIS` AS IdDispositivo
          FROM `$table` $where $orderBy";
  $st=$db->prepare($sql);
  if($types!=='') $st->bind_param($types, ...$params);
} else {
  $offset = ($page-1)*$size;
  $sql = "SELECT `$C_ID` AS ID, `$C_FECHA` AS Fecha, `$C_TURNO` AS Turno,
                 `$C_NOV` AS Novedades, `$C_FUN` AS IdFuncionario,
                 `$C_ING` AS IdIngreso, `$C_DIS` AS IdDispositivo
          FROM `$table` $where $orderBy
          LIMIT ?, ?";
  $types2 = $types.'ii'; $params2=$params; $params2[]=$offset; $params2[]=$size;
  $st=$db->prepare($sql); $st->bind_param($types2, ...$params2);
}

$st->execute(); $res=$st->get_result();
$rows=[];
while($row=$res->fetch_assoc()){
  $id = (int)$row['ID'];
  $rows[] = [
    'id'              => $id,
    'fecha'           => $row['Fecha'],
    'turno'           => $row['Turno'],
    'novedades'       => $row['Novedades'],
    'id_funcionario'  => $row['IdFuncionario'],
    'id_ingreso'      => $row['IdIngreso'],
    'id_dispositivo'  => $row['IdDispositivo'],
    'ID'              => $id,
    'Fecha'           => $row['Fecha'],
    'Turno'           => $row['Turno'],
    'Novedades'       => $row['Novedades'],
    'IdFuncionario'   => $row['IdFuncionario'],
    'IdIngreso'       => $row['IdIngreso'],
    'IdDispositivo'   => $row['IdDispositivo'],
    'IdBitacora'      => $id
  ];
}
$st->close();

if ($isCsv) {
  $fname = 'bitacora_'.date('Ymd_His').'.csv';
  $send_csv($fname, $rows);
}

$send_json(['ok'=>true,'message'=>'','rows'=>$rows,'total'=>$total]);
