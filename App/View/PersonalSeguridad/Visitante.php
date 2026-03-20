<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user me-2"></i>Registrar Visitante
                </h1>
                <a href="./VisitanteLista.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Visitantes
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Visitante</h6>
                </div>
                <div class="card-body">
                    <form id="formRegistrarVisitante">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Número de Identificación <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" id="IdentificacionVisitante" name="IdentificacionVisitante"
                                           class="form-control" placeholder="CC o CE" required>
                                </div>
                                <small class="text-muted">CC: 6-10 dígitos · CE: 4-20 caracteres alfanuméricos</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" id="NombreVisitante" name="NombreVisitante"
                                           class="form-control" placeholder="Nombre del visitante" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Correo Electrónico <span class="text-muted">(opcional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="CorreoVisitante" name="CorreoVisitante"
                                           class="form-control" placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Registrar Visitante
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
                        <i class="fas fa-info-circle me-2"></i>
                        Los campos marcados con <span class="text-danger fw-bold">*</span> son obligatorios.
                        El correo electrónico es opcional.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/validacionVisitante.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>