<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Encabezado -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-tie me-2"></i>Registrar Funcionario
                </h1>
                <a href="../view/FuncionarioLista.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Funcionarios
                </a>
            </div>

            <!-- Card principal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Funcionario</h6>
                </div>

                <div class="card-body">
                    <form id="formFuncionario" method="POST">
                        <div class="row">

                            <!-- Cargo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Cargo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <input type="text" class="form-control" name="CargoFuncionario" id="CargoFuncionario" required>
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="NombreFuncionario" id="NombreFuncionario" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Sede -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select class="form-select" name="IdSede" id="IdSede" required>
                                        <option value="">Seleccione una sede...</option>
                                        <option value="1">Sede Principal</option>
                                        <option value="2">Sede Norte</option>
                                        <option value="3">Sede Sur</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" name="TelefonoFuncionario" id="TelefonoFuncionario" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Documento -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Documento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="number" class="form-control" name="DocumentoFuncionario" id="DocumentoFuncionario" required>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="CorreoFuncionario" id="CorreoFuncionario" required>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Guardar -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Funcionario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card información adicional -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        El código QR del funcionario se generará automáticamente después de guardar los datos.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librería SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../js/javascript/js/ValidacionFuncionario.js"></script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
