<?php
session_start();

// Carga la parte superior del layout
require_once __DIR__ . '/../layouts/parte_superior_supervisor.php';
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-university me-2"></i>Registrar Institución
        </h1>

        <a href="InstitucionLista.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Instituciones
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>

        <div class="card-body">

            <form id="formInstituto">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="NombreInstitucion" class="form-label">Nombre de la Institución</label>
                        <input type="text" id="NombreInstitucion" name="NombreInstitucion" 
                               class="form-control shadow-sm" 
                               placeholder="Ej: Universidad Nacional" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="Nit_Codigo" class="form-label">NIT / Código</label>
                        <input type="text" id="Nit_Codigo" name="Nit_Codigo" 
                               class="form-control shadow-sm" 
                               placeholder="Ej: 9001234567" maxlength="10" required>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="TipoInstitucion" class="form-label">Tipo de Institución</label>
                        <select id="TipoInstitucion" name="TipoInstitucion" 
                                class="form-control shadow-sm" required>
                            <option value="">Seleccione tipo...</option>
                            <option value="Universidad">Universidad</option>
                            <option value="Colegio">Colegio</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="EstadoInstitucion" class="form-label">Estado</label>
                        <select id="EstadoInstitucion" name="EstadoInstitucion" 
                                class="form-control shadow-sm" required>
                            <option value="Activo" selected>Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Institución
                    </button>
                </div>

            </form>

        </div> <!-- FIN CARD-BODY -->
    </div> <!-- FIN CARD -->

</div> <!-- FIN CONTAINER -->

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>


<!-- ============================= -->
<!--       ARCHIVOS JAVASCRIPT     -->
<!-- ============================= -->

<!-- jQuery correcto -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Archivo JS del módulo Instituto -->
<script src="../../../Public/js/javascript/js/Instituto.js"></script>

