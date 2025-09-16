<?php
// models/DotacionesLista.php
// Vista de Dotaciones consumiendo ../backed/dotlista.php (A-2).
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>SEGTRACK — Dotaciones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Normalmente tu header ya carga Bootstrap/FontAwesome.
       Si NO los carga, descomenta estas líneas y ajusta rutas:
  <link rel="stylesheet" href="/segtrack/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="/segtrack/vendor/fontawesome/css/all.min.css">
  -->
  <style>
    .table thead th { cursor: pointer; }
    .badge-state { font-size: 90%; }
  </style>
</head>
<body>
<?php
  // Ajusta estas rutas si en tu proyecto están en otra carpeta
  require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid py-4">
  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><i class="fas fa-user-tie mr-2"></i>Dotaciones Registradas</h1>
    <div>
      <button id="btnExport" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-file-csv"></i> Exportar CSV
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <form id="filterForm" class="card card-body mb-3">
    <div class="form-row">
      <div class="col-md-4 mb-2">
        <label class="mb-1">Buscar</label>
        <input id="q" type="text" class="form-control" placeholder="Código, nombre o tipo...">
      </div>
      <div class="col-md-3 mb-2">
        <label class="mb-1">Tipo</label>
        <select id="tipo" class="form-control">
          <option value="">(Todos)</option>
        </select>
      </div>
      <div class="col-md-3 mb-2">
        <label class="mb-1">Estado</label>
        <select id="estado" class="form-control">
          <option value="">(Todos)</option>
        </select>
      </div>
      <div class="col-md-2 mb-2 d-flex align-items-end">
        <button class="btn btn-primary btn-block" type="submit">
          <i class="fas fa-search"></i> Filtrar
        </button>
      </div>
    </div>
  </form>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header py-2">
      <h6 class="m-0 font-weight-bold text-primary">Lista de Dotaciones</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0" id="tablaDotaciones">
          <thead class="thead-dark">
            <tr>
              <th data-sort="codigo">Código <i class="fas fa-sort fa-sm text-muted"></i></th>
              <th data-sort="nombre">Nombre <i class="fas fa-sort fa-sm text-muted"></i></th>
              <th data-sort="tipo">Tipo <i class="fas fa-sort fa-sm text-muted"></i></th>
              <th data-sort="estado">Estado <i class="fas fa-sort fa-sm text-muted"></i></th>
              <th>Novedad</th>
              <th style="width:160px;">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyRows">
            <tr><td colspan="6" class="text-center text-muted">Cargando…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex align-items-center">
        <small id="lblInfo" class="text-muted"></small>
        <div class="ml-auto">
          <ul id="paginacion" class="pagination pagination-sm mb-0"></ul>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/parte_inferior.php'; ?>

<script>
// =================== Config & Estado ===================
const API_URL = '../backed/dotlista.php'; // endpoint A-2

const state = {
  q: '', tipo: '', estado: '',
  page: 1, pageSize: 10,
  sort: 'nombre', dir: 'asc',
  total: 0, rows: []
};

// =================== Helpers UI ===================
function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

