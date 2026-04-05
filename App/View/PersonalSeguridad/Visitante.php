<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="card shadow">

        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Registrar Visitante
            </h6>
        </div>

        <div class="card-body">

            <form id="formRegistrarVisitante">

                <!-- CAMPOS DEL FORMULARIO -->
                <div class="row">
                    <div class="col-12">

                        <div class="row">

                            <!-- IDENTIFICACIÓN -->
                            <div class="col-md-4 mb-3">
                                <label for="IdentificacionVisitante" class="form-label">
                                    Número de Identificación *
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
                                    Nombre Completo *
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
                                <label for="IdInstitucion" class="form-label">Institución *</label>
                                <select id="IdInstitucion" name="IdInstitucion"
                                    class="form-control border-primary shadow-sm">
                                    <option value="">Seleccione institución...</option>
                                </select>
                                <div class="invalid-feedback">Este campo es obligatorio.</div>
                            </div>

                        </div>

                        <div class="row">

                            <!-- SEDE (se puebla dinámicamente según institución) -->
                            <div class="col-md-4 mb-3">
                                <label for="IdSede" class="form-label">Sede *</label>
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
                            <button type="submit" class="btn btn-success" id="btnRegistrar">
                                <i class="fas fa-save"></i> Registrar Visitante
                            </button>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/validacionVisitante.js"></script>