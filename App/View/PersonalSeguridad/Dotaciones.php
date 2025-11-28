<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-tshirt me-2"></i>Ingresar Dotación</h1>
        <a href="./DotacionLista.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Dotaciones
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Ingreso de Dotación</h6>
        </div>

        <div class="card-body">
            <form id="formIngresarDotacion">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="EstadoDotacion" class="form-label">Estado de la Dotación</label>
                        <select id="EstadoDotacion" name="EstadoDotacion" class="form-select border-primary shadow-sm" required>
                            <option value="" disabled selected>-- Seleccione --</option>
                            <option value="Buen estado">Buen estado</option>
                            <option value="Regular">Regular</option>
                            <option value="Dañado">Dañado</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="TipoDotacion" class="form-label">Tipo de Dotación</label>
                        <select id="TipoDotacion" name="TipoDotacion" class="form-select border-primary shadow-sm" required>
                            <option value="" disabled selected>-- Seleccione --</option>
                            <option value="Uniforme">Uniforme</option>
                            <option value="Equipo">Equipo</option>
                            <option value="Herramienta">Herramienta</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="NovedadDotacion" class="form-label">Novedad</label>
                    <textarea id="NovedadDotacion" name="NovedadDotacion" class="form-control border-primary shadow-sm" rows="3" placeholder="Describa la novedad..." required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="FechaEntrega" class="form-label">Fecha de Entrega</label>
                        <input type="datetime-local" id="FechaEntrega" name="FechaEntrega" class="form-control border-primary shadow-sm" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="FechaDevolucion" class="form-label">Fecha de Devolución</label>
                        <input type="datetime-local" id="FechaDevolucion" name="FechaDevolucion" class="form-control border-primary shadow-sm">
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="IdFuncionario" class="form-label">ID Funcionario</label>
                    <input type="number" id="IdFuncionario" name="IdFuncionario" class="form-control border-primary shadow-sm" placeholder="Ej: 101" required>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Dotación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function () {

    // Captura el submit del formulario de dotación
    $("#formIngresarDotacion").submit(function (e) {
        e.preventDefault(); // Evita que la página recargue

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        // Envía los datos al controlador usando AJAX
        $.ajax({
            url: "../../Controller/ControladorDotacion.php",
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",
            success: function (response) {
                console.log("Respuesta del servidor:", response);

                if (response.success) {
                    alert("✓ " + response.message);

                    // Limpia el formulario después de registrar
                    $("#formIngresarDotacion")[0].reset();
                } else {
                    let errorMsg = "✗ " + (response.message || "Error al registrar la dotación");
                    if (response.error) {
                        errorMsg += "\nDetalles: " + response.error;
                    }
                    alert(errorMsg);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Estado:", status);
                console.log("Respuesta completa del servidor:", xhr.responseText);

                let errorMessage = "Error de conexión con el servidor";
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Ignora errores de parseo
                }
                alert("✗ " + errorMessage);
            },
            complete: function () {
                // Restaura el botón
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

});
</script>


<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
