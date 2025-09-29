<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book me-2"></i>Registrar Bitácora</h1>
        <a href="../view/Bitacora_Listar.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Bitácoras
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form id="formRegistrarBitacora">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="TurnoBitacora" class="form-label">Turno</label>
                        <input type="text" id="TurnoBitacora" name="TurnoBitacora" class="form-control" placeholder="Ej: Jornada mañana" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="FechaBitacora" class="form-label">Fecha y Hora</label>
                        <input type="datetime-local" id="FechaBitacora" name="FechaBitacora" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="NovedadesBitacora" class="form-label">Novedades</label>
                    <textarea id="NovedadesBitacora" name="NovedadesBitacora" class="form-control" rows="3" placeholder="Describa las novedades aquí..." required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="IdFuncionario" class="form-label">ID Funcionario</label>
                        <input type="number" id="IdFuncionario" name="IdFuncionario" class="form-control" placeholder="Ej: 101" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="IdIngreso" class="form-label">ID Ingreso</label>
                        <input type="number" id="IdIngreso" name="IdIngreso" class="form-control" placeholder="Ej: 205" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">¿Hay visitante?</label>
                        <select id="TieneVisitante" name="TieneVisitante" class="form-select border-primary shadow-sm" required>
                            <option value="" disabled selected>-- Seleccione --</option>
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="VisitanteContainer" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label for="IdVisitante" class="form-label">ID Visitante</label>
                        <input type="number" id="IdVisitante" name="IdVisitante" class="form-control" placeholder="Ej: 303">
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

                <div class="row" id="DispositivoContainer" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label for="IdDispositivo" class="form-label">ID Dispositivo</label>
                        <input type="number" id="IdDispositivo" name="IdDispositivo" class="form-control" placeholder="Ej: 401">
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function () {
    // Mostrar u ocultar campo visitante
    $("#TieneVisitante").change(function () {
        if ($(this).val() === "si") {
            $("#VisitanteContainer").slideDown();
        } else {
            $("#VisitanteContainer, #DispositivoContainer").slideUp();
            $("#IdVisitante, #IdDispositivo").val("");
            $("#TraeDispositivo").val("");
        }
    });

    // Mostrar u ocultar campo dispositivo
    $("#TraeDispositivo").change(function () {
        if ($(this).val() === "si") {
            $("#DispositivoContainer").slideDown();
        } else {
            $("#DispositivoContainer").slideUp();
            $("#IdDispositivo").val("");
        }
    });

    // Registrar Bitácora
    $("#formRegistrarBitacora").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "../controller/bitacora_dotacion/controladorBitacora.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                console.log("Respuesta del servidor:", response);

                if (response.success) {
                    alert("✅ Bitácora registrada correctamente");
                    $("#formRegistrarBitacora")[0].reset();
                    $("#VisitanteContainer, #DispositivoContainer").hide();
                } else {
                    alert("❌ Error: " + (response.message || "Error desconocido"));
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Estado:", status);
                console.log("Respuesta completa del servidor:", xhr.responseText);
                alert("⚠️ Error al registrar la bitácora. Revisa la consola para más detalles.");
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