function badgeEstado(estado){
  if(!estado) return '<span class="badge badge-secondary badge-state">N/D</span>';
  const e = (estado+'').toLowerCase();
  if(e.includes('entreg')) return '<span class="badge badge-primary badge-state">Entregado</span>';
  if(e.includes('dispon') || e.includes('libre')) return '<span class="badge badge-success badge-state">Disponible</span>';
  if(e.includes('pend')) return '<span class="badge badge-warning badge-state">Pendiente</span>';
  if(e.includes('report') || e.includes('dañ') || e.includes('aver')) return '<span class="badge badge-danger badge-state">Novedad</span>';
  return `<span class="badge badge-light badge-state">${esc(estado)}</span>`;
}
function accionSegunEstado(estado){
  const e = (estado||'').toLowerCase();
  return e.includes('entreg') ? 'recibir' : 'entregar';
}
function qs(params){
  return Object.entries(params)
    .filter(([,v]) => v!=='' && v!=null)
    .map(([k,v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('&');
}
function setInfo(){
  const from = state.total===0 ? 0 : ((state.page-1)*state.pageSize + 1);
  const to   = Math.min(state.page*state.pageSize, state.total);
  document.getElementById('lblInfo').textContent = `Mostrando ${from}-${to} de ${state.total} registros`;
}

// Llena selects de Tipo/Estado a partir de datos cargados
function renderFiltersOptions(){
  const tipos = new Set(), estados = new Set();
  state.rows.forEach(r => {
    if(r.tipo) tipos.add(r.tipo);
    if(r.estado) estados.add(r.estado);
  });

  const selTipo = document.getElementById('tipo');
  const selEst  = document.getElementById('estado');

  const keep = (sel, keepVal) => {
    const v = sel.value || keepVal;
    while(sel.options.length>1) sel.remove(1);
    return v;
  };
  const curT = keep(selTipo, state.tipo);
  const curE = keep(selEst, state.estado);

  Array.from(tipos).sort().forEach(t=>{
    const o=document.createElement('option'); o.value=t; o.textContent=t; selTipo.appendChild(o);
  });
  Array.from(estados).sort().forEach(e=>{
    const o=document.createElement('option'); o.value=e; o.textContent=e; selEst.appendChild(o);
  });
  selTipo.value = curT; selEst.value = curE;
}

// =================== Render Tabla & Paginación ===================
function renderTable(){
  const tbody = document.getElementById('tbodyRows');
  if(!state.rows.length){
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>`;
    return;
  }
  tbody.innerHTML = state.rows.map(r=>{
    const accion = accionSegunEstado(r.estado);
    const urlVer = `DotacionesDetalle.php?id=${encodeURIComponent(r.id)}`;
    const urlAcc = `${urlVer}&accion=${accion}`;
    return `<tr>
      <td>${r.codigo ? esc(r.codigo) : '<span class="text-muted">—</span>'}</td>
      <td>${r.nombre ? esc(r.nombre) : '<span class="text-muted">—</span>'}</td>
      <td>${r.tipo   ? esc(r.tipo)   : '<span class="text-muted">—</span>'}</td>
      <td>${badgeEstado(r.estado)}</td>
      <td>${r.novedad ? esc(r.novedad) : '<span class="text-muted">—</span>'}</td>
      <td>
        <div class="btn-group btn-group-sm">
          <a class="btn btn-outline-secondary" href="${urlVer}">Ver</a>
          <a class="btn btn-primary text-white" href="${urlAcc}">${accion==='recibir'?'Recibir':'Entregar'}</a>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderPagination(){
  const ul = document.getElementById('paginacion');
  ul.innerHTML = '';
  const pages = Math.max(1, Math.ceil(state.total / state.pageSize));

  const add = (label, page, disabled=false, active=false)=>{
    const li = document.createElement('li');
    li.className = `page-item ${disabled?'disabled':''} ${active?'active':''}`;
    const a = document.createElement('a');
    a.className = 'page-link'; a.href = '#'; a.textContent = label;
    a.addEventListener('click', (e)=>{ e.preventDefault(); if(!disabled && state.page!==page){ state.page=page; loadData(); }});
    li.appendChild(a); ul.appendChild(li);
  };

  add('«', Math.max(1, state.page-1), state.page===1, false);
  const start = Math.max(1, state.page-2);
  const end   = Math.min(pages, state.page+2);
  for(let p=start; p<=end; p++) add(String(p), p, false, p===state.page);
  add('»', Math.min(pages, state.page+1), state.page===pages, false);
}

// =================== Data ===================
async function loadData(){
  const tbody = document.getElementById('tbodyRows');
  tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Cargando…</td></tr>`;

  const url = `${API_URL}?${qs({
    q: state.q, tipo: state.tipo, estado: state.estado,
    page: state.page, pageSize: state.pageSize,
    sort: state.sort, dir: state.dir
  })}`;

  try{
    const resp = await fetch(url, { headers: { 'Accept':'application/json' }});
    const json = await resp.json();
    if(!json.ok) throw new Error(json.message || 'Error al obtener datos');

    state.rows = json.rows || [];
    state.total = json.total || 0;

    renderTable();
    renderPagination();
    setInfo();
    renderFiltersOptions();

  }catch(err){
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${esc(err.message || err)}</td></tr>`;
    document.getElementById('paginacion').innerHTML='';
    document.getElementById('lblInfo').textContent='';
  }
}

// =================== Eventos ===================
document.getElementById('filterForm').addEventListener('submit', (e)=>{
  e.preventDefault();
  state.q = document.getElementById('q').value.trim();
  state.tipo = document.getElementById('tipo').value;
  state.estado = document.getElementById('estado').value;
  state.page = 1;
  loadData();
});

document.querySelectorAll('#tablaDotaciones thead th[data-sort]').forEach(th=>{
  th.addEventListener('click', ()=>{
    const s = th.getAttribute('data-sort');
    if(state.sort === s){ state.dir = (state.dir==='asc')?'desc':'asc'; }
    else { state.sort = s; state.dir='asc'; }
    state.page = 1;
    loadData();
  });
});

document.getElementById('btnExport').addEventListener('click', ()=>{
  if(!state.rows.length){ alert('No hay datos para exportar.'); return; }
  const headers = ['ID','Código','Nombre','Tipo','Estado','Novedad'];
  const lines = [headers.join(',')];
  state.rows.forEach(r=>{
    const vals = [
      r.id,
      (r.codigo||'').replace(/,/g,' '),
      (r.nombre||'').replace(/,/g,' '),
      (r.tipo||'').replace(/,/g,' '),
      (r.estado||'').replace(/,/g,' '),
      (r.novedad||'').replace(/[\r\n,]+/g,' ').trim()
    ].map(v => `"${String(v).replace(/"/g,'""')}"`);
    lines.push(vals.join(','));
  });
  const csv = '\uFEFF' + lines.join('\n'); // BOM + CSV
  const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href=url;
  const ts = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
  a.download = `dotaciones_${ts}.csv`; document.body.appendChild(a); a.click();
  document.body.removeChild(a); URL.revokeObjectURL(url);
});

document.addEventListener('DOMContentLoaded', loadData);
</script>

<!-- Si tu footer no carga JS de Bootstrap/JQuery y lo necesitas, ajusta rutas:
<script src="/segtrack/vendor/jquery/jquery.min.js"></script>
<script src="/segtrack/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
-->
</body>
</html>