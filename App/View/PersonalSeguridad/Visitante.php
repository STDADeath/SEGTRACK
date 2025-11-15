<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user me-2"></i>Registrar Visitante</h1>
        <a href="./VisitanteLista.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Visitantes
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form id="formRegistrarVisitante">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="IdentificacionVisitante" class="form-label">Identificación</label>
                        <input type="number" id="IdentificacionVisitante" name="IdentificacionVisitante" 
                            class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="NombreVisitante" class="form-label">Nombre Completo</label>
                        <input type="text" id="NombreVisitante" name="NombreVisitante" 
                               class="form-control" required>
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
    $("#formRegistrarVisitante").submit(function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        const original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        $.ajax({
            url: "../controller/ingreso_Visitante/controladorVisitante.php",
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    alert("✅ " + res.message);
                    $("#formRegistrarVisitante")[0].reset();
                } else {
                    alert("❌ " + (res.message || "Error al registrar visitante"));
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                alert("Error de conexión con el servidor");
            },
            complete: function () {
                btn.html(original);
                btn.prop('disabled', false);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
