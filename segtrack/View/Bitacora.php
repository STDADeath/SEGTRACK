<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-clipboard-list mr-2"></i>Registrar Bitácora</h1>
    <div>
      <a href="BitacoraLista.php" class="btn btn-sm btn-secondary"><i class="fas fa-list mr-1"></i> Ver Bitácora</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header py-2 bg-primary text-white">
      <strong>Información de la Bitácora</strong>
    </div>
    <div class="card-body">
      <form id="frmBita" method="post" action="/../Controller/bitacora_dotacion/BitacoraIngreso.php" novalidate>
        <div class="row">
          <!-- Columna izquierda -->
          <div class="col-lg-7">
            <div class="form-group">
              <label for="Turno">Turno <span class="text-danger">*</span></label>
              <select id="Turno" name="Turno" class="form-control" required>
                <option value="">Seleccione el turno...</option>
                <option value="Jornada mañana">Jornada mañana</option>
                <option value="Jornada tarde">Jornada tarde</option>
                <option value="Jornada nocturna">Jornada nocturna</option>
              </select>
              <small class="form-text text-muted">Debe coincidir con la jornada operativa definida.</small>
            </div>

            <div class="form-group mt-3">
              <div class="d-flex justify-content-between">
                <label for="Novedades">Novedades <span class="text-danger">*</span></label>
                <small id="countHint" class="text-muted">0 / 500</small>
              </div>
              <textarea id="Novedades" name="Novedades" maxlength="500" class="form-control" rows="4" placeholder="Describe la novedad del turno..." required></textarea>

              <div class="custom-control custom-switch mt-2">
                <input type="checkbox" class="custom-control-input" id="swSinNovedad">
                <label class="custom-control-label" for="swSinNovedad">Sin novedades</label>
              </div>

              <div class="mt-2">
                <span class="badge badge-light border mr-1 chip-template" data-text="Sin novedades. Todo bajo control.">Sin novedades</span>
                <span class="badge badge-light border mr-1 chip-template" data-text="Incidente menor con dispositivo.">Incidente menor</span>
                <span class="badge badge-light border mr-1 chip-template" data-text="Incidente mayor, requiere reporte.">Incidente mayor</span>
              </div>
            </div>
          </div>

          <!-- Columna derecha -->
          <div class="col-lg-5">
            <div class="card border-0">
              <div class="card-header bg-light py-2">
                <strong>Vincular entidades (opcional)</strong>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="chkFuncionario">
                    <label class="custom-control-label" for="chkFuncionario">Agregar Funcionario</label>
                  </div>
                  <div id="boxFuncionario" class="collapse">
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div>
                      <input type="number" min="0" class="form-control" id="IdFuncionario" name="IdFuncionario" placeholder="ID Funcionario">
                    </div>
                  </div>
                </div>

                <div class="form-group mt-3">
                  <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="chkIngreso">
                    <label class="custom-control-label" for="chkIngreso">Agregar Ingreso</label>
                  </div>
                  <div id="boxIngreso" class="collapse">
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-door-open"></i></span></div>
                      <input type="number" min="0" class="form-control" id="IdIngreso" name="IdIngreso" placeholder="ID Ingreso">
                    </div>
                  </div>
                </div>

                <div class="form-group mt-3">
                  <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="chkDispositivo">
                    <label class="custom-control-label" for="chkDispositivo">Agregar Dispositivo</label>
                  </div>
                  <div id="boxDispositivo" class="collapse">
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span></div>
                      <input type="number" min="0" class="form-control" id="IdDispositivo" name="IdDispositivo" placeholder="ID Dispositivo">
                    </div>
                  </div>
                </div>

                <div class="form-group mt-3">
                  <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="chkVisitante">
                    <label class="custom-control-label" for="chkVisitante">Agregar Visitante</label>
                  </div>
                  <div id="boxVisitante" class="collapse">
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                      <input type="number" min="0" class="form-control" id="IdVisitante" name="IdVisitante" placeholder="ID Visitante">
                    </div>
                  </div>
                </div>

                <small class="text-muted d-block mt-3">
                  * Si dejas un vínculo vacío o "0", se guardará como NULL. La fecha se asigna automáticamente.
                </small>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="BitacoraLista.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
          <div>
            <button type="button" id="btnGuardarNueva" class="btn btn-outline-primary mr-2" data-mode="new"><i class="fas fa-plus mr-1"></i> Guardar y nueva</button>
            <button type="submit" id="btnGuardar" class="btn btn-primary" data-mode="list"><i class="fas fa-save mr-1"></i> Guardar Bitácora</button>
          </div>
        </div>

        <div id="alertBox" class="alert mt-3" style="display:none;"></div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-header py-2 bg-light"><strong>Información Adicional</strong></div>
    <div class="card-body">
      <div class="alert alert-info mb-0">
        <i class="fas fa-info-circle mr-1"></i> El código QR (si aplica) se asociará después de guardar los datos de la Bitácora.
      </div>
    </div>
  </div>
