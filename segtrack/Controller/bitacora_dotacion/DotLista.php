<?php
// backed/dotlista.php (A-4.2) - estado de ciclo calculado + filtro id
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $cn = (new Conexion())->getConexion();
    if (!$cn) throw new Exception('Sin conexión a base de datos');
    $cn->set_charset('utf8mb4');

    $idFilter = isset($_GET['id']) ? (int)$_GET['id'] : 0; // << NUEVO
    $q        = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $tipo     = isset($_GET['tipo']) ? trim((string)$_GET['tipo']) : '';
    $estadoIn = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = (int)($_GET['pageSize'] ?? 10); $pageSize = $pageSize>0 ? min($pageSize,100) : 10;
    $sort     = strtolower((string)($_GET['sort'] ?? 'nombre'));
    $dir      = (strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc') ? 'DESC' : 'ASC';
    $debugMap = isset($_GET['debug']) && $_GET['debug']!=='0' && $_GET['debug']!=='';

    // Tabla y columnas
    $tabs=[]; $rt=$cn->query("SHOW TABLES"); while($r=$rt->fetch_array(MYSQLI_NUM)){$tabs[]=$r[0];}
    $tab=null; foreach(['dotacion','dotaciones','Dotacion','Dotaciones'] as $t){ if(in_array($t,$tabs,true)){ $tab=$t; break; } }
    if(!$tab) throw new Exception('No se encontró la tabla de dotaciones.');

    $cols=[]; $rc=$cn->query("SHOW COLUMNS FROM `$tab`"); while($r=$rc->fetch_assoc()){$cols[]=$r;}
    $exists=function($name)use($cols){ foreach($cols as $c){ if(strcasecmp($c['Field'],$name)===0) return $c['Field']; } return null; };
    $smart=function(array $tokens)use($cols){ $b=null;$sB=0; foreach($cols as $c){ $n=strtolower($c['Field']); $s=0;
      foreach($tokens as $t){ $t=strtolower($t); if(preg_match('/'.preg_quote($t,'/').'$/',$n))$s+=3; if(preg_match('/\b'.preg_quote($t,'/').'\b/',$n))$s+=2; if(strpos($n,$t)!==false)$s+=1; }
      if(strpos($n,'dotacion')!==false)$s+=2; if(preg_match('/\bid/',$n))$s-=2; if($s>$sB){$sB=$s;$b=$c['Field'];}} return $sB>0?$b:null; };

    $colId      = $exists('IdDotacion') ?: $exists('id_dotacion') ?: $exists('iddotacion') ?: $exists('id');
    $colCodigo  = $exists('CodigoDotacion') ?: $exists('Codigo') ?: $exists('Serial') ?: $exists('QrDispositivo');
    $colNombre  = $exists('NombreDotacion') ?: $exists('Nombre') ?: $exists('Descripcion');
    $colTipo    = $exists('TipoDotacion') ?: $exists('Tipo') ?: $exists('Categoria');
    $colEstF    = $exists('EstadoDotacion') ?: $exists('Estado') ?: $exists('Estatus'); // FÍSICO
    $colNov     = $exists('NovedadDotacion') ?: $exists('Novedad') ?: $exists('Observacion') ?: $exists('Observaciones');
    $colFEnt    = $exists('FechaEntrega') ?: $exists('fechaentrega') ?: $smart(['fechaentrega']);
    $colFDev    = $exists('FechaDevolucion') ?: $exists('fechadevolucion') ?: $smart(['fechadevolucion']);

    if(!$colId) throw new Exception('Falta columna ID.');

    $sel = ["d.`$colId` AS id"];
    if($colCodigo) $sel[]="d.`$colCodigo` AS codigo";
    if($colNombre) $sel[]="d.`$colNombre` AS nombre";
    if($colTipo)   $sel[]="d.`$colTipo` AS tipo";
    if($colEstF)   $sel[]="d.`$colEstF` AS estfis";
    if($colNov)    $sel[]="d.`$colNov` AS novedad";
    if($colFEnt)   $sel[]="d.`$colFEnt` AS fent";
    if($colFDev)   $sel[]="d.`$colFDev` AS fdev";
    $selectSQL = implode(', ', $sel);

    // WHERE
    $where=[]; $types=''; $vals=[];
    if($idFilter>0){ // << NUEVO
        $where[]="d.`$colId` = ?"; $types.='i'; $vals[]=$idFilter;
        $page=1; $pageSize=1; // garantizamos 1 fila
    }
    if($q!==''){
        $or=[]; foreach([$colNombre,$colCodigo,$colTipo] as $c){ if($c){ $or[]="d.`$c` LIKE ?"; $types.='s'; $vals[]='%'.$q.'%'; } }
        if($or) $where[]='('.implode(' OR ',$or).')';
    }
    if($tipo!=='' && $colTipo){ $where[]="d.`$colTipo`=?"; $types.='s'; $vals[]=$tipo; }

    // filtro estado: ciclo o físico
    $e = strtolower($estadoIn);
    $isCiclo = in_array($e, ['entregado','disponible','novedad'], true);
    if($estadoIn!==''){
        if($isCiclo){
            $parts=[];
            if($e==='novedad' && $colEstF){
                $parts[]="(LOWER(d.`$colEstF`) LIKE '%dañ%' OR LOWER(d.`$colEstF`) LIKE '%aver%')";
            } elseif($e==='entregado' && $colFEnt){
                $cond = "(d.`$colFEnt` IS NOT NULL";
                if($colFDev){ $cond .= " AND (d.`$colFDev` IS NULL OR d.`$colFDev` < d.`$colFEnt`)"; }
                $cond .= ")";
                $parts[]=$cond;
            } elseif($e==='disponible'){
                $sub = [];
                if($colEstF) $sub[]="(LOWER(d.`$colEstF`) LIKE '%dañ%' OR LOWER(d.`$colEstF`) LIKE '%aver%')";
                if($colFEnt){
                    $sub2="(d.`$colFEnt` IS NOT NULL";
                    if($colFDev) $sub2.=" AND (d.`$colFDev` IS NULL OR d.`$colFDev` < d.`$colFEnt`)";
                    $sub2.=")";
                    $sub[]=$sub2;
                }
                if($sub){ $parts[]='NOT ('.implode(' OR ',$sub).')'; }
            }
            if($parts) $where[]='('.implode(' AND ',$parts).')';
        } else {
            if($colEstF){ $where[]="d.`$colEstF`=?"; $types.='s'; $vals[]=$estadoIn; }
        }
    }
    $whereSQL = $where ? ('WHERE '.implode(' AND ',$where)) : '';

    // ORDER BY
    $sortMap = [
        'id'     => $colId,
        'codigo' => $colCodigo ?: $colId,
        'nombre' => $colNombre ?: ($colTipo ?: $colId),
        'tipo'   => $colTipo   ?: $colId,
        'estado' => $colFEnt ? $colFEnt : ($colEstF ?: $colId),
    ];
    $sortCol = $sortMap[$sort] ?? ($colNombre ?: $colTipo ?: $colId);
    $orderSQL = "ORDER BY d.`$sortCol` $dir";

    // TOTAL
    if($idFilter>0){
        $total=1; // << si viene id, devolvemos una fila
    } else {
        $stC=$cn->prepare("SELECT COUNT(1) AS total FROM `$tab` d $whereSQL");
        if($types!=='') $stC->bind_param($types, ...$vals);
        $stC->execute(); $total=(int)($stC->get_result()->fetch_assoc()['total'] ?? 0); $stC->close();
    }

    // DATA
    $offset=($page-1)*$pageSize;
    $stD=$cn->prepare("SELECT $selectSQL FROM `$tab` d $whereSQL $orderSQL LIMIT ? OFFSET ?");
    $typesData=$types.'ii'; $valsData=$vals; $valsData[]=$pageSize; $valsData[]=$offset;
    $stD->bind_param($typesData, ...$valsData); $stD->execute(); $res=$stD->get_result();

    $rows=[];
    while($r=$res->fetch_assoc()){
        $id=(int)$r['id'];
        $codigo=$r['codigo'] ?? ''; if($codigo==='' && $id>0) $codigo=sprintf('DOT-%06d',$id);
        $nombre=$r['nombre'] ?? '';
        $tipoV=$r['tipo'] ?? '';
        $estFis=$r['estfis'] ?? '';
        $fent =$r['fent'] ?? null; $fdev=$r['fdev'] ?? null;
        $tsEnt = ($fent && $fent!=='0000-00-00 00:00:00') ? strtotime($fent) : null;
        $tsDev = ($fdev && $fdev!=='0000-00-00 00:00:00') ? strtotime($fdev) : null;

        $isNov = $estFis!=='' && (stripos($estFis,'dañ')!==false || stripos($estFis,'aver')!==false);
        if($isNov)        $estado='Novedad';
        elseif($tsEnt && (!$tsDev || $tsDev < $tsEnt)) $estado='Entregado';
        else              $estado='Disponible';

        if($nombre==='') $nombre=$tipoV;

        $rows[]=[
            'id'=>$id,'codigo'=>$codigo,'nombre'=>$nombre,'tipo'=>$tipoV,
            'estado'=>$estado,'novedad'=>$r['novedad'] ?? '',
            'fent'=>$fent,'fdev'=>$fdev,'estfis'=>$estFis // útil para ficha
        ];
    }
    $stD->close();

    $out=['ok'=>true,'rows'=>$rows,'total'=>$total];
    if($debugMap){ $out['map']=['table'=>$tab,'id'=>$colId,'codigo'=>$colCodigo ?: '(auto DOT-...)',
        'nombre'=>$colNombre ?: '(fallback tipo)','tipo'=>$colTipo,
        'estado_fisico'=>$colEstF,'fecha_entrega'=>$colFEnt,'fecha_devolucion'=>$colFDev]; }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch(Throwable $e){
    echo json_encode(['ok'=>false,'rows'=>[],'total'=>0,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
