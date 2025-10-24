<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus mr-2"></i>Registrar Funcionario</h1>
    <div>
      <a href="FuncionariosLista.php" class="btn btn-sm btn-secondary"><i class="fas fa-list mr-1"></i> Ver Funcionarios</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header py-2 bg-primary text-white">
      <strong>Información del Funcionario</strong>
    </div>
    <div class="card-body">
      <form id="frmFuncionario" method="post" action="ControladorFuncionario.php" novalidate>
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group mb-3">
              <label for="NombreFuncionario">Nombre Completo <span class="text-danger">*</span></label>
              <input type="text" id="NombreFuncionario" name="NombreFuncionario" class="form-control" placeholder="Ingrese el nombre completo" required>
            </div>

            <div class="form-group mb-3">
              <label for="DocumentoFuncionario">Documento <span class="text-danger">*</span></label>
              <input type="text" id="DocumentoFuncionario" name="DocumentoFuncionario" class="form-control" placeholder="Número de documento" required>
            </div>

            <div class="form-group mb-3">
              <label for="TelefonoFuncionario">Teléfono <span class="text-danger">*</span></label>
              <input type="text" id="TelefonoFuncionario" name="TelefonoFuncionario" class="form-control" placeholder="Número de contacto" required>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group mb-3">
              <label for="CorreoFuncionario">Correo Electrónico <span class="text-danger">*</span></label>
              <input type="email" id="CorreoFuncionario" name="CorreoFuncionario" class="form-control" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="form-group mb-3">
              <label for="CargoFuncionario">Cargo <span class="text-danger">*</span></label>
              <select id="CargoFuncionario" name="CargoFuncionario" class="form-control" required>
                <option value="">Seleccione un cargo...</option>
                <option value="Supervisor">Supervisor</option>
                <option value="Personal_Seguridad">Personal de Seguridad</option>
                <option value="Administrador">Administrador</option>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="SedeFuncionario">Sede <span class="text-danger">*</span></label>
              <input type="text" id="SedeFuncionario" name="SedeFuncionario" class="form-control" placeholder="Ej: Sede Norte" required>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="FuncionariosLista.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
          <div>
            <button type="button" id="btnGuardarNuevo" class="btn btn-outline-primary mr-2" data-mode="new"><i class="fas fa-plus mr-1"></i> Guardar y nuevo</button>
            <button type="submit" id="btnGuardar" class="btn btn-primary" data-mode="list"><i class="fas fa-save mr-1"></i> Guardar Funcionario</button>
          </div>
        </div>

        <div id="alertBox" class="alert mt-3" style="display:none;"></div>
      </form>
    </div>
  </div>
</div>

<script>
// --- Configuración ---
const API_CREATE = "/../Controller/sede_institucion_funcionario_usuario/ingresoFuncionario.php";
const URL_LIST   = "FuncionariosLista.php";

// Helpers
const qs = sel => document.querySelector(sel);
const qsa = sel => Array.from(document.querySelectorAll(sel));

function showAlert(msg, type='success'){
  const box = qs('#alertBox');
  box.className = 'alert alert-' + type;
  box.innerHTML = msg;
  box.style.display = 'block';
  setTimeout(()=>{ box.style.display='none'; }, 3000);
}

// Control de botones
let submitMode = 'list';
qs('#btnGuardarNuevo').addEventListener('click', ()=>{ submitMode='new'; qs('#frmFuncionario').requestSubmit(); });
qs('#btnGuardar').addEventListener('click', ()=>{ submitMode='list'; });

// Envío del formulario
qs('#frmFuncionario').addEventListener('submit', async (e)=>{
  e.preventDefault();

  const campos = ['NombreFuncionario','DocumentoFuncionario','TelefonoFuncionario','CorreoFuncionario','CargoFuncionario','SedeFuncionario'];
  for (const id of campos) {
    if (!qs('#'+id).value.trim()) {
      showAlert('Completa el campo: '+id.replace('Funcionario',''), 'warning');
      return;
    }
  }

  const fd = new FormData(qs('#frmFuncionario'));

  try {
    const res = await fetch(API_CREATE, {
      method: 'POST',
      body: fd
    });

    const data = await res.json();

    if (!data.ok) {
      showAlert('Error: ' + (data.message || 'No se pudo guardar.'), 'danger');
      return;
    }

    if (submitMode === 'new') {
      qs('#frmFuncionario').reset();
      showAlert('Funcionario registrado correctamente.', 'success');
    } else {
      window.location.href = URL_LIST;
    }

  } catch (err) {
    showAlert('Error del servidor: ' + err.message, 'danger');
  }
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
