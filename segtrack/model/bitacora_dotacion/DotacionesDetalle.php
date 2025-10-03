<?php
// models/DotacionesDetalle.php (A-4.2 con fallback a dotlista)
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accion = isset($_GET['accion']) ? strtolower(trim($_GET['accion'])) : '';
if ($id <= 0) { http_response_code(400); echo '<!doctype html><meta charset="utf-8"><p>Falta id.</p>'; exit; }
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ver Dotación</title>
<style>.badge{display:inline-block;padding:.35em .6em;font-size:.85rem;border-radius:.35rem}
.badge-secondary{background:#6c757d;color:#fff}.badge-success{background:#28a745;color:#fff}
.badge-primary{background:#007bff;color:#fff}.badge-warning{background:#ffc107;color:#212529}
.badge-danger{background:#dc3545;color:#fff}.muted{color:#6c757d}</style>
</head><body>
<?php if (file_exists(__DIR__.'/parte_superior.php')) require_once __DIR__.'/parte_superior.php'; ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center mb-3">
    <h4 class="mb-0">Ver Dotación</h4>
    <a href="DotacionesLista.php" class="btn btn-sm btn-secondary ml-auto"><i class="fas fa-arrow-left"></i> Volver</a>
  </div>
  <div class="card"><div class="card-header bg-primary text-white py-2"><strong>Información de la Dotación</strong></div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-2"><span class="muted d-block">ID</span><span id="d-id">—</span></div>
          <div class="mb-2"><span class="muted d-block">Nombre</span><span id="d-nombre">—</span></div>
          <div class="mb-2"><span class="muted d-block">Estado</span><span id="d-estado"><span class="badge badge-secondary">N/D</span></span></div>
          <div class="mb-2"><span class="muted d-block">Novedad</span><span id="d-novedad">—</span></div>
        </div>
        <div class="col-md-6">
          <div class="mb-2"><span class="muted d-block">Código</span><span id="d-codigo">—</span></div>
          <div class="mb-2"><span class="muted d-block">Tipo</span><span id="d-tipo">—</span></div>
        </div>
      </div>
      <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-warning mr-2" data-toggle="modal" data-target="#modalEntregar"><i class="fas fa-hand-holding"></i> Entregar</button>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalRecibir"><i class="fas fa-undo"></i> Recibir</button>
      </div>
      <div id="detalle-error" class="text-danger mt-2" style="display:none"></div>
    </div>
  </div>
</div>

<!-- Modal Entregar -->
<div class="modal fade" id="modalEntregar" tabindex="-1" role="dialog" aria-labelledby="mEntregarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formEntregar" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="mEntregarLabel">Entregar Dotación</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="ent-id" value="<?php echo (int)$id; ?>">
        <div class="form-group">
          <label for="ent-func">ID Funcionario <span class="text-danger">*</span></label>
          <input type="number" id="ent-func" name="funcionario" class="form-control" min="1" required>
          <div class="invalid-feedback">Ingresa un ID válido.</div>
        </div>
        <div class="form-group"><label for="ent-nov">Observación (opcional)</label>
          <textarea id="ent-nov" name="novedad" class="form-control" rows="3" placeholder="Observaciones de la entrega..."></textarea></div>
        <div id="ent-msg" class="small"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
        <button type="submit" id="btnEntregar" class="btn btn-warning">Confirmar Entrega</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Recibir -->
<div class="modal fade" id="modalRecibir" tabindex="-1" role="dialog" aria-labelledby="mRecibirLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formRecibir" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="mRecibirLabel">Recibir Dotación</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="rec-id" value="<?php echo (int)$id; ?>">
        <div class="form-group">
          <label for="rec-estado">Estado físico al recibir</label>
          <select id="rec-estado" name="estado" class="form-control">
            <option value="">(Sin cambio)</option>
            <option value="Buen estado">Buen estado</option>
            <option value="Regular">Regular</option>
            <option value="Dañado">Dañado</option>
          </select>
        </div>
        <div class="form-group"><label for="rec-nov">Observación (opcional)</label>
          <textarea id="rec-nov" name="novedad" class="form-control" rows="3" placeholder="Observaciones de la recepción..."></textarea></div>
        <div id="rec-msg" class="small"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
        <button type="submit" id="btnRecibir" class="btn btn-success">Confirmar Recepción</button>
      </div>
    </form>
  </div>
</div>

<?php if (file_exists(__DIR__.'/parte_inferior.php')) require_once __DIR__.'/parte_inferior.php'; ?>

<script>
const ID_DOT = <?php echo (int)$id; ?>;
const ACCION = <?php echo json_encode($accion, JSON_UNESCAPED_UNICODE); ?>;
// Primer intento: detalle “oficial”
const API_DET1 = '../backed/dotdetalle.php?id=' + ID_DOT;
// Fallback: usamos el listado normalizado
const API_DET2 = '../backed/dotlista.php?id=' + ID_DOT + '&pageSize=1';

const API_ENT = '../backed/dotentregar.php';
const API_REC = '../backed/dotrecibir.php';

function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
function badge(text){ const t=(text||'').toLowerCase();
  if (t.includes('entreg')) return '<span class="badge badge-primary">Entregado</span>';
  if (t.includes('dispon')) return '<span class="badge badge-success">Disponible</span>';
  if (t.includes('noved'))  return '<span class="badge badge-danger">Novedad</span>';
  return '<span class="badge badge-secondary">N/D</span>'; }
function cicloDesde(fisico, fEnt, fDev){
  const fis = (fisico||'').toLowerCase();
  const isNov = fis.includes('dañ') || fis.includes('aver');
  const ent = fEnt ? new Date(fEnt) : null;
  const dev = fDev ? new Date(fDev) : null;
  if (isNov) return 'Novedad';
  if (ent && (!dev || dev < ent)) return 'Entregado';
  return 'Disponible';
}
function setTitles(det){
  const etiqueta = (det.codigo ? det.codigo : ('DOT-'+String(det.id).padStart(6,'0'))) + (det.nombre ? (' — '+det.nombre) : (det.tipo ? (' — '+det.tipo) : ''));
  document.getElementById('mEntregarLabel').textContent = 'Entregar ' + etiqueta;
  document.getElementById('mRecibirLabel').textContent  = 'Recibir ' + etiqueta;
}
function pintar(d){ // normalización y pintado
  const id      = d.id ?? d.IdDotacion ?? ID_DOT;
  const codigo  = d.codigo ?? d.CodigoDotacion ?? d.Codigo ?? (id?('DOT-'+String(id).padStart(6,'0')):'');
  const nombre  = d.nombre ?? d.NombreDotacion ?? d.Nombre ?? d.Descripcion ?? (d.tipo ?? d.TipoDotacion ?? '');
  const tipo    = d.tipo ?? d.TipoDotacion ?? d.Tipo ?? '';
  const fisico  = d.estfis ?? d.EstadoDotacion ?? d.estado ?? d.Estado ?? '';
  const fEnt    = d.fent ?? d.FechaEntrega ?? d.fechaentrega ?? '';
  const fDev    = d.fdev ?? d.FechaDevolucion ?? d.fechadevolucion ?? '';
  const novedad = d.novedad ?? d.NovedadDotacion ?? d.Novedad ?? d.Observacion ?? '';

  document.getElementById('d-id').textContent = id || '—';
  document.getElementById('d-codigo').textContent = codigo || '—';
  document.getElementById('d-nombre').textContent = nombre || '—';
  document.getElementById('d-tipo').textContent   = tipo || '—';
  document.getElementById('d-estado').innerHTML   = badge(cicloDesde(fisico,fEnt,fDev));
  document.getElementById('d-novedad').textContent= novedad || '—';

  // asegura ids ocultos por si el primer fetch falló
  document.getElementById('ent-id').value = id;
  document.getElementById('rec-id').value = id;

  setTitles({id, codigo, nombre, tipo});
}

async function tryJson(url){
  const r = await fetch(url, { headers:{'Accept':'application/json'} });
  const t = await r.text();
  try { return JSON.parse(t); } catch(e){ return { ok:false, raw:t }; }
}

async function loadDetalle(){
  let j = await tryJson(API_DET1);
  let data = null;
  if(j && j.ok){
    data = j.data || j.row || j;
  }
  if(!data || (Object.keys(data).length===0)){
    // Fallback al listado por id
    j = await tryJson(API_DET2);
    if(j && j.ok && Array.isArray(j.rows) && j.rows.length){
      // como el listado no trae novedad completa, dejamos campo vacío (igual se verá en otras vistas)
      data = j.rows[0];
    }
  }
  if(!data){
    document.getElementById('detalle-error').style.display='block';
    document.getElementById('detalle-error').textContent='No se pudo cargar el detalle de la dotación.';
    return;
  }
  pintar(data);

  // Auto-abrir si viene ?accion=
  if (ACCION === 'entregar' && window.jQuery && $('#modalEntregar').length) {
    $('#modalEntregar').modal({ show:true, backdrop:'static' });
  } else if (ACCION === 'recibir' && window.jQuery && $('#modalRecibir').length) {
    $('#modalRecibir').modal({ show:true, backdrop:'static' });
  }
}

// Submits
document.addEventListener('DOMContentLoaded', function(){
  loadDetalle();

  document.getElementById('formEntregar').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new URLSearchParams();
    fd.append('id', document.getElementById('ent-id').value || ID_DOT);
    fd.append('funcionario', document.getElementById('ent-func').value);
    fd.append('novedad', document.getElementById('ent-nov').value);
    const b = document.getElementById('btnEntregar'); b.disabled=true;
    try{
      const r = await fetch('../backed/dotentregar.php',{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:fd.toString()});
      const j = await r.json(); if(!j.ok) throw new Error(j.message||'Error al entregar');
      document.getElementById('ent-msg').innerHTML='<span class="text-success">Entrega registrada.</span>';
      setTimeout(()=>location.reload(),700);
    }catch(err){ document.getElementById('ent-msg').innerHTML='<span class="text-danger">'+esc(err.message||err)+'</span>'; }
    finally{ b.disabled=false; }
  });

  document.getElementById('formRecibir').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new URLSearchParams();
    fd.append('id', document.getElementById('rec-id').value || ID_DOT);
    fd.append('estado', document.getElementById('rec-estado').value);
    fd.append('novedad', document.getElementById('rec-nov').value);
    const b = document.getElementById('btnRecibir'); b.disabled=true;
    try{
      const r = await fetch('../backed/dotrecibir.php',{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:fd.toString()});
      const j = await r.json(); if(!j.ok) throw new Error(j.message||'Error al recibir');
      document.getElementById('rec-msg').innerHTML='<span class="text-success">'+esc(j.message||"Recepción registrada.")+'</span>';
      setTimeout(()=>location.reload(),700);
    }catch(err){ document.getElementById('rec-msg').innerHTML='<span class="text-danger">'+esc(err.message||err)+'</span>'; }
    finally{ b.disabled=false; }
  });
});
</script>
</body></html>
