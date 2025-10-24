<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Registrar Funcionario</h1>
                <a href="../Models/FuncionarioLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Funcionarios
                </a>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Funcionario</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../Controller/sede_institucion_funcionario_usuario/ControladorFuncionario.php" id="formFuncionario" class="needs-validation" novalidate>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="NombreFuncionario" required placeholder="Ingrese el nombre completo">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Documento</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="number" class="form-control" name="DocumentoFuncionario" required placeholder="Ingrese número de documento">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="number" class="form-control" name="TelefonoFuncionario" required placeholder="Ingrese el teléfono">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="CorreoFuncionario" required placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Cargo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                    <select class="form-select" name="CargoFuncionario" required>
                                        <option value="">Seleccione cargo...</option>
                                        <option value="Supervisor">Supervisor</option>
                                        <option value="Personal_Seguridad">Personal de Seguridad</option>
                                        <option value="Administrador">Administrador</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Sede</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control" name="SedeFuncionario" required placeholder="Ejemplo: Sede Norte">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Campo QR deshabilitado -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Código QR</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary w-100" disabled>Se generará automáticamente</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='../Models/FuncionarioLista.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Funcionario
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
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos del funcionario.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librería SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Librerías del template -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

<!-- Archivo JS de validación -->
<script src="../js/javascript/js/ValidacionFuncionario.js"></script>

</body>
</html>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
