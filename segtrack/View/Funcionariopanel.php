<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Registrar Funcionario</h1>
                <a href="FuncionarioLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Funcionarios
                </a>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Funcionario</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../Controller/sede_institucion_funcionario_usuario/ControladorFuncionariopanel.php" id="formFuncionario" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre Completo</label>
                                <input type="text" class="form-control" name="NombreFuncionario" required placeholder="Ingrese el nombre completo">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Documento</label>
                                <input type="number" class="form-control" name="DocumentoFuncionario" required placeholder="Ingrese número de documento">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="number" class="form-control" name="TelefonoFuncionario" required placeholder="Ingrese el teléfono">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" class="form-control" name="CorreoFuncionario" required placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Cargo</label>
                                <select class="form-select" name="CargoFuncionario" required>
                                    <option value="">Seleccione cargo...</option>
                                    <option value="Funcionario">Funcionario</option>
                                    <option value="Personal Seguridad">Personal Seguridad</option>
                                    <option value="Empresario">Empresario</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Sede</label>
                                <select class="form-select" name="IdSede" required>
                                    <option value="">Seleccione sede...</option>
                                    <option value="1">Sede Norte</option>
                                    <option value="2">Sede Sur</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Código QR</label>
                                <button type="button" class="btn btn-secondary w-100" disabled>Se generará automáticamente</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='FuncionarioLista.php'">Volver</button>
                            <button type="submit" class="btn btn-primary">Guardar Funcionario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
