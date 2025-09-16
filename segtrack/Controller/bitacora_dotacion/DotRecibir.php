<?php
// backed/dotrecibir.php (A-4.1)
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $cnx = (new Conexion())->getConexion();
    if (!$cnx) throw new Exception('Sin conexión a base de datos');
    $cnx->set_charset('utf8mb4');

    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $fisicoIn  = isset($_POST['estado']) ? trim((string)$_POST['estado']) : ''; // físico: "Buen estado" | "Regular" | "Dañado"
    $novedadIn = isset($_POST['novedad']) ? trim((string)$_POST['novedad']) : '';
    if ($id <= 0) throw new Exception('ID de dotación inválido.');

    // --- tabla/columnas
    $tabs=[]; $rs=$cnx->query("SHOW TABLES"); while($r=$rs->fetch_array(MYSQLI_NUM)){$tabs[]=$r[0];}
    $tabDot=null; foreach($tabs as $t){ if(preg_match('/^dotacion(es)?$/i',$t)){ $tabDot=$t; break; } }
    if(!$tabDot) throw new Exception('No existe la tabla de dotaciones.');

    $cols=[]; $rc=$cnx->query("SHOW COLUMNS FROM `$tabDot`"); while($r=$rc->fetch_assoc()){ $cols[]=$r; }
    $find=function($names)use($cols){ $lc=array_map(fn($c)=>strtolower($c['Field']),$cols);
      foreach((array)$names as $n){ $i=array_search(strtolower($n),$lc,true); if($i!==false)return $cols[$i]['Field']; } return null; };
    $smart=function(array $tokens)use($cols){ $b=null;$sB=0; foreach($cols as $c){ $n=strtolower($c['Field']); $s=0;
      foreach($tokens as $t){ $t=strtolower($t); if(preg_match('/'.preg_quote($t,'/').'$/',$n))$s+=3; if(preg_match('/\b'.preg_quote($t,'/').'\b/',$n))$s+=2; if(strpos($n,$t)!==false)$s+=1; }
      if(strpos($n,'dotacion')!==false)$s+=2; if(preg_match('/\bid/',$n))$s-=2; if($s>$sB){$sB=$s;$b=$c['Field'];}} return $sB>0?$b:null; };

    $colId   = $find(['IdDotacion','id_dotacion','iddotacion','id']) ?? $smart(['iddotacion','id']);
    $colEstF = $find(['EstadoDotacion','Estado','Estatus']) ?? $smart(['estadodotacion','estado','estatus']); // FÍSICO
    $colNov  = $find(['NovedadDotacion','Novedad','Observacion','Observaciones']) ?? $smart(['novedad','observacion','observaciones']);
    $colFEnt = $find(['FechaEntrega','fechaentrega']) ?? $smart(['fechaentrega']);
    $colFDev = $find(['FechaDevolucion','fechadevolucion']) ?? $smart(['fechadevolucion']);
    $colFunc = $find(['IdFuncionario','idfuncionario']) ?? $smart(['idfuncionario']);
    if(!$colId || !$colFunc) throw new Exception('Faltan columnas clave (IdDotacion/IdFuncionario).');

    // ¿IdFuncionario permite NULL?
    $nullableFunc=false; foreach($cols as $c){ if($c['Field']===$colFunc){ $nullableFunc = (strtoupper((string)$c['Null'])==='YES'); break; } }

    // --- UPDATE (no escribimos estado de ciclo)
    $now=date('Y-m-d H:i:s'); $sets=[]; $types=''; $vals=[];
    if($colFDev){ $sets[]="`$colFDev`=?"; $types.='s'; $vals[]=$now; }
    if($nullableFunc){ $sets[]="`$colFunc`=NULL"; $notaFunc='IdFuncionario puesto en NULL.'; }
    else { $notaFunc='IdFuncionario conservado (columna NO NULL con FK).'; }

    // si viene estado físico desde el modal, lo actualizamos
    if($colEstF && $fisicoIn!==''){ $sets[]="`$colEstF`=?"; $types.='s'; $vals[]=$fisicoIn; }

    if($colNov && ($fisicoIn!=='' || $novedadIn!=='')){
      $txt = trim(($fisicoIn!=='' ? "Estado físico: $fisicoIn" : '') . (($fisicoIn!==''&&$novedadIn!=='')?'; ':'') . ($novedadIn!=='' ? "Comentario: $novedadIn" : ''));
      $append=sprintf("[%s] Recepción: %s",$now,$txt);
      $sets[]="`$colNov`=TRIM(CONCAT(COALESCE(`$colNov`,''), CASE WHEN COALESCE(`$colNov`,'')='' THEN '' ELSE '\n' END, ?))";
      $types.='s'; $vals[]=$append;
    }

    if(!$sets) throw new Exception('Nada por actualizar.');
    $sql="UPDATE `$tabDot` SET ".implode(', ',$sets)." WHERE `$colId`=?"; $types.='i'; $vals[]=$id;
    $up=$cnx->prepare($sql); $up->bind_param($types, ...$vals); $up->execute(); $up->close();

    echo json_encode(['ok'=>true,'message'=>'Recepción registrada correctamente. '.$notaFunc], JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){
    echo json_encode(['ok'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
