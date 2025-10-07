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
                    <form id="formDispositivo">
                        <div class="row">
                            <!-- QR (solo informativo) -->
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

                                <!-- Campo de texto visible solo si se elige "Otro" -->
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
                                    <input type="text" class="form-control" name="MarcaDispositivo" id="MarcaDispositivo" required>
                                </div>
                            </div>
                        </div>

                        <!-- Campos Funcionario -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Funcionario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="number" class="form-control" name="IdFuncionario" id="IdFuncionario">
                                </div>
                            </div>
                        </div>

                        <!-- Campos Visitante -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Visitante</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="number" class="form-control" name="IdVisitante" id="IdVisitante">
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

<!-- Script principal -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formDispositivo");
    const tipoDispositivo = document.getElementById("TipoDispositivo");
    const campoOtro = document.getElementById("campoOtro");

    // Mostrar u ocultar el campo "Otro"
    tipoDispositivo.addEventListener("change", () => {
        campoOtro.style.display = tipoDispositivo.value === "Otro" ? "block" : "none";
    });

    // Manejo del envío del formulario
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Obtener valores
        const tipo = tipoDispositivo.value.trim();
        const otro = document.querySelector("input[name='OtroTipoDispositivo']").value.trim();
        const marca = document.getElementById("MarcaDispositivo").value.trim();
        const idFuncionario = document.getElementById("IdFuncionario").value.trim();
        const idVisitante = document.getElementById("IdVisitante").value.trim();

        // Validaciones básicas
        if (!tipo) return Swal.fire("Error", "Debe seleccionar un tipo de dispositivo", "error");
        if (tipo === "Otro" && otro === "") return Swal.fire("Error", "Debe especificar el tipo de dispositivo", "error");
        if (marca === "") return Swal.fire("Error", "Debe ingresar la marca del dispositivo", "error");

        if ((idFuncionario === "" && idVisitante === "") || (idFuncionario && idVisitante)) {
            return Swal.fire("Error", "Debe ingresar solo un ID: Funcionario o Visitante", "error");
        }

        // Preparar datos
        const formData = new FormData(form);
        formData.append("accion", "registrar");

        // Enviar datos al controlador
        try {
            const response = await fetch("../../Controller/parqueadero_dispositivo/ControladorDispositivo.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();
            console.log("Respuesta del servidor:", data);

            if (data.success) {
                Swal.fire("✅ Éxito", data.message, "success");
                form.reset();
                campoOtro.style.display = "none";
            } else {
                Swal.fire("❌ Error", data.message || "Ocurrió un error al registrar", "error");
            }
        } catch (error) {
            console.error("Error al enviar:", error);
            Swal.fire("⚠ Error de conexión", "No se pudo contactar con el servidor.", "warning");
        }
    });
});
</script>

<?php require_once __DIR__ . '/../model/Plantilla/parte_inferior.php'; ?>