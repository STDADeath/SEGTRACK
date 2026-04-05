<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">

    <!-- ── Header ── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book me-2"></i>Bitácoras Registradas
        </h1>
    </div>

    <!-- ── Filtros ── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Bitácoras
            </h6>
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-clock mr-1 text-primary"></i>Turno
                    </label>
                    <select id="filtroTurno" class="form-control">
                        <option value="">Todos</option>
                        <option value="Jornada mañana">Jornada mañana</option>
                        <option value="Jornada tarde">Jornada tarde</option>
                        <option value="Jornada noche">Jornada noche</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-calendar mr-1 text-primary"></i>Fecha
                    </label>
                    <input type="date" id="filtroFecha" class="form-control">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-user-tie mr-1 text-primary"></i>Funcionario
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filtroFuncionario" class="form-control"
                               placeholder="Buscar por nombre...">
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-user mr-1 text-primary"></i>Visitante
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="filtroVisitante" class="form-control"
                               placeholder="Buscar visitante...">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Bitácoras</h6>
            <span class="badge badge-primary" id="contadorBitacoras" style="font-size:0.85rem;">Cargando...</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center"
                   id="TablaBitacoraSupervisor" style="width:100%;">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Turno</th>
                        <th>Novedades</th>
                        <th>Fecha y Hora</th>
                        <th>Supervisor</th>
                        <th>Personal Seguridad</th>
                        <th>Visitante</th>
                        <th>Dispositivo</th>
                        <th>PDF</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaBitacoraSupervisor">
                    <tr>
                        <td colspan="11" class="text-center py-4">
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
<div class="modal fade" id="modalEditarBitacora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Bitácora #<span id="editIdBitacoraLabel"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIdBitacora">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Turno</label>
                        <select id="editTurnoBitacora" class="form-control">
                            <option value="Jornada mañana">Jornada mañana</option>
                            <option value="Jornada tarde">Jornada tarde</option>
                            <option value="Jornada noche">Jornada noche</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Fecha y Hora</label>
                        <input type="datetime-local" id="editFechaBitacora" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold">Novedades</label>
                        <textarea id="editNovedadesBitacora" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Registrado por <small class="text-muted">(Solo lectura)</small>
                        </label>
                        <input type="text" id="editNombreFuncionarioBit" class="form-control bg-light" readonly>
                        <input type="hidden" id="editIdFuncionarioBit">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div id="editCargoBadge"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Visitante <small class="text-muted">(Solo lectura)</small>
                        </label>
                        <input type="text" id="editNombreVisitanteBit" class="form-control bg-light" readonly>
                        <input type="hidden" id="editIdVisitanteBit">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            Dispositivo <small class="text-muted">(Solo lectura)</small>
                        </label>
                        <input type="text" id="editNombreDispositivoBit" class="form-control bg-light" readonly>
                        <input type="hidden" id="editIdDispositivoBit">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">
                            PDF Actual <small class="text-muted">(Solo lectura)</small>
                        </label>
                        <div id="editPdfActual"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button class="btn btn-primary" id="btnGuardarEdicionBitacora">
                    <i class="fas fa-save mr-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL CAMBIO DE ESTADO ══ -->
<div class="modal fade" id="modalCambiarEstadoBitacora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstadoBitacora">
                <h5 class="modal-title" id="tituloCambioEstadoBitacora"></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualBitacora">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstadoBitacora" class="mb-3 mt-2" style="font-size:1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstadoBitacora">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<script src="/SEGTRACK/Public/js/javascript/js/ValidacionBitacoraSupervisor.js"></script>