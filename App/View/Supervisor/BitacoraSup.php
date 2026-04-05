<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Cabecera -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-book me-2"></i>Registrar Bitácora
                </h1>
                <a href="./BitacoraSupervisor.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Bitácoras
                </a>
            </div>

            <!-- Card principal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información de la Bitácora</h6>
                </div>
                <div class="card-body">
                    <form id="formRegistrarBitacoraSupervisor" enctype="multipart/form-data">

                        <!-- Fila 1: Turno + Fecha -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Turno <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <select id="TurnoBitacora" name="TurnoBitacora"
                                            class="form-select" required>
                                        <option value="" disabled selected>Seleccione turno...</option>
                                        <option value="Jornada mañana">Jornada mañana</option>
                                        <option value="Jornada tarde">Jornada tarde</option>
                                        <option value="Jornada noche">Jornada noche</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Fecha y Hora <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="datetime-local" id="FechaBitacora" name="FechaBitacora"
                                           class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Novedades -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">
                                    Novedades <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <textarea id="NovedadesBitacora" name="NovedadesBitacora"
                                              class="form-control" rows="3"
                                              placeholder="Describa las novedades del turno..." required></textarea>
                                </div>
                                <small class="text-muted">Mínimo 10 caracteres.</small>
                            </div>
                        </div>

                        <!-- Supervisor + ¿Hay visitante? -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Supervisor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <select id="IdFuncionario" name="IdFuncionario"
                                            class="form-select" required>
                                        <option value="" disabled selected>Cargando supervisores...</option>
                                    </select>
                                </div>
                                <div id="msgPersonal" class="form-text"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    ¿Hay visitante? <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                                    <select id="TieneVisitante" name="TieneVisitante"
                                            class="form-select" required>
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="no">No</option>
                                        <option value="si">Sí</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Visitante (condicional) -->
                        <div class="row" id="VisitanteContainer" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Visitante <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <select id="IdVisitante" name="IdVisitante" class="form-select">
                                        <option value="" disabled selected>Cargando visitantes...</option>
                                    </select>
                                </div>
                                <div id="msgVisitante" class="form-text"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    ¿El visitante trae dispositivo? <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-laptop"></i></span>
                                    <select id="TraeDispositivo" name="TraeDispositivo" class="form-select">
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="no">No</option>
                                        <option value="si">Sí</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Dispositivo (condicional) -->
                        <div class="row" id="DispositivoContainer" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Dispositivo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-desktop"></i></span>
                                    <select id="IdDispositivo" name="IdDispositivo" class="form-select">
                                        <option value="" disabled selected>-- Seleccione dispositivo --</option>
                                    </select>
                                </div>
                                <div id="msgDispositivo" class="form-text"></div>
                            </div>
                        </div>

                        <!-- Adjuntar PDF -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                    Adjuntar PDF de soporte
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-paperclip"></i></span>
                                    <input type="file" id="ReportePDF" name="ReportePDF"
                                           class="form-control" accept="application/pdf">
                                </div>
                                <small class="text-muted">Opcional · máx. 5 MB</small>
                                <div id="pdfPreview" class="mt-2" style="display:none;">
                                    <span class="badge bg-success py-2 px-3">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <span id="pdfNombre"></span>
                                    </span>
                                    <button type="button" id="btnQuitarPDF"
                                            class="btn btn-sm btn-link text-danger p-0 ms-2">
                                        <i class="fas fa-times"></i> Quitar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botón -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Registrar Bitácora
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Card informativa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Los campos marcados con <span class="text-danger fw-bold">*</span> son obligatorios.
                        El PDF de soporte es opcional y no debe superar los 5 MB.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/SEGTRACK/Public/js/javascript/js/ValidacionBitacoraSupervisor.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>