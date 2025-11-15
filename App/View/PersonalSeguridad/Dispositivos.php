<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Registrar Dispositivo</h1>
                <a href="./DispositivoLista.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dispositivos 
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Dispositivo</h6>
                </div>

                <div class="card-body">
                    <form id="formDispositivo" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">QR del Dispositivo</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary w-100" disabled>Próximamente</button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo de Dispositivo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-desktop"></i></span>
                                    <select class="form-select" name="TipoDispositivo" id="TipoDispositivo" required>
                                        <option value="">Seleccione tipo...</option>
                                        <option value="Portatil">Portátil</option>
                                        <option value="Tablet">Tablet</option>
                                        <option value="Computador">Computador</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>

                                <div id="campoOtro" class="mt-2" style="display:none;">
                                    <input type="text" class="form-control" name="OtroTipoDispositivo" placeholder="Especifique el tipo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" name="MarcaDispositivo" id="MarcaDispositivo" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Funcionario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="number" class="form-control" name="IdFuncionario" id="IdFuncionario" placeholder="Ej: 101">
                                </div>
                            </div>
                        </div>

                        <!-- 🔹 Selección de visitante -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">¿El dispositivo pertenece a un visitante?</label>
                                <select id="TieneVisitante" name="TieneVisitante" class="form-select border-primary shadow-sm">
                                    <option value="no" selected>No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>
                        </div>

                        <!-- 🔹 Campo ID Visitante (oculto por defecto) -->
                        <div class="row" id="VisitanteContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Visitante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="number" class="form-control" name="IdVisitante" id="IdVisitante" placeholder="Ej: 303">
                                </div>
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

<!-- Librería SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ✅ Script de validación externo -->
<script src="../js/javascript/js/ValidacionDispositivo.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>