<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-tshirt me-2"></i>Registrar Dotación
                </h1>
                <a href="./DotacionSupervisor.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dotaciones
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información de la Dotación</h6>
                </div>
                <div class="card-body">
                    <form id="formIngresarDotacionSupervisor">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-circle-check"></i></span>
                                    <select id="EstadoDotacion" name="EstadoDotacion" class="form-select" required>
                                        <option value="" disabled selected>Seleccione estado...</option>
                                        <option value="Buen estado">Buen estado</option>
                                        <option value="Regular">Regular</option>
                                        <option value="Dañado">Dañado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                    <select id="TipoDotacion" name="TipoDotacion" class="form-select" required>
                                        <option value="" disabled selected>Seleccione tipo...</option>
                                        <option value="Uniforme">Uniforme</option>
                                        <option value="Equipo">Equipo</option>
                                        <option value="Herramienta">Herramienta</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Novedad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <textarea id="NovedadDotacion" name="NovedadDotacion"
                                              class="form-control" rows="3"
                                              placeholder="Describa la novedad..." required></textarea>
                                </div>
                                <small class="text-muted">Mínimo 10 caracteres.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Fecha de Entrega <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                    <input type="datetime-local" id="FechaEntrega" name="FechaEntrega" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Fecha de Devolución</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-xmark"></i></span>
                                    <input type="datetime-local" id="FechaDevolucion" name="FechaDevolucion" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Supervisor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <select id="IdFuncionario" name="IdFuncionario" class="form-select" required>
                                        <option value="" disabled selected>Cargando supervisores...</option>
                                    </select>
                                </div>
                                <div id="msgFuncionario" class="form-text"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Registrar Dotación
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
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/SEGTRACK/Public/js/javascript/js/ValidacionDotacionSupervisor.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>