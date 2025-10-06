<?php require_once __DIR__ . '/../model/Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Registrar Dispositivos</h1>
                <a href="../model/DispositivoLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dispositivos
                </a>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Dispositivo</h6>
                </div>
                <div class="card-body">
                    <form id="formDispositivo" method="POST">
                        <div class="row">
                            <!-- QR deshabilitado, ahora solo botón -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">QR del Dispositivo</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary w-100" disabled>Próximamente</button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo de Dispositivo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-desktop"></i></span>
                                    <select class="form-select" name="TipoDispositivo" id="TipoDispositivo" required>
                                        <option value="">Seleccione tipo...</option>
                                        <option value="Portatil">Portátil</option>
                                        <option value="Tablet">Tablet</option>
                                        <option value="Computador">Computador</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <!-- Campo de texto oculto para "Otro" -->
                                <div id="campoOtro" class="mt-2" style="display:none;">
                                    <input type="text" class="form-control" name="OtroTipoDispositivo" placeholder="Especifique el tipo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Marca</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" name="MarcaDispositivo" required>
                                </div>
                            </div>
                        </div>

                        <!-- Select para visitante -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">¿Hay visitante?</label>
                                <select id="TieneVisitante" name="TieneVisitante" class="form-select border-primary shadow-sm" required>
                                    <option value="" disabled selected>-- Seleccione --</option>
                                    <option value="no">No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campos de visitante -->
                        <div class="row" id="VisitanteContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="IdVisitante" class="form-label">ID Visitante</label>
                                <input type="number" id="IdVisitante" name="IdVisitante" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">¿El visitante trae dispositivo?</label>
                                <select id="TraeDispositivo" name="TraeDispositivo" class="form-select border-primary shadow-sm">
                                    <option value="" disabled selected>-- Seleccione --</option>
                                    <option value="no">No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campos del dispositivo asociado al visitante -->
                        <div class="row" id="DispositivoContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="IdDispositivo" class="form-label">ID Dispositivo</label>
                                <input type="number" id="IdDispositivo" name="IdDispositivo" class="form-control">
                            </div>
                        </div>

                        <!-- Campos funcionario -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Funcionario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="number" class="form-control" name="IdFuncionario">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='../View/DispositivoLista.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Dispositivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos del dispositivo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librería SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Librerías necesarias -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/javascript/demo/sb-admin-2.min.js"></script>

<!-- AJAX Dispositivo -->
<script>
$(document).ready(function () {

    // Mostrar/ocultar campos de visitante
    $("#TieneVisitante").change(function () {
        if ($(this).val() === "si") {
            $("#VisitanteContainer").slideDown();
        } else {
            $("#VisitanteContainer, #DispositivoContainer").slideUp();
            $("#IdVisitante, #IdDispositivo").val("");
            $("#TraeDispositivo").val("");
        }
    });

    // Mostrar/ocultar campos de dispositivo visitante
    $("#TraeDispositivo").change(function () {
        if ($(this).val() === "si") {
            $("#DispositivoContainer").slideDown();
        } else {
            $("#DispositivoContainer").slideUp();
            $("#IdDispositivo").val("");
        }
    });

    // Mostrar campo "Otro" si se selecciona esa opción
    $("#TipoDispositivo").change(function () {
        if ($(this).val() === "Otro") {
            $("#campoOtro").slideDown();
        } else {
            $("#campoOtro").slideUp();
            $("input[name='OtroTipoDispositivo']").val("");
        }
    });

    // Enviar formulario Dispositivo con AJAX
    $("#formDispositivo").submit(function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        $.ajax({
            url: "../../Controller/parqueadero_dispositivo/ControladorDispositivo.php",
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",
            success: function (response) {
                console.log("Respuesta del servidor:", response);

                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: response.message,
                        confirmButtonText: "Aceptar"
                    });

                    $("#formDispositivo")[0].reset();
                    $("#VisitanteContainer, #DispositivoContainer, #campoOtro").hide();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message || "No se pudo registrar el dispositivo"
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Estado:", status);
                console.log("Respuesta completa del servidor:", xhr.responseText);

                Swal.fire({
                    icon: "warning",
                    title: "Error de conexión",
                    text: "⚠️ No se pudo conectar con el servidor. Verifica la configuración."
                });
            },
            complete: function () {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

});
</script>

<?php require_once __DIR__ . '/../model/Plantilla/parte_inferior.php'; ?>
