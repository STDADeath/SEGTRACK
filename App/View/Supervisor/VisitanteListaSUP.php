<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">

    <!-- ── Header ── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-friends me-2"></i>Visitantes Registrados
        </h1>
    </div>

    <!-- ── Filtros ── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Visitantes
            </h6>
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-id-card mr-1 text-primary"></i>Identificación
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filtroIdentificacion" class="form-control"
                               placeholder="Número de identificación">
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-user mr-1 text-primary"></i>Nombre
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filtroNombre" class="form-control"
                               placeholder="Nombre del visitante">
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-toggle-on mr-1 text-primary"></i>Estado
                    </label>
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos</option>
                        <option value="Activo">✅ Activo</option>
                        <option value="Inactivo">❌ Inactivo</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label d-block invisible">.</label>
                    <button type="button" id="btnFiltrar" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i>Filtrar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Tabla ── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Visitantes</h6>
            <span class="badge badge-primary" id="contadorVisitantes" style="font-size:0.85rem;">Cargando...</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center"
                   id="TablaVisitanteSupervisor" style="width:100%;">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Identificación</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Institución</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaVisitanteSupervisor">
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2 d-block"></i>
                            <span class="text-muted">Cargando...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ══ MODAL EDITAR ══ -->
<div class="modal fade" id="modalEditarVisitante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Visitante #<span id="editIdVisitanteLabel"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIdVisitante">

                <div class="row">
                    <!-- Identificación: solo lectura -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Identificación <small class="text-muted">(Solo lectura)</small>
                        </label>
                        <input type="text" id="editIdentificacionVisitante"
                               class="form-control bg-light" readonly>
                    </div>
                    <!-- Nombre: EDITABLE -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Nombre Completo <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="editNombreVisitante"
                               class="form-control border-primary"
                               placeholder="Nombre del visitante">
                        <div class="invalid-feedback">Solo letras, mínimo 3 caracteres.</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Correo: editable -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Correo Electrónico <small class="text-muted">(opcional)</small>
                        </label>
                        <input type="email" id="editCorreoVisitante"
                               class="form-control border-primary"
                               placeholder="correo@ejemplo.com">
                        <div class="invalid-feedback">Ingrese un correo válido.</div>
                    </div>
                    <!-- Institución -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Institución <span class="text-danger">*</span>
                        </label>
                        <select id="editIdInstitucion" class="form-control border-primary">
                            <option value="">Seleccione institución...</option>
                        </select>
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sede -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Sede <span class="text-danger">*</span>
                        </label>
                        <select id="editIdSede" class="form-control border-primary" disabled>
                            <option value="">Primero seleccione una institución...</option>
                        </select>
                        <div id="editSpinnerSede" class="mt-1 text-muted small d-none">
                            <i class="fas fa-spinner fa-spin me-1"></i> Cargando sedes...
                        </div>
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button class="btn btn-primary" id="btnGuardarEdicionVisitante">
                    <i class="fas fa-save mr-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL CAMBIO DE ESTADO ══ -->
<div class="modal fade" id="modalCambiarEstadoVisitante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstadoVisitante">
                <h5 class="modal-title" id="tituloCambioEstadoVisitante"></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualVisitante">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstadoVisitante" class="mb-3 mt-2" style="font-size:1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstadoVisitante">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/SEGTRACK/Public/js/javascript/js/ValidacionVisitanteSupervisor.js"></script>