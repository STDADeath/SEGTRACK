<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Registrar Funcionario</h1>
        <a href="FuncionarioLista.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Funcionarios
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form id="formRegistrarFuncionario">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="CargoFuncionario" class="form-label">Cargo</label>
                        <input type="text" id="CargoFuncionario" name="CargoFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: Supervisor, Guarda, etc." required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="NombreFuncionario" class="form-label">Nombre Completo</label>
                        <input type="text" id="NombreFuncionario" name="NombreFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: Juan Pérez" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="IdSede" class="form-label">Sede</label>
                        <input type="number" id="IdSede" name="IdSede" class="form-control border-primary shadow-sm" placeholder="Ej: 1" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="TelefonoFuncionario" class="form-label">Teléfono</label>
                        <input type="number" id="TelefonoFuncionario" name="TelefonoFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: 3001234567" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="DocumentoFuncionario" class="form-label">Documento</label>
                        <input type="number" id="DocumentoFuncionario" name="DocumentoFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: 1002456789" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="CorreoFuncionario" class="form-label">Correo Electrónico</label>
                    <input type="email" id="CorreoFuncionario" name="CorreoFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: correo@empresa.com" required>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Funcionario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function () {
    $("#formRegistrarFuncionario").submit(function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        $.ajax({
            url: "../controller/sede_institucion_funcionario_usuario/controladorFuncionarios.php",
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",
            success: function (response) {  
                console.log("Respuesta del servidor:", response);

                if (response.success) {
                    alert("✅ " + response.message);
                    $("#formRegistrarFuncionario")[0].reset();
                } else {
                    alert("❌ " + (response.message || "Error al registrar funcionario"));
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Respuesta completa:", xhr.responseText);
                alert("⚠️ Error de conexión con el servidor");
            },
            complete: function () {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
