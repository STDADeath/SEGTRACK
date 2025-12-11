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

                    <!-- IDENTIFICACIÓN -->
                    <div class="col-md-6 mb-3">
                        <label for="IdentificacionVisitante" class="form-label">Número de Identificación</label>
                        <input type="Number" id="IdentificacionVisitante" name="IdentificacionVisitante"
                               class="form-control"   required>
                    </div>

                    <!-- NOMBRE -->
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

<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/js/javascript/js/validacionVisitante.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
