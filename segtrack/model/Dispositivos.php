<?php require_once __DIR__ . '/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Registrar Dispositivos</h1>
                <a href="../models/DispositivoLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Dispositivos
                </a>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Dispositivo</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../backed/IngresoDispositivo.php" id="formDispositivo" class="needs-validation" novalidate>

                        <div class="row">
                            <!-- QR deshabilitado, ahora solo botón -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">QR del Dispositivo</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary w-100" disabled>Próximamente</button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo de Dispositivo</label>
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
                                <!-- Campo de texto oculto para "Otro" -->
                                <div id="campoOtro" class="mt-2" style="display:none;">
                                    <input type="text" class="form-control" name="OtroTipoDispositivo" placeholder="Especifique el tipo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Marca</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" name="MarcaDispositivo" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Funcionario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="number" class="form-control" name="IdFuncionario" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">ID Visitante</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="number" class="form-control" name="IdVisitante">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='../models/DispositivoLista.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
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


<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
<script src="../js/javascript/js/ValidacionDispositivo.js"></script>

</body>
</html>

<?php require_once __DIR__ . '/../models/parte_inferior.php'; ?>