</div>

<script>
// --- Config (endpoint dinámico) ---
const API_CREATE = (function () {
  return location.pathname.replace(/\/models\/[^\/]*$/, '/../Controller/BitacoraIngreso.php');
})();
const URL_LIST   = 'BitacoraLista.php';

// Helpers
const qs = sel => document.querySelector(sel);
const qsa = sel => Array.from(document.querySelectorAll(sel));
function showAlert(msg, type='success'){
  const box = qs('#alertBox');
  box.className = 'alert alert-' + type;
  box.innerHTML = msg;
  box.style.display = 'block';
  setTimeout(()=>{ box.style.display='none'; }, 3500);
}

// Chips
qsa('.chip-template').forEach(el=>{
  el.addEventListener('click', ()=>{
    const txt = el.getAttribute('data-text') || '';
    const ta  = qs('#Novedades');
    ta.value = txt;
    ta.dispatchEvent(new Event('input'));
  });
});

// Switch sin novedades
qs('#swSinNovedad').addEventListener('change', (e)=>{
  const ta = qs('#Novedades');
  if (e.target.checked){
    ta.value = 'Sin novedades. Todo bajo control.';
    ta.setAttribute('readonly','readonly');
  } else {
    ta.removeAttribute('readonly');
  }
  ta.dispatchEvent(new Event('input'));
});

// Contador
qs('#Novedades').addEventListener('input', ()=>{
  const len = qs('#Novedades').value.length;
  qs('#countHint').textContent = `${len} / 500`;
});

// Toggles
function bindToggle(chkSel, boxSel, inputSel){
  const chk = qs(chkSel), box = qs(boxSel), input = qs(inputSel);
  chk.addEventListener('change', ()=>{
    if(chk.checked){ box.classList.add('show'); input.focus(); }
    else { box.classList.remove('show'); input.value=''; }
  });
}
bindToggle('#chkFuncionario','#boxFuncionario','#IdFuncionario');
bindToggle('#chkIngreso','#boxIngreso','#IdIngreso');
bindToggle('#chkDispositivo','#boxDispositivo','#IdDispositivo');
bindToggle('#chkVisitante','#boxVisitante','#IdVisitante');

// Submit
let submitMode = 'list';
qs('#btnGuardarNueva').addEventListener('click', ()=>{ submitMode='new'; qs('#frmBita').requestSubmit(); });
qs('#btnGuardar').addEventListener('click', ()=>{ submitMode='list'; });

qs('#frmBita').addEventListener('submit', async (e)=>{
  e.preventDefault();

  const Turno = qs('#Turno').value.trim();
  const Novedades = qs('#Novedades').value.trim();
  if (!Turno){ showAlert('Selecciona el Turno.', 'warning'); return; }
  if (!Novedades){ showAlert('Escribe las Novedades.', 'warning'); return; }

  const fd = new FormData();
  fd.append('Turno', Turno);
  fd.append('Novedades', Novedades);

  // NO enviar '' ni '0' (se tomarán como NULL)
  ['IdFuncionario','IdIngreso','IdDispositivo','IdVisitante'].forEach(k=>{
    const raw = (qs('#'+k).value || '').trim();
    const n = parseInt(raw, 10);
    if (!isNaN(n) && n > 0) fd.append(k, String(n));
  });

  try{
    const res = await fetch(API_CREATE, {
      method:'POST',
      credentials:'same-origin',
      cache:'no-store',
      headers:{ 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: fd
    });

    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); }
    catch(err){
      console.error('Respuesta no JSON del servidor:\n', raw);
      showAlert('Respuesta del servidor no es JSON. Revisa la consola.', 'danger');
      return;
    }

    if(!data.ok){
      showAlert('Error: ' + (data.message || 'No se pudo crear'), 'danger');
      return;
    }

    if (submitMode === 'new'){
      qs('#Novedades').value=''; qs('#Novedades').dispatchEvent(new Event('input'));
      qsa('#frmBita input[type=number]').forEach(i=>i.value='');
      qsa('.collapse').forEach(c=>c.classList.remove('show'));
      qsa('.custom-control-input').forEach(ch=>{ if(ch.id.startsWith('chk')) ch.checked=false; });
      qs('#swSinNovedad').checked=false; qs('#Novedades').removeAttribute('readonly');
      showAlert('Bitácora creada (ID '+ (data.id || '') +'). Puedes registrar otra.', 'success');
    } else {
      window.location.href = URL_LIST;
    }
  }catch(err){
    showAlert('Error de red o servidor: ' + err.message, 'danger');
  }
});
</script>
<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
