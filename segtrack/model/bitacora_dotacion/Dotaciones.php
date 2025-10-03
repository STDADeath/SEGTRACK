<?php require_once __DIR__ . '/../models/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus me-2"></i>Registrar Dotacion</h1>
                <a href="DotacionesLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dotacion
                </a>
            </div>
            
            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información de la Dotacion</h6>
                </div>
                <div class="card-body">
                    <form id="form-dotacion" method="POST" action="../backed/DotacionIngreso.php" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="NombreDotacion" class="form-label fw-semibold">Nombre Dotacion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" id="NombreDotacion" class="form-control" name="NombreDotacion" required>
                                </div>
                            </div>

                            <!-- ID Dotacion -->
                            <div class="col-md-6 mb-3">
                                <label for="idDotacion" class="form-label fw-semibold">ID Dotacion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <!-- DESPUÉS (alta): NO ENVIAR name -->
                                    <input type="text" id="IdDotacion" class="form-control" value="(se autogenera)" disabled>
                                </div>
                            </div>
                        </div>

                        

                        <div class="row">
                            <!-- Tipo Dotacion -->
                            <div class="col-md-6 mb-3">
                                <label for="TipoDotacion" class="form-label fw-semibold">Tipo Dotacion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <select id="TipoDotacion" class="form-select" name="TipoDotacion" required>
                                        <option value="">Seleccione un tipo...</option>
                                        <option value="Uniforme">Uniforme</option>
                                        <option value="Equipo">Equipo</option>
                                        <option value="Herramienta">Herramienta</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Estado Dotacion -->
                            <div class="col-md-6 mb-3">
                                <label for="EstadoDotacion" class="form-label fw-semibold">Estado Dotacion</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <select id="EstadoDotacion" class="form-select" name="EstadoDotacion" required>
                                        <option value="">Seleccione un estado...</option>
                                        <option value="Buen estado">Buen Estado</option>
                                        <option value="Regular">Regular</option>
                                        <option value="Dañado">Dañado</option>
                                    </select>
                                </div>
                            </div>

                            
                        </div>

                        <div class="row">
                            
                            
                            
                            <!-- Fecha de Entrega -->
                            <div class="col-md-6 mb-3">
                                <label for="fechaEntrega" class="form-label fw-semibold">Fecha de Entrega</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                    <input type="datetime-local" id="fechaEntrega" name="FechaEntrega" class="form-control" required>
                                </div>
                                </div>

                                <!-- Fecha de Devolución -->
                                <div class="col-md-6 mb-3">
                                <label for="fechaDevolucion" class="form-label fw-semibold">Fecha de Devolución</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                    <input type="datetime-local" id="fechaDevolucion" name="FechaDevolucion" class="form-control">
                                </div>
                                </div>

                                <!-- ID Funcionario -->
                                <div class="col-md-6 mb-3">
                                <label for="IdFuncionario" class="form-label fw-semibold">ID Funcionario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <input type="number" id="IdFuncionario" name="IdFuncionario" class="form-control" min="1" required>
                                </div>
                                </div>

                                <!-- Novedad -->
                                <div class="col-md-6 mb-3">
                                <label for="Novedad" class="form-label fw-semibold">Novedad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" id="Novedad" class="form-control" name="Novedad" required>
                                </div>
                            </div>
                            </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <!-- Botón Volver (opcional) -->
                            <a href="DotacionesLista.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Volver
                            </a>

                            <!-- ÚNICO botón de Guardar -->
                            <button type="submit" id="btn-guardar" class="btn btn-primary">
                                <span class="txt">Guardar Dotacion</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                         </div>
                    </form>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos de la dotacion.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../js/sb-admin-2.min.js"></script>

<!-- Modal: Registro exitoso -->
<div class="modal fade" id="modalDotacionOk" tabindex="-1" role="dialog" aria-labelledby="modalOkLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalOkLabel">✅ Dotación registrada</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">ID</dt><dd class="col-sm-8" id="md-id">—</dd>
          <dt class="col-sm-4">Código</dt><dd class="col-sm-8" id="md-codigo">—</dd>
          <dt class="col-sm-4">Estado</dt><dd class="col-sm-8" id="md-estado">—</dd>
          <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8" id="md-nombre">—</dd>
          <dt class="col-sm-4">Tipo</dt><dd class="col-sm-8" id="md-tipo">—</dd>
        </dl>
        <!-- HOOK_QR_VIEW -->
        <div id="qr-slot" class="d-none mt-3"></div>
      </div>
      <div class="modal-footer">
        <a id="btn-ver" class="btn btn-outline-primary" href="#">Ver Dotación</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btn-nueva">Registrar otra</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var $form = $('#form-dotacion');
  var $btn  = $('#btn-guardar');
  var $spin = $btn.find('.spinner-border');
  var $txt  = $btn.find('.txt');

  function setLoading(on) {
    if (on) { $btn.prop('disabled', true); $spin.removeClass('d-none'); $txt.text('Guardando...'); }
    else    { $btn.prop('disabled', false); $spin.addClass('d-none');   $txt.text('Guardar Dotacion'); }
  }
  function clearValidation() {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').remove();
  }

  $form.on('submit', function (e) {
    e.preventDefault();
    clearValidation();
    setLoading(true);

    var action = $form.attr('action') || '../backed/DotacionIngreso.php';
    var fd = new FormData(this);

    fetch(action, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        setLoading(false);

        if (!data.ok) {
          if (data.errors) {
            Object.keys(data.errors).forEach(function (name) {
              var $field = $form.find('[name="'+name+'"]');
              if ($field.length) {
                $field.addClass('is-invalid');
                var fb = $('<div class="invalid-feedback"></div>').text(data.errors[name]);
                if ($field.next('.invalid-feedback').length === 0) $field.after(fb);
              }
            });
            $('html,body').animate({ scrollTop: $form.offset().top - 80 }, 300);
          } else {
            alert(data.message || 'Ocurrió un error.');
          }
          return;
        }

        // Éxito: llenar y mostrar modal
        $('#md-id').text(data.id);
        $('#md-codigo').text(data.codigo || '—');
        $('#md-estado').text(data.estado || '—');
        $('#md-nombre').text($form.find('[name="NombreDotacion"]').val() || '—');
        var tipoTexto = $('#TipoDotacion option:selected').text() || '—';
        $('#md-tipo').text(tipoTexto);
        $('#btn-ver').attr('href', 'DotacionesDetalle.php?id=' + data.id);

        $('#modalDotacionOk').modal('show');
      })
      .catch(function (err) {
        setLoading(false);
        alert('Error de red o servidor. Revisa la consola.');
        console.error(err);
      });
  });

  $('#btn-nueva').on('click', function () {
    $form[0].reset();
    clearValidation();
    $('#modalDotacionOk').modal('hide');
  });
})();
</script>

</body>
</html>

<!---fin del contenido principal--->
<?php require_once __DIR__ . '/../models/parte_inferior.php'; ?>