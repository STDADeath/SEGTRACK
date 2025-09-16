<?php
/*  SEGTRACK QR – Bitácora (LISTA)
    Vista Bootstrap 4 con filtros, paginación, edición (modal) y eliminación.

    Endpoints (AJAX):
      - LISTA:     ./BitacoraLista.php
      - ACTUALIZAR ../backed/BitacoraActualizar.php
      - ELIMINAR   ../backed/BitacoraEliminar.php
*/

$SUP = __DIR__ . '/parte_superior.php';
$INF = __DIR__ . '/parte_inferior.php';
$USING_SHELL = file_exists($SUP) && file_exists($INF);

if ($USING_SHELL) {
  require_once $SUP; // Tu layout con sidebar/estilos
} else {
  // Fallback simple si no tienes parte_superior/parte_inferior
  ?><!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Bitácora Registrada</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap 4 sin romper tu stack -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
      body { background:#f8f9fc; }
      .card-header { font-weight:600 }
      th.sortable{cursor:pointer}
      .badge-turno{font-size:.85rem}
      .pagination-status{font-size:.85rem;color:#6c757d}
      .btn-xs{padding:.25rem .4rem;font-size:.75rem;line-height:1;border-radius:.2rem}
    </style>
  </head>
  <body class="p-3">
  <?php
}
?>
<div class="container-fluid mt-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><span class="mr-2">👥</span>Bitácora Registrada</h4>
    <div>
      <!-- RUTA CORREGIDA -->
      <a href="./Bitacora.php" class="btn btn-sm btn-primary">
        <span class="mr-1">＋</span>Nueva Bitácora
      </a>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-header">Filtros</div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Novedades (texto)</label>
          <input type="text" class="form-control" id="f_q" placeholder="Buscar en novedades...">
        </div>
        <div class="form-group col-md-2">
          <label>Turno</label>
          <select class="form-control" id="f_turno">
            <option>(Todos)</option>
            <option>Jornada mañana</option>
            <option>Jornada tarde</option>
            <option>Jornada noche</option>
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>ID Funcionario</label>
          <input type="number" class="form-control" id="f_funcionario" placeholder="Ej: 12" min="1" step="1">
        </div>
        <div class="form-group col-md-2">
          <label>Desde</label>
          <input type="text" class="form-control" id="f_desde" placeholder="dd/mm/aaaa">
        </div>
        <div class="form-group col-md-2">
          <label>Hasta</label>
          <input type="text" class="form-control" id="f_hasta" placeholder="dd/mm/aaaa">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <div>
          <button class="btn btn-primary mr-2" id="btnBuscar">🔎 Buscar</button>
          <button class="btn btn-secondary" id="btnLimpiar">🧹 Limpiar</button>
        </div>
        <div>
          <label class="mr-2">Tamaño</label>
          <select id="f_size" class="custom-select custom-select-sm w-auto d-inline-block mr-2">
            <option>10</option><option>20</option><option>50</option>
          </select>
          <button class="btn btn-success" id="btnCsv">📤 Exportar CSV</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista -->
  <div class="card">
    <div class="card-header">Lista de Bitácora</div>
    <div class="card-body p-0">
      <div id="alertBox" class="alert alert-danger m-3 d-none"></div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-dark">
          <tr>
            <th class="sortable" data-sort="id">ID</th>
            <th class="sortable" data-sort="fecha">Fecha</th>
            <th>Turno</th>
            <th>Novedades</th>
            <th class="sortable" data-sort="funcionario">Id Funcionario</th>
            <th>Id Ingreso</th>
            <th>Id Dispositivo</th>
            <th style="width:120px">Acciones</th>
          </tr>
          </thead>
          <tbody id="tbodyList">
          <tr><td colspan="8" class="text-center text-muted py-4">Cargando…</td></tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center p-3">
        <div class="pagination-status" id="statusTotal">Total: 0</div>
        <nav>
          <ul class="pagination pagination-sm mb-0" id="pager">
            <!-- Dinámico -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditarBitacora" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2">
        <h6 class="modal-title">Editar Bitácora</h6>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>IdBitacora</label>
            <input type="text" class="form-control" id="editId" readonly>
          </div>
          <div class="form-group col-md-9">
            <label>Turno</label>
            <input class="form-control" id="editTurno" list="turnosList">
            <datalist id="turnosList">
              <option>Jornada mañana</option>
              <option>Jornada tarde</option>
              <option>Jornada noche</option>
            </datalist>
          </div>
        </div>

        <div class="form-group">
          <label>Novedades</label>
          <input type="text" class="form-control" id="editNovedades" maxlength="500">
        </div>

        <div class="form-row d-none">
           <div class="form-group col-md-4">
             <label>IdFuncionario</label>
             <input type="number" class="form-control" id="editFuncionario" min="1" step="1">
           </div>
           <div class="form-group col-md-4">
             <label>IdIngreso</label>
             <input type="number" class="form-control" id="editIngreso" min="1" step="1">
           </div>
           <div class="form-group col-md-4">
             <label>IdDispositivo</label>
             <input type="number" class="form-control" id="editDispositivo" min="1" step="1">
           </div>
         </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarCambios">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<?php if ($USING_SHELL): ?>
  <?php require_once $INF; ?>
<?php else: ?>
  <!-- Fallback JS si no hay tu layout -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  </body></html>
<?php endif; ?>

<script>
// ======================== CONFIG (RUTAS CORRECTAS) ========================
const API_LIST = '../backed/BitaList.php';
const API_UPD  = '../backed/BitacoraActualizar.php';
const API_DEL  = '../backed/BitacoraEliminar.php';

// ======================== ESTADO UI ========================
let state = { page:1, size:10, sort:'fecha', dir:'desc', total:0, rows:[] };

// ======================== HELPERS ========================
function showError(msg){
  $('#alertBox').text(msg).removeClass('d-none');
  setTimeout(()=>$('#alertBox').addClass('d-none'), 4000);
}
function val(v, alt=''){ return (v===null || v===undefined || v==='') ? alt : v; }
function badgeTurno(t){
  if(!t) return '';
  let cls='badge-secondary', s=t.toLowerCase();
  if (s.indexOf('mañana')>=0 || s.indexOf('manana')>=0) cls='badge-info';
  else if (s.indexOf('tarde')>=0) cls='badge-warning';
  else if (s.indexOf('noche')>=0) cls='badge-dark';
  return `<span class="badge ${cls} badge-turno">${t}</span>`;
}
function buildParams(extra={}){
  return {
    q: $('#f_q').val().trim(),
    turno: $('#f_turno').val(),
    funcionario: $('#f_funcionario').val().trim(),
    desde: $('#f_desde').val().trim(),
    hasta: $('#f_hasta').val().trim(),
    page: state.page,
    size: state.size,
    sort: state.sort,
    dir: state.dir,
    ...extra
  };
}
function renderPager(){
  const totalPages = Math.max(1, Math.ceil(state.total / state.size));
  const $p = $('#pager').empty();
  const mk = (label, page, disabled=false, active=false) =>
    $(`<li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
         <a class="page-link" href="#">${label}</a>
       </li>`).on('click', function(e){
         e.preventDefault();
         if(disabled||active) return;
         state.page = page;
         loadData();
       });
  $p.append(mk('«', Math.max(1, state.page-1), state.page===1));
  let N=totalPages, start=1, end=totalPages;
  if (N>7){ start=Math.max(1, state.page-3); end=Math.min(totalPages, start+6); if(end-start<6) start=Math.max(1,end-6); }
  for(let i=start;i<=end;i++){ $p.append(mk(i,i,false,i===state.page)); }
  $p.append(mk('»', Math.min(totalPages, state.page+1), state.page===totalPages));
  $('#statusTotal').text('Total: '+state.total);
}

// ======================== CARGA DE DATOS ========================
function loadData(){
  state.size = parseInt($('#f_size').val(),10) || 10;
  $('#tbodyList').html('<tr><td colspan="8" class="text-center text-muted py-4">Cargando…</td></tr>');

  $.getJSON(API_LIST, buildParams(), function(r){
    if(!r || r.ok!==true){
      showError(val(r && r.message, 'Error: No se pudo cargar la lista.'));
      $('#tbodyList').html('<tr><td colspan="8" class="text-center text-muted py-4">Error cargando datos</td></tr>');
      return;
    }
    const rows = r.rows || [];
    state.total = parseInt(r.total || rows.length, 10);
    state.rows  = rows;

    const $tb = $('#tbodyList').empty();
    if (rows.length===0){
      $tb.html('<tr><td colspan="8" class="text-center text-muted py-4">Sin resultados</td></tr>');
    } else {
      rows.forEach(row=>{
        const id  = val(row.IdBitacora, val(row.ID, row.id));
        const fe  = val(row.Fecha, row.fecha);
        const tu  = val(row.Turno, row.turno);
        const no  = val(row.Novedades, row.novedades);
        const fu  = val(row.IdFuncionario, row.id_funcionario);
        const ing = val(row.IdIngreso, row.id_ingreso);
        const dis = val(row.IdDispositivo, row.id_dispositivo);
        const tr = `<tr>
          <td>${id}</td>
          <td>${val(fe,'')}</td>
          <td>${badgeTurno(tu)}</td>
          <td>${val(no,'')}</td>
          <td>${val(fu,'')}</td>
          <td>${val(ing,'')}</td>
          <td>${val(dis,'')}</td>
          <td>
            <button class="btn btn-info btn-xs mr-1" title="Editar"
                    onclick='openEdit(${JSON.stringify({id,tu,no,fu,ing,dis}).replace(/"/g,"&quot;")})'>✏️</button>
            <button class="btn btn-danger btn-xs" title="Eliminar" onclick="removeItem(${id})">🗑️</button>
          </td>
        </tr>`;
        $tb.append(tr);
      });
    }
    renderPager();
  }).fail(function(){
    showError('Error de red o servidor al cargar la lista.');
    $('#tbodyList').html('<tr><td colspan="8" class="text-center text-muted py-4">Error cargando datos</td></tr>');
  });
}

// ======================== EDITAR ========================
function openEdit(o){
  $('#editId').val(o.id);
  $('#editTurno').val(o.tu || '');
  $('#editNovedades').val(o.no || '');
  $('#editFuncionario').val(o.fu || '');
  $('#editIngreso').val(o.ing || '');
  $('#editDispositivo').val(o.dis || '');
  $('#modalEditarBitacora').modal('show');
}
$('#btnGuardarCambios').on('click', function(){
  const payload = {
    IdBitacora:    $('#editId').val(),
    Turno:         $('#editTurno').val().trim(),
    Novedades:     $('#editNovedades').val().trim(),
    IdFuncionario: $('#editFuncionario').val().trim(),
    IdIngreso:     $('#editIngreso').val().trim(),
    IdDispositivo: $('#editDispositivo').val().trim()
  };
  if (!payload.IdBitacora || !payload.Turno || !payload.Novedades){
    alert('Complete los campos obligatorios'); return;
  }
  $.ajax({
    url: API_UPD, type:'POST', dataType:'json', data: payload,
    success: function(r){
      $('#modalEditarBitacora').modal('hide');
      if (r && r.ok){ alert('Bitácora actualizada'); loadData(); }
      else { alert('No se pudo actualizar: ' + (r && r.message ? r.message : 'Error')); }
    },
    error: function(){ alert('Error al actualizar'); }
  });
});

// ======================== ELIMINAR ========================
function removeItem(id){
  if(!confirm('¿Eliminar la bitácora #' + id + '?')) return;
  $.ajax({
    url: API_DEL, type:'POST', dataType:'json', data:{ IdBitacora:id },
    success: function(r){
      if (r && r.ok){ loadData(); }
      else { alert('No se pudo eliminar: ' + (r && r.message ? r.message : 'Error')); }
    },
    error: function(){ alert('Error al eliminar'); }
  });
}
window.removeItem = removeItem;

// ======================== ORDENAR ========================
$('th.sortable').on('click', function(){
  const s = $(this).data('sort');
  if (state.sort === s) state.dir = (state.dir==='asc'?'desc':'asc');
  else { state.sort = s; state.dir = 'asc'; }
  state.page = 1; loadData();
});

// ======================== ACCIONES FILTROS ========================
$('#btnBuscar').on('click', function(){ state.page=1; loadData(); });
$('#btnLimpiar').on('click', function(){
  $('#f_q').val(''); $('#f_turno').val('(Todos)'); $('#f_funcionario').val('');
  $('#f_desde').val(''); $('#f_hasta').val(''); $('#f_size').val('10');
  state = { page:1, size:10, sort:'fecha', dir:'desc', total:0, rows:[] };
  loadData();
});
$('#f_size').on('change', function(){ state.page=1; loadData(); });

$('#btnCsv').on('click', function(){
  const params = buildParams({ csv:1 });
  const q = $.param(params);
  window.location = API_LIST + '?' + q;
});

// ======================== INIT ========================
$(function(){ loadData(); });
</script>
