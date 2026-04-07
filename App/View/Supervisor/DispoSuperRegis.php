<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Registrar Dispositivo</h1>
                <a href="./DispositivoSupervisor.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dispositivos
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Dispositivo</h6>
                </div>

                <div class="card-body">
                    <form id="formDispositivo" method="POST">
                        
                        <!-- Tipo de Dispositivo -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-desktop mr-1 text-primary"></i>Tipo de Dispositivo <span class="text-danger">*</span>
                                </label>
                                <select name="TipoDispositivo" id="TipoDispositivo" class="form-control" required>
                                    <option value="">Seleccione tipo...</option>
                                    <option value="Portatil">Portátil</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Computador">Computador</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <div id="campoOtro" class="mt-2" style="display:none;">
                                    <input type="text" class="form-control" name="OtroTipoDispositivo" placeholder="Especifique el tipo">
                                </div>
                            </div>

                            <!-- Marca -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-tag mr-1 text-primary"></i>Marca <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" name="MarcaDispositivo" id="MarcaDispositivo" required placeholder="Ej: HP, Dell, Lenovo">
                                </div>
                            </div>
                        </div>

                        <!-- Número Serial -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-barcode mr-1 text-primary"></i>Número Serial
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control" name="NumeroSerial" id="NumeroSerial" placeholder="Ej: SN123456789">
                                </div>
                                <small class="text-muted">Campo opcional - Ingrese el serial del dispositivo</small>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════════════════════════
                             CASCADA: Institución → Sede → Funcionario / Visitante
                             ════════════════════════════════════════════════════════════ -->

                        <!-- Institución -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-university mr-1 text-primary"></i>Institución <span class="text-danger">*</span>
                                </label>
                                <select name="IdInstitucion" id="IdInstitucion" class="form-control" required>
                                    <option value="">Cargando instituciones...</option>
                                </select>
                                <small class="text-muted">Seleccione la institución para cargar las sedes.</small>
                            </div>

                            <!-- Sede (dinámica) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Sede <span class="text-danger">*</span>
                                </label>
                                <select name="IdSede" id="IdSede" class="form-control" required disabled>
                                    <option value="">Primero seleccione una institución...</option>
                                </select>
                                <small class="text-muted">Seleccione la sede para cargar funcionarios y visitantes.</small>
                            </div>
                        </div>

                        <!-- ¿Pertenece a funcionario o visitante? -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-user mr-1 text-primary"></i>¿A quién pertenece el dispositivo?
                                </label>
                                <select id="TieneVisitante" name="TieneVisitante" class="form-control">
                                    <option value="no" selected>Funcionario</option>
                                    <option value="si">Visitante</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campo Funcionario (visible por defecto) -->
                        <div class="row" id="FuncionarioContainer">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-user-tie mr-1 text-primary"></i>Funcionario <span class="text-danger">*</span>
                                </label>
                                <select name="IdFuncionario" id="IdFuncionario" class="form-control" disabled>
                                    <option value="">Primero seleccione una sede...</option>
                                </select>
                                <small class="text-muted">Los funcionarios se filtran según la sede seleccionada.</small>
                            </div>
                        </div>

                        <!-- Campo Visitante (oculto por defecto) -->
                        <div class="row" id="VisitanteContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                                    <i class="fas fa-user mr-1 text-primary"></i>Visitante <span class="text-danger">*</span>
                                </label>
                                <select name="IdVisitante" id="IdVisitante" class="form-control" disabled>
                                    <option value="">Primero seleccione una sede...</option>
                                </select>
                                <small class="text-muted">Los visitantes se filtran según la sede seleccionada.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Dispositivo
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
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos del dispositivo.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionDispositivo.js?v=<?= time() ?>"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>