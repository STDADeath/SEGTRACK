<?php require_once __DIR__ . '/../models/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus me-2"></i>Registrar Funcionario</h1>
                <a href="FuncionariosLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Funcionarios
                </a>
            </div>
            
            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Funcionario</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../backed/IngresoFuncionario.php" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-semibold">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" id="nombre" class="form-control" name="Nombre" required>
                                </div>
                            </div>

                            <!-- Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="documento" class="form-label fw-semibold">Documento</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="number" id="documento" class="form-control" name="Documento" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" id="telefono" class="form-control" name="Telefono" required>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label fw-semibold">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="correo" class="form-control" name="Correo" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cargo -->
                            <div class="col-md-6 mb-3">
                                <label for="cargo" class="form-label fw-semibold">Cargo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <select id="cargo" class="form-select" name="Cargo" required>
                                        <option value="">Seleccione un cargo...</option>
                                        <option value="personalSeguridad">Personal de Seguridad</option>
                                        <option value="Funcionario">Funcionario</option>
                                        <option value="empresarial">Empresarial</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ID Sede (Opcional) -->
                            <div class="col-md-6 mb-3">
                                <label for="sede" class="form-label fw-semibold">ID de Sede (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="number" id="sede" class="form-control" name="IdSede">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='FuncionariosLista.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Funcionario
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
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos del funcionario.
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

</body>
</html>

<!---fin del contenido principal--->
<?php require_once __DIR__ . '/../models/parte_inferior.php'; ?>