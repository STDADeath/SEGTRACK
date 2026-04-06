<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus me-2"></i>Registrar Visitante
        </h1>
        <a href="./VisitanteSupervisor.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Visitantes
        </a>
    </div>

    <div class="card shadow">

        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Información del Visitante
            </h6>
        </div>

        <div class="card-body">

            <form id="formRegistrarVisitanteSupervisor">

                <div class="row">
                    <div class="col-12">

                        <div class="row">

                            <!-- IDENTIFICACIÓN -->
                            <div class="col-md-4 mb-3">
                                <label for="IdentificacionVisitante" class="form-label">
                                    Número de Identificación <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="IdentificacionVisitante"
                                       name="IdentificacionVisitante"
                                       maxlength="11"
                                       class="form-control border-primary shadow-sm"
                                       placeholder="Ej: 1234567890">
                                <div class="invalid-feedback">
                                    Ingrese solo números (6 a 11 dígitos).
                                </div>
                            </div>

                            <!-- NOMBRE -->
                            <div class="col-md-4 mb-3">
                                <label for="NombreVisitante" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="NombreVisitante"
                                       name="NombreVisitante"
                                       class="form-control border-primary shadow-sm"
                                       placeholder="Ej: Juan Pérez">
                                <div class="invalid-feedback">
                                    Mínimo 3 letras, solo caracteres válidos.
                                </div>
                            </div>

                            <!-- INSTITUCIÓN -->
                            <div class="col-md-4 mb-3">
                                <label for="IdInstitucion" class="form-label">
                                    Institución <span class="text-danger">*</span>
                                </label>
                                <select id="IdInstitucion" name="IdInstitucion"
                                        class="form-control border-primary shadow-sm">
                                    <option value="">Seleccione institución...</option>
                                </select>
                                <div class="invalid-feedback">Este campo es obligatorio.</div>
                            </div>

                        </div>

                        <div class="row">

                            <!-- SEDE -->
                            <div class="col-md-4 mb-3">
                                <label for="IdSede" class="form-label">
                                    Sede <span class="text-danger">*</span>
                                </label>
                                <select id="IdSede" name="IdSede"
                                        class="form-control border-primary shadow-sm"
                                        disabled>
                                    <option value="">Primero seleccione una institución...</option>
                                </select>
                                <div class="invalid-feedback">Este campo es obligatorio.</div>
                                <div id="spinnerSede" class="mt-1 text-muted small d-none">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Cargando sedes...
                                </div>
                            </div>

                            <!-- CORREO -->
                            <div class="col-md-4 mb-3">
                                <label for="CorreoVisitante" class="form-label">
                                    Correo Electrónico
                                    <span class="text-muted">(opcional)</span>
                                </label>
                                <input type="email"
                                       id="CorreoVisitante"
                                       name="CorreoVisitante"
                                       class="form-control border-primary shadow-sm"
                                       placeholder="correo@ejemplo.com">
                                <div class="invalid-feedback">Ingrese un correo válido.</div>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success" id="btnRegistrarVisitante">
                                <i class="fas fa-save me-1"></i> Registrar Visitante
                            </button>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/SEGTRACK/Public/js/javascript/js/ValidacionVisitanteSupervisor.js"></script>