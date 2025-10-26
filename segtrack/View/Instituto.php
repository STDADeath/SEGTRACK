<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-university me-2"></i>Registrar Institución</h1>
                <a href="InstitucionLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Instituciones
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información de la Institución</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../Controller/sede_institucion_funcionario_usuario/ControladorInstituto.php" class="needs-validation" novalidate>
                        <input type="hidden" name="accion" value="insertar">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nombre de la Institución *</label>
                                <input type="text" class="form-control" name="NombreInstitucion" required maxlength="150" placeholder="Ingrese el nombre de la institución">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo de Institución *</label>
                                <select class="form-select" name="TipoInstitucion" required>
                                    <option value="">Seleccione tipo...</option>
                                    <option value="Universidad">Universidad</option>
                                    <option value="Colegio">Colegio</option>
                                    <option value="Empresa">Empresa</option>
                                    <option value="ONG">ONG</option>
                                    <option value="Hospital">Hospital</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado *</label>
                                <select class="form-select" name="EstadoInstitucion" required>
                                    <option value="Activo" selected>Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campo NIT oculto -->
                        <input type="hidden" name="Nit_Codigo" value="">

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='InstitucionLista.php'">Volver</button>
                            <button type="submit" class="btn btn-primary">Guardar Institución</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>